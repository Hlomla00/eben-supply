<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_seeded_product_has_its_own_existing_image(): void
    {
        $this->seed(ProductSeeder::class);

        $products = Product::all();
        $this->assertNotEmpty($products);

        foreach ($products as $product) {
            $this->assertFileExists(public_path($product->image_path), $product->name);
        }

        $this->assertSame($products->count(), $products->pluck('image_path')->unique()->count(),
            'Two products share the same image');
    }

    public function test_cap_and_tote_products_use_matching_images(): void
    {
        $this->seed(ProductSeeder::class);

        foreach (Product::where('category', 'cap')->get() as $cap) {
            $this->assertMatchesRegularExpression('/cap/', $cap->image_path, $cap->name);
        }

        foreach (Product::where('category', 'tote_bag')->get() as $tote) {
            $this->assertMatchesRegularExpression('/tote/', $tote->image_path, $tote->name);
        }

        $this->assertSame('images/products/product 1.jpeg',
            Product::firstWhere('name', 'ES Classic Tee – Jet Black')->image_path);
    }

    public function test_reseeding_does_not_reset_live_stock_or_prices(): void
    {
        $this->seed(ProductSeeder::class);
        $cap = Product::firstWhere('name', 'ES Dad Cap – Signature');
        $cap->update(['stock_quantity' => 3, 'price' => 999]);

        $this->seed(ProductSeeder::class);

        $cap->refresh();
        $this->assertSame(3, (int) $cap->stock_quantity);
        $this->assertEquals(999, $cap->price);
    }

    public function test_reseeding_repairs_a_previously_mismatched_image(): void
    {
        $this->seed(ProductSeeder::class);
        $cap = Product::firstWhere('name', 'ES Dad Cap – Signature');
        // A database seeded by the earlier catalogue, which showed the wrong photo.
        $cap->update(['image_path' => 'images/products/product 2.jpeg', 'stock_quantity' => 7]);

        $this->seed(ProductSeeder::class);

        $cap->refresh();
        $this->assertSame('images/products/photos/grey-cap.jpg', $cap->image_path);
        $this->assertSame(7, (int) $cap->stock_quantity);
    }

    public function test_a_custom_image_set_by_an_admin_is_never_replaced(): void
    {
        $this->seed(ProductSeeder::class);
        $cap = Product::firstWhere('name', 'ES Dad Cap – Signature');
        $cap->update(['image_path' => 'images/products/uploads/my-own.jpg', 'description' => 'Admin text']);

        $this->seed(ProductSeeder::class);

        $this->assertSame('images/products/uploads/my-own.jpg', $cap->fresh()->image_path);
        $this->assertSame('Admin text', $cap->fresh()->description);
    }

    public function test_reseeding_upgrades_a_database_from_the_earlier_catalogue(): void
    {
        $retired = Product::create(['name' => 'ES Snapback – Classic Black', 'category' => 'cap', 'price' => 249,
            'stock_quantity' => 5, 'image_path' => 'images/products/product 1.jpeg']);
        $customised = Product::create(['name' => 'ES 5-Panel – Street Cap', 'category' => 'cap', 'price' => 249,
            'stock_quantity' => 5, 'image_path' => 'images/products/uploads/real-photo.jpg']);
        $zip = Product::create(['name' => 'ES Zip Tote – Black', 'category' => 'tote_bag', 'price' => 199,
            'stock_quantity' => 9, 'image_path' => 'images/products/product 3.jpeg']);
        CartItem::create(['user_id' => User::factory()->create()->id, 'product_id' => $retired->id, 'quantity' => 1]);

        $this->seed(ProductSeeder::class);

        $this->assertSoftDeleted($retired);
        $this->assertDatabaseMissing('cart_items', ['product_id' => $retired->id]);
        $this->assertNotSoftDeleted($customised);   // an admin gave it a real photo
        $zip->refresh();
        $this->assertSame('ES Logo Tote – Black', $zip->name);
        $this->assertSame('images/products/photos/black-e-tote.jpg', $zip->image_path);
        $this->assertSame(9, (int) $zip->stock_quantity);
    }

    public function test_missing_image_falls_back_to_existing_placeholder(): void
    {
        $product = Product::create([
            'name' => 'No Image Tee', 'category' => 'tshirt', 'price' => 100, 'stock_quantity' => 1,
        ]);

        $this->assertStringEndsWith(Product::PLACEHOLDER_IMAGE, $product->image_url);
        $this->assertFileExists(public_path(Product::PLACEHOLDER_IMAGE));
    }

    public function test_category_labels_come_from_one_list(): void
    {
        $tote = new Product(['category' => 'tote_bag']);

        $this->assertSame('Tote Bag', $tote->category_label);
        $this->assertSame('badge-tote_bag', $tote->category_badge);
        $this->assertSame('Tote Bags', Product::categoryPluralLabel('tote_bag'));
    }
}
