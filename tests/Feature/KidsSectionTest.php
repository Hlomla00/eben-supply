<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KidsSectionTest extends TestCase
{
    use RefreshDatabase;

    private function kidsTee(array $attributes = []): Product
    {
        return Product::factory()
            ->withSizes(['2-3Y' => 3, '4-5Y' => 0, '6-7Y' => 5])
            ->create(array_merge(['name' => 'ES Kids Tee – Test', 'is_kids' => true], $attributes));
    }

    public function test_kids_page_lists_only_kids_products(): void
    {
        $this->kidsTee();
        Product::factory()->create(['name' => 'ES Adult Cap']);

        $this->get('/kids')->assertOk()
            ->assertSee('ES Kids Tee – Test')
            ->assertDontSee('ES Adult Cap')
            ->assertSee('<title>Kids | Eben Supply', false);
    }

    public function test_kids_page_filters_by_category_within_kids(): void
    {
        $this->kidsTee();
        Product::factory()->create(['name' => 'ES Kids Cap', 'is_kids' => true]);
        Product::factory()->withSizes()->create(['name' => 'ES Adult Tee']);

        $this->get('/kids?category=tshirt')->assertOk()
            ->assertSee('Kids T-Shirts')
            ->assertSee('ES Kids Tee – Test')
            ->assertDontSee('ES Kids Cap')
            ->assertDontSee('ES Adult Tee');
    }

    public function test_kids_link_is_in_desktop_and_mobile_navigation(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertSame(1, substr_count($html, 'class="nav-link pb-1 ">Kids</a>'), 'desktop nav');
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'href="' . route('kids.index') . '"'), 'mobile menu and footer');
    }

    public function test_kids_product_page_shows_kids_sizes_and_disables_sold_out_size(): void
    {
        $tee = $this->kidsTee();

        $this->get("/products/{$tee->id}")->assertOk()
            ->assertSeeInOrder(['data-size="2-3Y"', 'data-size="4-5Y"', 'data-size="6-7Y"'], false)
            ->assertSee("Child's height", false);
    }

    public function test_kids_size_stock_is_tracked_per_size_in_cart(): void
    {
        $user = User::factory()->create();
        $tee = $this->kidsTee();

        $this->actingAs($user)->post('/cart/add', ['product_id' => $tee->id, 'size' => '4-5Y', 'quantity' => 1])
            ->assertSessionHas('error');
        $this->actingAs($user)->post('/cart/add', ['product_id' => $tee->id, 'size' => 'M', 'quantity' => 1])
            ->assertSessionHas('error');
        $this->actingAs($user)->post('/cart/add', ['product_id' => $tee->id, 'size' => '2-3Y', 'quantity' => 4])
            ->assertSessionHas('error');
        $this->actingAs($user)->post('/cart/add', ['product_id' => $tee->id, 'size' => '2-3Y', 'quantity' => 3])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cart_items', ['product_id' => $tee->id, 'size' => '2-3Y', 'quantity' => 3]);
        $this->assertSame(1, CartItem::count());
    }

    public function test_admin_can_create_a_kids_product_with_kids_sizes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'ES Kids Hoodie', 'category' => 'tshirt', 'price' => 249, 'is_kids' => 1,
            'sizing' => 'sized', 'image' => $this->fakeImage(),
            'sizes' => ['2-3Y' => 1, '4-5Y' => 2, '6-7Y' => 3, '8-9Y' => 0, '10-11Y' => 0, '12-13Y' => 4],
        ])->assertRedirect('/admin/products');

        $product = Product::with('sizes')->firstWhere('name', 'ES Kids Hoodie');
        $this->assertTrue($product->is_kids);
        $this->assertSame(ProductSize::KIDS_SIZES, $product->sizes->pluck('size')->all());
        $this->assertSame(10, $product->stock_quantity);
    }

    public function test_adult_sizes_are_rejected_for_a_kids_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'ES Kids Wrong', 'category' => 'tshirt', 'price' => 249, 'is_kids' => 1,
            'sizing' => 'sized', 'sizes' => ['S' => 1], 'image' => $this->fakeImage(),
        ])->assertSessionHasErrors('sizes');
    }

    public function test_admin_can_move_a_product_into_kids(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tee = Product::factory()->withSizes(['S' => 2, 'M' => 2])->create();

        $this->actingAs($admin)->get("/admin/products/{$tee->id}/edit")->assertOk()->assertSee('name="is_kids"', false);

        $this->actingAs($admin)->put("/admin/products/{$tee->id}", [
            'name' => $tee->name, 'category' => 'tshirt', 'price' => 200, 'is_kids' => 1,
            'sizing' => 'sized', 'sizes' => ['4-5Y' => 3, '6-7Y' => 1],
        ])->assertRedirect();

        $tee->refresh()->load('sizes');
        $this->assertTrue($tee->is_kids);
        $this->assertSame(ProductSize::KIDS_SIZES, $tee->sizes->pluck('size')->all());
        $this->assertSame(4, $tee->stock_quantity);
        $this->assertSame(1, Product::kids()->count());
    }
}
