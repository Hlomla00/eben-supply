<?php
// PRT362S — Eben Supply | Group KN3

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kids is a section across the existing categories (a kids tee is still a
     * T-shirt), so it is a flag on products. Sizes widen from the adult enum
     * to a short string so kids sizes such as "4-5Y" can be stocked per size.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_kids')->default(false)->after('category')->index();
        });

        Schema::table('product_sizes', function (Blueprint $table) {
            $table->string('size', 20)->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_sizes', function (Blueprint $table) {
            $table->enum('size', ['S', 'M', 'L', 'XL', 'XXL'])->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_kids']);
            $table->dropColumn('is_kids');
        });
    }
};
