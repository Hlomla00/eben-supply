<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Single source for category keys and labels: [key => [singular, plural, badge class]].
     */
    public const CATEGORIES = [
        'tshirt'   => ['T-Shirt', 'T-Shirts', 'badge-tshirt'],
        'cap'      => ['Cap', 'Caps', 'badge-cap'],
        'tote_bag' => ['Tote Bag', 'Tote Bags', 'badge-tote_bag'],
    ];

    /** Shown if a product's image file is missing. */
    public const PLACEHOLDER_IMAGE = 'images/products/placeholder.jpg';

    protected $fillable = [
        'name',
        'description',
        'category',
        'price',
        'stock_quantity',
        'image_path',
        'is_featured',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'is_featured' => 'boolean',
    ];

    public function sizes()
    {
        return $this->hasMany(ProductSize::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock_quantity <= 0) return 'out_of_stock';
        if ($this->stock_quantity < 5) return 'low_stock';
        return 'in_stock';
    }

    // Units available for a size, or for the whole product if it has no sizes.
    // Returns 0 for a size the product doesn't stock.
    public function availableStock(?string $size): int
    {
        if ($this->sizes->isEmpty()) {
            return $size === null ? (int) $this->stock_quantity : 0;
        }

        return (int) ($this->sizes->firstWhere('size', $size)?->stock_quantity ?? 0);
    }

    public static function categoryPluralLabel(?string $category): ?string
    {
        return self::CATEGORIES[$category][1] ?? null;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category][0] ?? ucfirst((string) $this->category);
    }

    public function getCategoryBadgeAttribute(): string
    {
        return self::CATEGORIES[$this->category][2] ?? '';
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image_path && file_exists(public_path($this->image_path))) {
            return asset($this->image_path);
        }
        return asset(self::PLACEHOLDER_IMAGE);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
