<?php

namespace App\Models;

use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Order emails (placed, status changed) — Hlomla Magopeni 218070349
#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'fulfillment',
        'total_amount',
        'contact_name',
        'contact_phone',
        'contact_email',
        'delivery_address',
        'payment_reference',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    // Order statuses — Hlomla Magopeni 218070349
    // Lifecycle: pending/paid -> confirmed -> ready -> collected, or cancelled
    // before collection. Checkout still creates orders as "pending"; switching
    // it to "paid" is left to the checkout owner.
    public const STATUSES = ['pending', 'paid', 'confirmed', 'ready', 'collected', 'cancelled'];

    // Steps shown on the customer's order timeline
    public const TRACKING_STEPS = ['paid', 'confirmed', 'ready', 'collected'];

    // Statuses an admin can choose; "pending" is not offered for new changes
    public const ADMIN_STATUSES = ['paid', 'confirmed', 'ready', 'collected', 'cancelled'];

    // Collected and cancelled orders are closed and cannot change again
    public function isClosed(): bool
    {
        return in_array($this->status, ['collected', 'cancelled'], true);
    }

    // Puts the ordered quantities back on the product and size stock.
    // Called inside a transaction when an order is cancelled.
    public function restock(): void
    {
        $this->loadMissing('items');

        foreach ($this->items as $item) {
            $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
            if (!$product) {
                continue;
            }

            if ($item->size !== null) {
                ProductSize::where('product_id', $product->id)
                    ->where('size', $item->size)
                    ->lockForUpdate()
                    ->increment('stock_quantity', $item->quantity);
            }

            $product->increment('stock_quantity', $item->quantity);
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'pending'   => ['label' => 'Pending',   'class' => 'bg-yellow-100 text-yellow-800'],
            'paid'      => ['label' => 'Paid',      'class' => 'status-paid'],
            'confirmed' => ['label' => 'Confirmed', 'class' => 'bg-blue-100 text-blue-800'],
            'ready'     => ['label' => 'Ready',     'class' => 'bg-green-100 text-green-800'],
            'collected' => ['label' => 'Collected', 'class' => 'bg-gray-100 text-gray-800'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'status-cancelled'],
            default     => ['label' => ucfirst($this->status), 'class' => 'bg-gray-100 text-gray-800'],
        };
    }

    public function getDeliveryFeeAttribute(): float
    {
        return $this->fulfillment === 'delivery' ? 60.00 : 0.00;
    }
}

