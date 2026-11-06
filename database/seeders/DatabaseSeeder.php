<?php
// PRT362S — Eben Supply | Group KN3

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
        ]);

        // Demo promo codes (some expired or used up, for testing). They give
        // real discounts, so they are only created outside production.
        if (!app()->isProduction()) {
            $this->call(PromoCodeSeeder::class);
        }
    }
}