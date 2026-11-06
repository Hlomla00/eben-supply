<?php
// Hlomla Magopeni 218070349 — Eben Supply | Group KN3
// Adds the "paid" and "cancelled" order statuses. The column default becomes
// "paid"; checkout currently sets "pending" itself, and "pending" stays valid
// so existing orders still load.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'confirmed', 'ready', 'collected', 'cancelled'])
                ->default('paid')
                ->change();
        });
    }

    public function down(): void
    {
        DB::table('orders')->whereIn('status', ['paid', 'cancelled'])->update(['status' => 'pending']);

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'ready', 'collected'])
                ->default('pending')
                ->change();
        });
    }
};
