<?php
// Hlomla Magopeni 218070349 — Eben Supply | Group KN3
// Renders every admin and storefront page outside cart/checkout, and checks
// the admin product create/edit flow (issue #17) and order ownership.

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAndStorePagesTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name = 'Smoke Tee', string $category = 'tshirt'): Product
    {
        $product = Product::create([
            'name' => $name, 'category' => $category, 'price' => 289.00, 'stock_quantity' => 4,
        ]);
        ProductSize::create(['product_id' => $product->id, 'size' => 'M', 'stock_quantity' => 4]);

        return $product;
    }

    private function orderFor(?User $user, Product $product): Order
    {
        $order = Order::create([
            'user_id' => $user?->id, 'status' => 'pending', 'fulfillment' => 'pickup',
            'total_amount' => 289.00, 'contact_name' => 'Smoke Customer', 'contact_phone' => '082 000 0002',
            'contact_email' => 'smoke@test.co.za', 'payment_reference' => 'ES-SMOKE1',
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $product->id, 'size' => 'M', 'quantity' => 1, 'unit_price' => 289.00,
        ]);

        return $order;
    }

    // Admin pages

    public function test_admin_pages_render(): void
    {
        $admin   = User::factory()->create(['role' => 'admin']);
        $product = $this->product();
        $order   = $this->orderFor(null, $product);

        $this->actingAs($admin);
        $this->get('/admin/admin-dashboard')->assertOk();
        $this->get('/admin/orders')->assertOk()->assertSee('ES-SMOKE1');
        $this->get("/admin/orders/{$order->id}")->assertOk()->assertSee('Smoke Customer');
        $this->get('/admin/products')->assertOk()->assertSee('Smoke Tee');
        $this->get('/admin/products/create')->assertOk()->assertSee('name="name"', false);
        $this->get("/admin/products/{$product->id}/edit")->assertOk()->assertSee('Smoke Tee');
        $this->get('/admin/inventory')->assertOk()->assertSee('Smoke Tee');
    }

    public function test_admin_can_create_a_product_with_sizes(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post('/admin/products', [
                'name' => 'New Cap', 'category' => 'cap', 'price' => 199.00,
                'sizing' => 'sized', 'sizes' => ['S' => 2, 'M' => 3], 'image' => $this->fakeImage(),
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'New Cap')->firstOrFail();
        $this->assertSame(5, $product->stock_quantity);
        $this->assertSame(3, ProductSize::where('product_id', $product->id)->where('size', 'M')->value('stock_quantity'));
    }

    public function test_admin_can_edit_a_product(): void
    {
        $product = $this->product();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put("/admin/products/{$product->id}", [
                'name' => 'Smoke Tee v2', 'category' => 'tshirt', 'price' => 310.00,
                'sizing' => 'sized', 'sizes' => ['M' => 6, 'L' => 2], 'image' => $this->fakeImage(),
            ])
            ->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $this->assertSame('Smoke Tee v2', $product->name);
        $this->assertEquals(310.00, $product->price);
        $this->assertSame(8, $product->stock_quantity);
    }

    public function test_admin_product_form_is_validated(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post('/admin/products', ['name' => '', 'category' => 'jacket', 'price' => -5])
            ->assertSessionHasErrors(['name', 'category', 'price']);

        $this->assertDatabaseCount('products', 0);
    }

    // Storefront pages

    public function test_storefront_pages_render(): void
    {
        $product = $this->product();

        $this->get('/')->assertOk();
        $this->get('/products')->assertOk()->assertSee('Smoke Tee');
        $this->get("/products/{$product->id}")->assertOk()->assertSee('Smoke Tee');
    }

    public function test_product_search_and_category_filter(): void
    {
        $this->product('Jet Black Tee', 'tshirt');
        $this->product('Classic Snapback', 'cap');

        $this->get('/products?search=Snapback')->assertOk()
            ->assertSee('Classic Snapback')->assertDontSee('Jet Black Tee');

        $this->get('/products?category=tshirt')->assertOk()
            ->assertSee('Jet Black Tee')->assertDontSee('Classic Snapback');
    }

    // My Orders

    public function test_customer_sees_own_orders_only(): void
    {
        $product = $this->product();
        $me      = User::factory()->create();
        $other   = User::factory()->create();
        $mine    = $this->orderFor($me, $product);
        $theirs  = $this->orderFor($other, $product);

        $this->actingAs($me);
        $this->get('/my-orders')->assertOk()->assertSee('ES-SMOKE1');
        $this->get("/my-orders/{$mine->id}")->assertOk();
        $this->get("/my-orders/{$theirs->id}")->assertForbidden();
    }

    public function test_guest_is_sent_to_login_for_my_orders(): void
    {
        $this->get('/my-orders')->assertRedirect(route('login'));
    }
}
