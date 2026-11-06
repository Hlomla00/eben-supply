<?php
// PRT362S — Eben Supply | Group KN3

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshot the product name on each order line so past orders still read
     * correctly after a product is renamed or deleted.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('product_name')->nullable()->after('product_id');
        });

        // Backfill existing lines from their (possibly soft-deleted) products.
        DB::table('order_items')->whereNull('product_name')->orderBy('id')->each(function ($item) {
            $name = DB::table('products')->where('id', $item->product_id)->value('name');
            DB::table('order_items')->where('id', $item->id)->update(['product_name' => $name]);
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('product_name');
        });
    }
};
