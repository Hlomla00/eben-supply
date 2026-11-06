<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CartItem extends Model
{
    /** Most of one product and size a shopper can have in the cart. */
    public const MAX_QUANTITY = 10;

    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'size',
        'quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** The signed-in user's cart, or the guest's cart for this session. */
    public static function forCurrentShopper(): Builder
    {
        return auth()->check()
            ? static::where('user_id', auth()->id())
            : static::where('session_id', session()->getId());
    }

    /**
     * The current shopper's cart lines with their products. Lines whose
     * product has been deleted are removed, and the shopper is told which.
     */
    public static function currentItems(): Collection
    {
        $items = static::forCurrentShopper()->with('product.sizes')->oldest('id')->get();

        $gone = $items->filter(fn (self $item) => $item->product === null);
        if ($gone->isNotEmpty()) {
            static::whereIn('id', $gone->pluck('id'))->delete();
            $names = Product::onlyTrashed()->whereIn('id', $gone->pluck('product_id'))->pluck('name');
            session()->flash('error', ($names->isNotEmpty() ? $names->join(', ', ' and ') : 'An item')
                . ' is no longer available and was removed from your cart.');
        }

        return $items->diff($gone)->values();
    }

    /** Price per item now, including any running sale. */
    public function getUnitPriceAttribute(): float
    {
        return (float) ($this->product?->current_price ?? 0);
    }

    public function getLineTotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }
}
