<?php
// PRT362S — Eben Supply | Group KN3

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();            // stored upper-case
            $table->enum('type', ['percentage', 'fixed']);
            $table->decimal('value', 10, 2);
            $table->decimal('min_spend', 10, 2)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('per_customer_limit')->nullable();
            $table->unsignedInteger('times_used')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Orders keep the money breakdown as charged, so later price or code
        // changes never alter a past order.
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->nullable()->after('fulfillment');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('subtotal');
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('discount_amount');
            $table->foreignId('promo_code_id')->nullable()->after('delivery_fee')->constrained()->nullOnDelete();
            $table->string('promo_code', 40)->nullable()->after('promo_code_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('discount_amount', 10, 2)->default(0)->after('unit_price');
        });

        // Backfill existing orders: no promo codes existed, delivery was R60.
        DB::table('orders')->whereNull('subtotal')->orderBy('id')->each(function ($order) {
            $subtotal = DB::table('order_items')->where('order_id', $order->id)
                ->sum(DB::raw('quantity * unit_price'));
            DB::table('orders')->where('id', $order->id)->update([
                'subtotal'     => $subtotal,
                'delivery_fee' => $order->fulfillment === 'delivery' ? 60.00 : 0,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('discount_amount');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promo_code_id');
            $table->dropColumn(['subtotal', 'discount_amount', 'delivery_fee', 'promo_code']);
        });

        Schema::dropIfExists('promo_codes');
    }
};
