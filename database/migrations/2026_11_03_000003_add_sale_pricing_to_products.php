<?php
// PRT362S — Eben Supply | Group KN3

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional sale on a product. The sale is live while the dates allow it;
     * Product::current_price is the only place that decides which price applies.
     * Order lines keep the normal price next to the price paid.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('sale_price', 10, 2)->nullable()->after('price');
            $table->timestamp('sale_starts_at')->nullable()->after('sale_price');
            $table->timestamp('sale_ends_at')->nullable()->after('sale_starts_at');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('original_unit_price', 10, 2)->nullable()->after('quantity');
        });

        // Existing orders had no sales: the normal price is what was paid.
        DB::table('order_items')->whereNull('original_unit_price')->update([
            'original_unit_price' => DB::raw('unit_price'),
        ]);
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('original_unit_price');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sale_price', 'sale_starts_at', 'sale_ends_at']);
        });
    }
};
