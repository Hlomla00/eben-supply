<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name'           => 'ES Test ' . fake()->unique()->words(2, true),
            'description'    => fake()->sentence(),
            'category'       => 'cap',
            'price'          => 200.00,
            'stock_quantity' => 10,
            'image_path'     => 'images/products/photos/green-cap.jpg',
            'is_featured'    => false,
        ];
    }

    /**
     * A sized product with the given [size => stock] rows; total stock follows the sizes.
     */
    public function withSizes(array $sizes = ['S' => 5, 'M' => 5, 'L' => 5]): static
    {
        return $this->state(fn () => [
            'category'       => 'tshirt',
            'stock_quantity' => array_sum($sizes),
        ])->afterCreating(function (Product $product) use ($sizes) {
            foreach ($sizes as $size => $qty) {
                ProductSize::create(['product_id' => $product->id, 'size' => $size, 'stock_quantity' => $qty]);
            }
        });
    }
}
