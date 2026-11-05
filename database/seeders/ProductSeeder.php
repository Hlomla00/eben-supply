<?php
// PRT362S — Eben Supply | Group KN3

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Images earlier versions of this seeder gave each product. Products still on
     * one of these get the current photo and its matching description.
     */
    private const MISMATCHED_IMAGES = [
        'ES Classic Tee – Jet Black'      => ['images/products/G-T-shirt front.jpeg', 'images/products/tshirt-black.svg'],
        'ES Classic Tee – Pure White'     => ['images/products/shirt.jpeg', 'images/products/tshirt-white.svg'],
        'ES Dad Cap – Signature'          => ['images/products/product 2.jpeg', 'images/products/cap-ivory.svg'],
        'ES Cap – Front Logo Edition'     => ['images/products/front picture.jpeg', 'images/products/cap-navy.svg'],
        'ES Cap – Lifestyle Series'       => ['images/products/black model.jpeg', 'images/products/cap-olive.svg'],
        'ES Market Tote – Natural Canvas' => ['images/products/tote bag.jpeg'],
        'ES Logo Tote – Black'            => ['images/products/product 3.jpeg', 'images/products/tote-black.svg'],
        'ES Supply Tote – Branded'        => ['images/products/supply here.jpeg', 'images/products/tote-sage.svg'],
        'ES Large Tote – Everyday'        => ['images/products/tote bag.jpeg', 'images/products/tote-cream.svg'],
        'ES Mini Tote – Compact'          => ['images/products/product 3.jpeg', 'images/products/tote-slate.svg'],
    ];

    /** Products renamed to match their photo: old name => new name. */
    private const RENAMED = [
        'ES Zip Tote – Black' => 'ES Logo Tote – Black',
    ];

    /**
     * Products with no real photo, removed from the shop (soft delete, so past
     * orders keep them) — but only while they still carry the seeded drawing.
     */
    private const RETIRED = [
        'ES Snapback – Classic Black'   => ['images/products/cap-black.svg', 'images/products/product 1.jpeg'],
        'ES 5-Panel – Street Cap'       => ['images/products/cap-grey.svg', 'images/products/product 1.jpeg'],
    ];

    public function run(): void
    {
        /*
        |----------------------------------------------------------------------
        | Every product uses a real photo from public/images/products/.
        |----------------------------------------------------------------------
        | product 1.jpeg (folded black tees), supply here.jpeg (cream tee),
        | G-T-shirt back.jpeg (green back-print tee), jerseys.jpeg (grey
        | quarter-zip), sweaters.jpeg (crew sweaters) and photos/*.jpg
        | (web-sized cap and tote product shots).
        */

        $products = [
            // ── T-SHIRTS ──────────────────────────────────────────────────────
            [
                'name'           => 'ES Classic Tee – Jet Black',
                'description'    => 'Signature heavyweight cotton tee in jet black with a white Eben Supply script and "Made in Cape Town" chest print. Reinforced double-needle stitching, unisex relaxed fit.',
                'category'       => 'tshirt',
                'price'          => 289.00,
                'stock_quantity' => 54,
                'image_path'     => 'images/products/product 1.jpeg',
                'is_featured'    => true,
                'sizes'          => ['S' => 10, 'M' => 18, 'L' => 14, 'XL' => 8, 'XXL' => 4],
            ],
            [
                'name'           => 'ES Classic Tee – Pure White',
                'description'    => 'Off-white essential tee with the "Eben Supply | here." chest print — from the streets of Cape Town to here in Joburg. Combed ring-spun cotton, ribbed collar.',
                'category'       => 'tshirt',
                'price'          => 289.00,
                'stock_quantity' => 44,
                'image_path'     => 'images/products/supply here.jpeg',
                'is_featured'    => true,
                'sizes'          => ['S' => 8, 'M' => 15, 'L' => 12, 'XL' => 6, 'XXL' => 3],
            ],
            [
                'name'           => 'ES Graphic Tee – Back Print',
                'description'    => 'Forest-green boxy tee with a large Eben Supply "Est. 2015 · Made in Cape Town" back print. Heavyweight 220gsm cotton, ribbed collar. Limited quantities.',
                'category'       => 'tshirt',
                'price'          => 319.00,
                'stock_quantity' => 35,
                'image_path'     => 'images/products/G-T-shirt back.jpeg',
                'is_featured'    => true,
                'sizes'          => ['S' => 6, 'M' => 12, 'L' => 10, 'XL' => 5, 'XXL' => 2],
            ],

            [
                'name'           => 'ES Jersey – Street Edition',
                'description'    => 'Grey marl quarter-zip jersey with green Eben Supply "Made in Cape Town" script embroidery. Brushed fleece, stand collar, relaxed fit.',
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
                'name'           => 'ES Dad Cap – Signature',
                'description'    => 'Relaxed low-profile dad cap in charcoal cotton twill. Cream "Eben Supply · Made in Cape Town" script embroidery on the front. Adjustable strap at back.',
                'category'       => 'cap',
                'price'          => 229.00,
                'stock_quantity' => 25,
                'image_path'     => 'images/products/photos/grey-cap.jpg',
                'is_featured'    => true,
                'sizes'          => [],
            ],
            [
                'name'           => 'ES Cap – Front Logo Edition',
                'description'    => 'Forest-green dad cap with an oversized cream E logo and SUPPLY embroidered on the front. Pre-curved brim, adjustable strap. One size fits most.',
                'category'       => 'cap',
                'price'          => 239.00,
                'stock_quantity' => 20,
                'image_path'     => 'images/products/photos/green-cap.jpg',
                'is_featured'    => false,
                'sizes'          => [],
            ],
            [
                'name'           => 'ES Cap – Lifestyle Series',
                'description'    => 'Two-tone cap with a cream crown and forest-green brim, eyelets and button. Embroidered green Eben Supply "Est. 2015 ZA" E badge on the front. Adjustable strap.',
                'category'       => 'cap',
                'price'          => 219.00,
                'stock_quantity' => 18,
                'image_path'     => 'images/products/photos/cream-green-cap.jpg',
                'is_featured'    => false,
                'sizes'          => [],
            ],
            [
                'name'           => 'ES Script Cap – Navy',
                'description'    => 'Navy cotton-twill dad cap with a raised red Eben Supply script and "Cape Town, South Africa" in cream. Low profile, pre-curved brim, adjustable strap. One size fits most.',
                'category'       => 'cap',
                'price'          => 239.00,
                'stock_quantity' => 20,
                'image_path'     => 'images/products/photos/navy-cap.jpg',
                'is_featured'    => true,
                'sizes'          => [],
            ],
            [
                'name'           => 'ES Script Cap – Maroon',
                'description'    => 'Deep maroon dad cap with a gold Eben Supply script and "Cape Town, South Africa" embroidery — a warm colourway for the cooler months. Low profile, adjustable strap.',
                'category'       => 'cap',
                'price'          => 239.00,
                'stock_quantity' => 20,
                'image_path'     => 'images/products/photos/maroon-cap.jpg',
                'is_featured'    => false,
                'sizes'          => [],
            ],

            // ── TOTE BAGS ─────────────────────────────────────────────────────
            [
                'name'           => 'ES Market Tote – Natural Canvas',
                'description'    => 'Heavy-duty natural canvas tote with a green "Est. 2015 · Cape Town, ZA" E SUPPLY badge print. Long reinforced handles, gusseted base. Holds up to 15kg.',
                'category'       => 'tote_bag',
                'price'          => 199.00,
                'stock_quantity' => 30,
                'image_path'     => 'images/products/photos/natural-badge-tote.jpg',
                'is_featured'    => true,
                'sizes'          => [],
            ],
            [
                'name'           => 'ES Logo Tote – Black',
                'description'    => 'Black canvas tote with a cream E SUPPLY "Est. 2015 · CPT//SSP" logo print. Long handles and a roomy flat body for a full day out.',
                'category'       => 'tote_bag',
                'price'          => 219.00,
                'stock_quantity' => 25,
                'image_path'     => 'images/products/photos/black-e-tote.jpg',
                'is_featured'    => true,
                'sizes'          => [],
            ],
            [
                'name'           => 'ES Supply Tote – Branded',
                'description'    => 'Statement natural canvas tote with a full-panel red Eben Supply "Cape Town, South Africa" stamp print. Long handles, gusseted base.',
                'category'       => 'tote_bag',
                'price'          => 199.00,
                'stock_quantity' => 30,
                'image_path'     => 'images/products/photos/natural-stamp-tote.jpg',
                'is_featured'    => true,
                'sizes'          => [],
            ],
            [
                'name'           => 'ES Large Tote – Everyday',
                'description'    => 'Roomy forest-green canvas tote for market runs, beach days or gym carry. Cream E SUPPLY print, long handles that fit over the shoulder.',
                'category'       => 'tote_bag',
                'price'          => 229.00,
                'stock_quantity' => 20,
                'image_path'     => 'images/products/photos/green-e-tote.jpg',
                'is_featured'    => true,
                'sizes'          => [],
            ],
            [
                'name'           => 'ES Mini Tote – Compact',
                'description'    => 'Navy canvas tote with a cream Eben Supply "Made in Cape Town" script print. Light and flat for the everyday essentials — keys, wallet, phone.',
                'category'       => 'tote_bag',
                'price'          => 179.00,
                'stock_quantity' => 28,
                'image_path'     => 'images/products/photos/navy-script-tote.jpg',
                'is_featured'    => false,
                'sizes'          => [],
            ],
        ];

        $this->upgradeExistingCatalogue();

        foreach ($products as $data) {
            $sizes = $data['sizes'];
            unset($data['sizes']);

            // Seeding can run again on a live database, so never overwrite live
            // stock, prices or admin edits on products that already exist.
            $product = Product::withTrashed()->firstWhere('name', $data['name']);

            if (!$product) {
                $product = Product::create($data);

                foreach ($sizes as $size => $qty) {
                    ProductSize::create([
                        'product_id'     => $product->id,
                        'size'           => $size,
                        'stock_quantity' => $qty,
                    ]);
                }
                continue;
            }

            // Repair images that were seeded against the wrong product, or as a drawing.
            if (in_array($product->image_path, self::MISMATCHED_IMAGES[$data['name']] ?? [], true)) {
                $product->update(['image_path' => $data['image_path'], 'description' => $data['description']]);
            }
        }
    }

    /**
     * Bring databases seeded by earlier versions in line: renames, and products
     * without a real photo removed — only where the seeded values are untouched.
     */
    private function upgradeExistingCatalogue(): void
    {
        foreach (self::RENAMED as $old => $new) {
            $product = Product::withTrashed()->firstWhere('name', $old);
            if ($product && !Product::withTrashed()->where('name', $new)->exists()
                && in_array($product->image_path, self::MISMATCHED_IMAGES[$new] ?? [], true)) {
                $product->update(['name' => $new]);
            }
        }

        foreach (self::RETIRED as $name => $seededImages) {
            $product = Product::firstWhere('name', $name);
            if ($product && in_array($product->image_path, $seededImages, true)) {
                CartItem::where('product_id', $product->id)->delete();
                $product->delete();
            }
        }
    }
}
