<?php
// Hlomla Magopeni 218070349 — Eben Supply | Group KN3

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function tshirt(int $mStock = 3, int $lStock = 5): Product
    {
        $product = Product::create([
            'name'           => 'Test Tee',
            'category'       => 'tshirt',
            'price'          => 289.00,
            'stock_quantity' => $mStock + $lStock,
        ]);
        ProductSize::create(['product_id' => $product->id, 'size' => 'M', 'stock_quantity' => $mStock]);
        ProductSize::create(['product_id' => $product->id, 'size' => 'L', 'stock_quantity' => $lStock]);

        return $product;
    }

    private function toteBag(int $stock = 2): Product
    {
        return Product::create([
            'name'           => 'Test Tote',
            'category'       => 'tote_bag',
            'price'          => 150.00,
            'stock_quantity' => $stock,
        ]);
    }

    private function checkout(): \Illuminate\Testing\TestResponse
    {
        $this->post('/checkout', [
            'contact_name'     => 'Test Customer',
            'contact_phone'    => '082 000 0002',
            'contact_email'    => 'customer@test.co.za',
            'fulfillment'      => 'delivery',
            'delivery_address' => '12 Albert Rd, Woodstock',
        ])->assertRedirect(route('checkout.payment'));

        return $this->post('/checkout/payment');
    }

    // The test client does not keep cookies between requests; send the
    // current session cookie so the next request continues the same visit.
    private function asSameBrowser(): static
    {
        return $this->withCookie(config('session.cookie'), session()->getId());
    }

    // Cart

    public function test_can_add_item_within_stock(): void
    {
        $product = $this->tshirt();

        $this->post('/cart/add', ['product_id' => $product->id, 'size' => 'M', 'quantity' => 2])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'size' => 'M', 'quantity' => 2]);
    }

    public function test_cannot_add_more_than_size_stock(): void
    {
        $product = $this->tshirt(mStock: 3);

        $this->post('/cart/add', ['product_id' => $product->id, 'size' => 'M', 'quantity' => 4])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_stock_check_includes_quantity_already_in_cart(): void
    {
        $product = $this->tshirt(mStock: 3);
        $this->actingAs(User::factory()->create());

        $this->post('/cart/add', ['product_id' => $product->id, 'size' => 'M', 'quantity' => 2]);
        $this->post('/cart/add', ['product_id' => $product->id, 'size' => 'M', 'quantity' => 2])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'size' => 'M', 'quantity' => 2]);
    }

    public function test_stock_error_message_is_shown_on_the_page(): void
    {
        $product = $this->tshirt(mStock: 1);

        $this->from('/products/' . $product->id)
            ->followingRedirects()
            ->post('/cart/add', ['product_id' => $product->id, 'size' => 'M', 'quantity' => 2])
            ->assertOk()
            ->assertSee('toast-show', false)
            ->assertSee('Only 1 of Test Tee (M) in stock.');
    }

    public function test_cannot_add_size_product_does_not_have(): void
    {
        $product = $this->tshirt();

        $this->post('/cart/add', ['product_id' => $product->id, 'size' => 'XXXL', 'quantity' => 1])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cannot_add_out_of_stock_product_without_sizes(): void
    {
        $product = $this->toteBag(stock: 0);

        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cannot_update_cart_quantity_above_stock(): void
    {
        $user    = User::factory()->create();
        $product = $this->toteBag(stock: 2);
        $item    = CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user)
            ->post('/cart/update', ['cart_item_id' => $item->id, 'quantity' => 3])
            ->assertSessionHas('error');

        $this->assertSame(1, $item->fresh()->quantity);
    }

    // Checkout

    public function test_checkout_deducts_stock_and_shows_confirmation(): void
    {
        $user  = User::factory()->create();
        $tee   = $this->tshirt(mStock: 3, lStock: 5);
        $tote  = $this->toteBag(stock: 2);
        CartItem::create(['user_id' => $user->id, 'product_id' => $tee->id, 'size' => 'M', 'quantity' => 2]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $tote->id, 'quantity' => 1]);

        $this->actingAs($user);
        $this->checkout()->assertRedirect(route('order.confirmation'));

        $this->assertSame(1, ProductSize::where('product_id', $tee->id)->where('size', 'M')->value('stock_quantity'));
        $this->assertSame(5, ProductSize::where('product_id', $tee->id)->where('size', 'L')->value('stock_quantity'));
        $this->assertSame(6, $tee->fresh()->stock_quantity);
        $this->assertSame(1, $tote->fresh()->stock_quantity);

        $order = Order::first();
        $this->assertSame(2, $order->items()->count());
        $this->assertDatabaseCount('cart_items', 0);

        $this->get(route('order.confirmation'))
            ->assertOk()
            ->assertSee($order->payment_reference)
            ->assertSee('R788.00'); // 2 × 289 + 150 + 60 delivery
    }

    public function test_checkout_fails_without_changes_when_stock_ran_out(): void
    {
        $user = User::factory()->create();
        $tee  = $this->tshirt(mStock: 3);
        CartItem::create(['user_id' => $user->id, 'product_id' => $tee->id, 'size' => 'M', 'quantity' => 2]);

        $this->actingAs($user);
        $this->post('/checkout', [
            'contact_name'     => 'Test Customer',
            'contact_phone'    => '082 000 0002',
            'contact_email'    => 'customer@test.co.za',
            'fulfillment'      => 'pickup',
        ]);

        // Someone else buys the stock before payment
        ProductSize::where('product_id', $tee->id)->where('size', 'M')->update(['stock_quantity' => 1]);

        $this->post('/checkout/payment')
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, ProductSize::where('product_id', $tee->id)->where('size', 'M')->value('stock_quantity'));
        $this->assertSame(8, $tee->fresh()->stock_quantity);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_confirmation_redirects_home_without_an_order(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('order.confirmation'))
            ->assertRedirect(route('home'));
    }

    // Guest cart

    public function test_guest_cart_moves_to_account_on_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $tote = $this->toteBag(stock: 5);

        $this->post('/cart/add', ['product_id' => $tote->id, 'quantity' => 2]);
        $this->assertDatabaseHas('cart_items', ['product_id' => $tote->id, 'user_id' => null]);

        $this->asSameBrowser()->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('cart_items', ['product_id' => $tote->id, 'user_id' => $user->id, 'quantity' => 2, 'session_id' => null]);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_guest_cart_merges_with_existing_account_cart(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $tote = $this->toteBag(stock: 5);
        CartItem::create(['user_id' => $user->id, 'product_id' => $tote->id, 'quantity' => 1]);

        $this->post('/cart/add', ['product_id' => $tote->id, 'quantity' => 2]);
        $this->asSameBrowser()->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', ['product_id' => $tote->id, 'user_id' => $user->id, 'quantity' => 3]);
    }
}
