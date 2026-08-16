<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |----------------------------------------------------------------------
        | Real product images already in public/images/products/
        |----------------------------------------------------------------------
        | T-Shirts  → G-T-shirt front.jpeg, G-T-shirt back.jpeg, shirt.jpeg,
        |             jerseys.jpeg, sweaters.jpeg
        | Caps      → product 1.jpeg, product 2.jpeg, front picture.jpeg,
        |             black model.jpeg
        | Tote Bags → tote bag.jpeg, product 3.jpeg, supply here.jpeg
        */

        $products = [
            // ── T-SHIRTS ──────────────────────────────────────────────────────
            [
                'name'           => 'ES Classic Tee – Jet Black',
                'description'    => 'Our signature heavyweight cotton tee in jet black. Bold EBEN SUPPLY chest print, reinforced double-needle stitching at seams and hem. Unisex relaxed fit — a wardrobe cornerstone.',
                'category'       => 'tshirt',
                'price'          => 289.00,
                'stock_quantity' => 54,
                'image_path'     => 'images/products/G-T-shirt front.jpeg',
                'is_featured'    => true,
                'sizes'          => ['S' => 10, 'M' => 18, 'L' => 14, 'XL' => 8, 'XXL' => 4],
            ],
            [
                'name'           => 'ES Classic Tee – Pure White',
                'description'    => 'Clean pure-white essential tee with a tonal EBEN SUPPLY print. 100% combed ring-spun cotton, pre-shrunk. Pairs with everything in your rotation.',
                'category'       => 'tshirt',
                'price'          => 289.00,
                'stock_quantity' => 44,
                'image_path'     => 'images/products/shirt.jpeg',
                'is_featured'    => true,
                'sizes'          => ['S' => 8, 'M' => 15, 'L' => 12, 'XL' => 6, 'XXL' => 3],
            ],
            [
                'name'           => 'ES Graphic Tee – Back Print',
                'description'    => 'Double-sided graphic tee with a bold back print design. Heavyweight 220gsm cotton, ribbed collar, boxy silhouette. Limited quantities.',
                'category'       => 'tshirt',
                'price'          => 319.00,
                'stock_quantity' => 35,
                'image_path'     => 'images/products/G-T-shirt back.jpeg',
                'is_featured'    => true,
                'sizes'          => ['S' => 6, 'M' => 12, 'L' => 10, 'XL' => 5, 'XXL' => 2],
            ],
            [
                'name'           => 'ES Jersey – Street Edition',
                'description'    => 'Premium streetwear jersey with breathable mesh panelling and embroidered ES logo. Relaxed fit, drop shoulders. Perfect for layering or solo wear.',
                'category'       => 'tshirt',
                'price'          => 349.00,
                'stock_quantity' => 24,
                'image_path'     => 'images/products/jerseys.jpeg',
                'is_featured'    => false,
                'sizes'          => ['S' => 4, 'M' => 9, 'L' => 7, 'XL' => 3, 'XXL' => 1],
            ],
            [
                'name'           => 'ES Sweater – Woodstock Crew',
                'description'    => 'Midweight fleece crew-neck sweater. Brushed inner lining, ribbed cuffs and hem. Screen-printed EBEN SUPPLY arc logo on chest. Autumn essential.',
                'category'       => 'tshirt',
                'price'          => 399.00,
                'stock_quantity' => 29,
                'image_path'     => 'images/products/sweaters.jpeg',
                'is_featured'    => true,
                'sizes'          => ['S' => 5, 'M' => 10, 'L' => 8, 'XL' => 4, 'XXL' => 2],
            ],

            // ── CAPS ──────────────────────────────────────────────────────────
            [
                'name'           => 'ES Snapback – Classic Black',
                'description'    => 'Structured 6-panel snapback with embroidered ES logo on the front panel. Flat brim, adjustable snap closure at back. One size fits most.',
                'category'       => 'cap',
                'price'          => 249.00,
                'stock_quantity' => 25,
                'image_path'     => 'images/products/product 1.jpeg',
                'is_featured'    => true,
                'sizes'          => [],
            ],
            [
                'name'           => 'ES Dad Cap – Signature',
                'description'    => 'Relaxed low-profile dad cap in premium canvas. Tonal ES embroidery on front. Antique brass buckle strap at back for adjustable fit.',
                'category'       => 'cap',
                'price'          => 229.00,
                'stock_quantity' => 25,
                'image_path'     => 'images/products/product 2.jpeg',
                'is_featured'    => true,
                'sizes'          => [],
            ],
            [
                'name'           => 'ES Cap – Front Logo Edition',
                'description'    => 'Clean minimal cap with an oversized ES front logo embroidery. Structured crown, pre-curved brim. Available in one size with adjustable closure.',
                'category'       => 'cap',
                'price'          => 239.00,
                'stock_quantity' => 20,
                'image_path'     => 'images/products/front picture.jpeg',
                'is_featured'    => false,
                'sizes'          => [],
            ],
            [
                'name'           => 'ES Cap – Lifestyle Series',
                'description'    => 'Lifestyle-edition cap shot on location in Woodstock. Washed canvas with a lived-in feel. Embroidered ES monogram with tonal stitching.',
                'category'       => 'cap',
                'price'          => 219.00,
                'stock_quantity' => 18,
                'image_path'     => 'images/products/black model.jpeg',
                'is_featured'    => false,
                'sizes'          => [],
            ],
            [
                'name'           => 'ES 5-Panel – Street Cap',
                'description'    => 'Lightweight 5-panel camp cap. Fabric strap closure, structured front panel with ES badge. Ideal for outdoor wear and everyday carry.',
                'category'       => 'cap',
                'price'          => 199.00,
                'stock_quantity' => 22,
                'image_path'     => 'images/products/product 1.jpeg',
                'is_featured'    => false,
                'sizes'          => [],
            ],

            // ── TOTE BAGS ─────────────────────────────────────────────────────
            [
                'name'           => 'ES Market Tote – Natural Canvas',
                'description'    => 'Heavy-duty 12oz natural canvas tote with screen-printed EBEN SUPPLY branding. Long reinforced handles, gusseted base. Holds up to 15kg.',
                'category'       => 'tote_bag',
                'price'          => 199.00,
                'stock_quantity' => 30,
                'image_path'     => 'images/products/tote bag.jpeg',
                'is_featured'    => true,
                'sizes'          => [],
            ],
            [
                'name'           => 'ES Zip Tote – Black',
                'description'    => 'Premium zip-top tote in water-resistant black canvas. Interior pocket, branded zipper pull. Holds everything you need for a full day out.',
                'category'       => 'tote_bag',
                'price'          => 219.00,
                'stock_quantity' => 25,
                'image_path'     => 'images/products/product 3.jpeg',
                'is_featured'    => true,
                'sizes'          => [],
            ],
            [
                'name'           => 'ES Supply Tote – Branded',
                'description'    => 'Statement tote with a full-panel EBEN SUPPLY screen print. Cotton canvas construction, flat bottom, short and long handle options.',
                'category'       => 'tote_bag',
                'price'          => 199.00,
                'stock_quantity' => 30,
                'image_path'     => 'images/products/supply here.jpeg',
                'is_featured'    => true,
                'sizes'          => [],
            ],
            [
                'name'           => 'ES Large Tote – Everyday',
                'description'    => 'Oversized canvas tote for beach days, market runs, or gym carry. Dark EBEN SUPPLY print, padded base, extra-long handles that fit over the shoulder.',
                'category'       => 'tote_bag',
                'price'          => 229.00,
                'stock_quantity' => 20,
                'image_path'     => 'images/products/tote bag.jpeg',
                'is_featured'    => true,
                'sizes'          => [],
            ],
            [
                'name'           => 'ES Mini Tote – Compact',
                'description'    => 'Compact canvas mini tote for the essentials — keys, wallet, phone. White EBEN SUPPLY print, short carry handles. Great as a gift too.',
                'category'       => 'tote_bag',
                'price'          => 179.00,
                'stock_quantity' => 28,
                'image_path'     => 'images/products/product 3.jpeg',
                'is_featured'    => false,
                'sizes'          => [],
            ],
        ];

        foreach ($products as $data) {
            $sizes = $data['sizes'];
            unset($data['sizes']);

            $product = Product::updateOrCreate(
                ['name' => $data['name']],
                $data
            );

            foreach ($sizes as $size => $qty) {
                ProductSize::updateOrCreate(
                    ['product_id' => $product->id, 'size' => $size],
                    ['stock_quantity' => $qty]
                );
            }
        }
    }
}
