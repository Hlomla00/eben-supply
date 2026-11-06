<?php
// PRT362S — Eben Supply | Group KN3
// Covers the fixes from the 6 November 2026 audit of this repository
// (see docs/AUDIT.md, section "Audit of this repository").

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditFixesTest extends TestCase
{
    use RefreshDatabase;

    private function tee(array $attributes = []): Product
    {
        $product = Product::create($attributes + [
            'name' => 'Audit Tee', 'category' => 'tshirt', 'price' => 200.00, 'stock_quantity' => 20,
        ]);
        ProductSize::create(['product_id' => $product->id, 'size' => 'M', 'stock_quantity' => 20]);

        return $product;
    }

    private function details(string $fulfillment = 'pickup', string $email = 'buyer@test.co.za'): array
    {
        return [
            'contact_name' => 'Audit Buyer', 'contact_phone' => '082 000 0003', 'contact_email' => $email,
            'fulfillment' => $fulfillment, 'delivery_address' => $fulfillment === 'delivery' ? '1 Main Rd, Woodstock' : null,
        ];
    }

    private function inCart(User $user, Product $product, int $quantity = 1): CartItem
    {
        return CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'size' => 'M', 'quantity' => $quantity]);
    }

    private function orderWith(User $user, Product $product): Order
    {
        $order = Order::create([
            'user_id' => $user->id, 'status' => 'paid', 'fulfillment' => 'delivery', 'total_amount' => 260,
            'contact_name' => 'A', 'contact_phone' => '1', 'contact_email' => $user->email, 'payment_reference' => 'ES-AUDIT1',
        ]);
        $order->items()->create(['product_id' => $product->id, 'size' => 'M', 'quantity' => 1, 'unit_price' => 200]);

        return $order;
    }

    // ── A5: deleting a product ──────────────────────────────────────────────

    public function test_deleted_product_is_removed_from_cart_with_a_message(): void
    {
        $user = User::factory()->create();
        $this->inCart($user, $this->tee())->product->delete();

        $this->actingAs($user)->get('/cart')->assertOk()->assertSee('no longer available');
        $this->assertSame(0, CartItem::count());
        $this->actingAs($user)->get('/checkout')->assertRedirect(route('cart.index'));
    }

    public function test_past_orders_still_show_a_deleted_product(): void
    {
        $user    = User::factory()->create();
        $product = $this->tee();
        $order   = $this->orderWith($user, $product);
        $product->delete();

        $this->actingAs($user)->get("/my-orders/{$order->id}")->assertOk()->assertSee('Audit Tee');
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get("/admin/orders/{$order->id}")->assertOk()->assertSee('Audit Tee');
    }

    public function test_order_lines_keep_the_product_name_as_ordered(): void
    {
        $user    = User::factory()->create();
        $product = $this->tee();
        $this->inCart($user, $product);

        $this->actingAs($user)->post('/checkout', $this->details());
        $this->actingAs($user)->post('/checkout/payment')->assertRedirect(route('order.confirmation'));

        $product->update(['name' => 'Renamed Tee']);
        $this->assertSame('Audit Tee', Order::first()->items->first()->name);
    }

    // ── A1 and sale prices: charging the right amount ───────────────────────

    public function test_items_added_after_the_details_step_are_charged(): void
    {
        $user    = User::factory()->create();
        $product = $this->tee();
        $this->inCart($user, $product);

        $this->actingAs($user)->post('/checkout', $this->details('delivery'));
        CartItem::first()->update(['quantity' => 3]);
        $this->actingAs($user)->post('/checkout/payment');

        $order = Order::firstOrFail();
        $this->assertEquals(600.00, $order->subtotal);
        $this->assertEquals(660.00, $order->total_amount);
    }

    public function test_checkout_charges_the_running_sale_price(): void
    {
        $user = User::factory()->create();
        $this->inCart($user, $this->tee(['sale_price' => 150.00, 'sale_starts_at' => now()->subDay(), 'sale_ends_at' => now()->addDay()]), 2);

        $this->actingAs($user)->get('/cart')->assertSee('R300.00');
        $this->actingAs($user)->post('/checkout', $this->details());
        $this->actingAs($user)->post('/checkout/payment');

        $order = Order::firstOrFail();
        $this->assertEquals(300.00, $order->total_amount);
        $this->assertEquals(150.00, $order->items->first()->unit_price);
    }

    public function test_a_changed_total_is_shown_before_paying(): void
    {
        $user = User::factory()->create();
        $this->inCart($user, $this->tee());

        $this->actingAs($user)->post('/checkout', $this->details());
        $this->actingAs($user)->post('/checkout/payment', ['expected_total' => '150.00'])
            ->assertRedirect(route('checkout.payment'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'R200.00'));

        $this->assertSame(0, Order::count());
    }

    // ── Promo codes ─────────────────────────────────────────────────────────

    public function test_promo_code_discount_is_applied_and_stored(): void
    {
        $user  = User::factory()->create();
        $promo = PromoCode::create(['code' => 'welcome10', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);
        $this->inCart($user, $this->tee(), 2);

        $this->actingAs($user)->post('/cart/promo', ['code' => 'Welcome10'])->assertSessionHas('success');
        $this->actingAs($user)->get('/cart')->assertSee('WELCOME10')->assertSee('R40.00');
        $this->actingAs($user)->post('/checkout', $this->details('delivery'));
        $this->actingAs($user)->get('/checkout/payment')->assertSee('R420.00');
        $this->actingAs($user)->post('/checkout/payment', ['expected_total' => '420.00'])->assertRedirect(route('order.confirmation'));

        $order = Order::firstOrFail();
        $this->assertEquals(400.00, $order->subtotal);
        $this->assertEquals(40.00, $order->discount_amount);
        $this->assertEquals(420.00, $order->total_amount);
        $this->assertSame('WELCOME10', $order->promo_code);
        $this->assertSame(1, $promo->fresh()->times_used);
        $this->actingAs($user)->get("/my-orders/{$order->id}")->assertSee('Discount (WELCOME10)', false);
    }

    public function test_a_promo_code_can_be_used_once_per_customer(): void
    {
        $user = User::factory()->create();
        PromoCode::create(['code' => 'ONCE', 'type' => 'fixed', 'value' => 20, 'is_active' => true]);
        $this->inCart($user, $this->tee());

        $this->actingAs($user)->post('/cart/promo', ['code' => 'ONCE']);
        $this->actingAs($user)->post('/checkout', $this->details());
        $this->actingAs($user)->post('/checkout/payment');

        $this->inCart($user, $this->tee(['name' => 'Second Tee']));
        $this->actingAs($user)->post('/cart/promo', ['code' => 'ONCE'])
            ->assertSessionHas('promo_error', fn ($m) => str_contains($m, 'already used'));
    }

    public function test_unknown_promo_codes_are_rejected_and_rate_limited(): void
    {
        $user = User::factory()->create();
        $this->inCart($user, $this->tee());

        foreach (range(1, 5) as $i) {
            $this->actingAs($user)->post('/cart/promo', ['code' => "NOPE{$i}"])->assertSessionHas('promo_error', "That promo code isn't valid.");
        }
        $this->actingAs($user)->post('/cart/promo', ['code' => 'NOPE6'])
            ->assertSessionHas('promo_error', fn ($m) => str_contains($m, 'Too many'));
    }

    public function test_admin_can_create_and_deactivate_a_promo_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/promos', ['code' => 'spring15', 'type' => 'percentage', 'value' => 15, 'is_active' => 1])
            ->assertRedirect(route('admin.promos.index'));
        $promo = PromoCode::where('code', 'SPRING15')->firstOrFail();

        $this->actingAs($admin)->get('/admin/promos')->assertOk()->assertSee('SPRING15');
        $this->actingAs($admin)->post("/admin/promos/{$promo->id}/toggle");
        $this->assertFalse($promo->fresh()->is_active);
        $this->actingAs(User::factory()->create())->get('/admin/promos')->assertForbidden();
    }

    // ── Quantity limit ──────────────────────────────────────────────────────

    public function test_cart_quantity_is_capped_at_ten(): void
    {
        $user    = User::factory()->create();
        $product = $this->tee();

        $this->actingAs($user)->post('/cart/add', ['product_id' => $product->id, 'size' => 'M', 'quantity' => 11])->assertSessionHasErrors('quantity');
        $this->actingAs($user)->post('/cart/add', ['product_id' => $product->id, 'size' => 'M', 'quantity' => 8]);
        $this->actingAs($user)->post('/cart/add', ['product_id' => $product->id, 'size' => 'M', 'quantity' => 3])
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'up to 10'));

        $this->assertSame(8, CartItem::first()->quantity);
    }

    // ── A25: delivered, not collected ───────────────────────────────────────

    public function test_delivery_orders_say_delivered(): void
    {
        $user  = User::factory()->create();
        $order = $this->orderWith($user, $this->tee());
        $order->update(['status' => 'collected']);

        $this->assertSame('Delivered', $order->fresh()->status_badge['label']);
        $this->actingAs($user)->get("/my-orders/{$order->id}")->assertSee('Delivered')->assertDontSee('Collected');
    }

    // ── A23, A30: real counts ───────────────────────────────────────────────

    public function test_home_stats_use_real_counts(): void
    {
        $this->tee();
        $this->tee(['name' => 'Second Tee']);

        $this->get('/')->assertOk()->assertDontSee('15+')
            ->assertSeeInOrder(['2', 'Products', '1', 'Categories']);
    }

    public function test_order_history_counts_units_not_lines(): void
    {
        $user  = User::factory()->create();
        $order = $this->orderWith($user, $this->tee());
        $order->items()->first()->update(['quantity' => 3]);

        $this->actingAs($user)->get('/my-orders')->assertOk()->assertSeeInOrder(['Items', '3']);
    }

    // ── Admin, error and information pages ──────────────────────────────────

    public function test_admin_root_goes_to_the_dashboard_and_phones_get_a_menu(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Ada Admin']);

        $this->actingAs($admin)->get('/admin')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($admin)->get('/admin/admin-dashboard')->assertOk()
            ->assertSee('<details class="lg:hidden', false)
            ->assertSee('Promo Codes')
            ->assertSee('Ada Admin');
    }

    public function test_missing_pages_show_the_branded_404(): void
    {
        $this->get('/no-such-page')->assertNotFound()->assertSee('Page not found')->assertSee('Back to the store');
        $this->get('/products/999999')->assertNotFound()->assertSee('Page not found');
    }

    public function test_information_pages_render_and_are_linked_in_the_footer(): void
    {
        foreach (['/contact' => 'Contact Us', '/shipping' => 'Delivery', '/returns' => '7 days', '/terms' => 'Terms', '/privacy' => 'POPIA'] as $url => $text) {
            $this->get($url)->assertOk()->assertSee($text);
        }

        $this->get('/')->assertSee(route('pages.privacy'))->assertSee(route('pages.returns'));
    }

    public function test_contact_details_only_show_when_configured(): void
    {
        $this->get('/contact')->assertDontSee('mailto:');

        config(['store.email' => 'hello@ebensupply.test']);
        $this->get('/contact')->assertSee('mailto:hello@ebensupply.test', false);
    }
}
