<?php

namespace Tests\Feature;

use App\Models\Product;
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
        $this->assertSame(3, $cap->stock_quantity);
        $this->assertEquals(999, $cap->price);
    }

    public function test_reseeding_repairs_a_previously_mismatched_image(): void
    {
        $this->seed(ProductSeeder::class);
        $cap = Product::firstWhere('name', 'ES Dad Cap – Signature');
        // An install seeded by an earlier version: illustration and old description.
        $cap->update(['image_path' => 'images/products/cap-ivory.svg', 'description' => 'Tonal ES embroidery.', 'stock_quantity' => 7]);

        $this->seed(ProductSeeder::class);

        $cap->refresh();
        $this->assertSame('images/products/photos/grey-cap.jpg', $cap->image_path);
        $this->assertStringContainsString('charcoal', $cap->description);
        $this->assertSame(7, $cap->stock_quantity);
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

    public function test_no_product_uses_a_drawing_or_placeholder(): void
    {
        $this->seed(ProductSeeder::class);

        foreach (Product::all() as $product) {
            $this->assertMatchesRegularExpression('/\.(jpe?g|png|webp)$/i', $product->image_path, $product->name);
        }
        $this->assertSame([], glob(public_path('images/products/*.svg')));
        $this->assertSame([], glob(public_path('images/products/*/*.svg')));
    }

    public function test_reseeding_upgrades_an_install_from_the_earlier_catalogue(): void
    {
        // As seeded by the previous version: drawings, old name, old category.
        $oldKids = Product::factory()->create(['name' => 'ES Kids Tee – Sand', 'is_kids' => true,
            'image_path' => 'images/products/kids/kids-tee-sand.svg']);
        $customised = Product::factory()->create(['name' => 'ES Snapback – Classic Black',
            'image_path' => 'images/products/uploads/real-photo.jpg']);
        $zip = Product::factory()->create(['name' => 'ES Zip Tote – Black', 'category' => 'tote_bag',
            'image_path' => 'images/products/tote-black.svg', 'stock_quantity' => 9]);
        $jersey = Product::factory()->create(['name' => 'ES Jersey – Street Edition', 'category' => 'tshirt',
            'image_path' => 'images/products/jerseys.jpeg']);
        \App\Models\CartItem::create(['user_id' => \App\Models\User::factory()->create()->id, 'product_id' => $oldKids->id, 'quantity' => 1]);

        $this->seed(ProductSeeder::class);

        $this->assertSoftDeleted($oldKids);
        $this->assertDatabaseMissing('cart_items', ['product_id' => $oldKids->id]);
        $this->assertNotSoftDeleted($customised);                       // an admin gave it a real photo
        $zip->refresh();
        $this->assertSame('ES Logo Tote – Black', $zip->name);
        $this->assertSame('images/products/photos/black-e-tote.jpg', $zip->image_path);
        $this->assertSame(9, $zip->stock_quantity);
        $this->assertSame('sweater', $jersey->fresh()->category);
        $this->assertSame(1, Product::where('name', 'ES Logo Tote – Black')->count());
    }

    public function test_missing_image_falls_back_to_existing_placeholder(): void
    {
        $product = Product::create([
            'name' => 'No Image Tee', 'category' => 'tshirt', 'price' => 100, 'stock_quantity' => 1,
        ]);

        $this->assertStringEndsWith(Product::PLACEHOLDER_IMAGE, $product->image_url);
        $this->assertFileExists(public_path(Product::PLACEHOLDER_IMAGE));
    }
}
