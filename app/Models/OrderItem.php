<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'size',
        'quantity',
        'unit_price',
        'discount_amount',
    ];

    protected $casts = [
        'unit_price'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Past orders still show deleted products (they are soft-deleted).
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /** Name as it was when ordered, so renames and deletions don't change history. */
    public function getNameAttribute(): string
    {
        return $this->product_name ?? $this->product?->name ?? 'Product no longer available';
    }

    public function getImageUrlAttribute(): string
    {
        return $this->product?->image_url ?? asset(Product::PLACEHOLDER_IMAGE);
    }

    public function getLineTotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }
}
