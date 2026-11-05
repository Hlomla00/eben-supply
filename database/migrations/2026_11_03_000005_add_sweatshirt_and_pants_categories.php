<?php
// PRT362S — Eben Supply | Group KN3

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sweatshirts/hoodies and pants get their own categories instead of
     * being filed under T-Shirts.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('category', ['tshirt', 'sweater', 'pants', 'cap', 'tote_bag'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('category', ['tshirt', 'cap', 'tote_bag'])->change();
        });
    }
};
