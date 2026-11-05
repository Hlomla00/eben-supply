<?php
// PRT362S — Eben Supply | Group KN3

namespace App\Models;

use Carbon\CarbonInterface;
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
        'sweater'  => ['Sweatshirt', 'Sweatshirts & Hoodies', 'badge-sweater'],
        'pants'    => ['Pants', 'Pants', 'badge-pants'],
        'cap'      => ['Cap', 'Caps', 'badge-cap'],
        'tote_bag' => ['Tote Bag', 'Tote Bags', 'badge-tote_bag'],
    ];

    /** Shown if a product's image is missing: the brand logo, never a drawing. */
    public const PLACEHOLDER_IMAGE = 'images/products/logo.jpg';

    public const LOW_STOCK_THRESHOLD = 5;

    protected $fillable = [
        'name',
        'description',
        'category',
        'is_kids',
        'price',
        'sale_price',
        'sale_starts_at',
        'sale_ends_at',
        'stock_quantity',
        'image_path',
        'is_featured',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'sale_price'     => 'decimal:2',
        'sale_starts_at' => 'datetime',
        'sale_ends_at'   => 'datetime',
        'is_featured'    => 'boolean',
        'is_kids'        => 'boolean',
        'stock_quantity' => 'integer',
    ];

    public function sizes()
    {
        // Rows are created in display order (S → XXL), so id order is size order.
        return $this->hasMany(ProductSize::class)->orderBy('id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public static function categoryKeys(): array
    {
        return array_keys(self::CATEGORIES);
    }

    /**
     * Category keys that have at least one live product in the section, in
     * display order, so menus and pills never link to an empty page.
     */
    public static function categoriesInUse(bool $kids = false): array
    {
        // once(): cached per request and per argument.
        return once(fn () => array_values(array_intersect(
            self::categoryKeys(),
            self::where('is_kids', $kids)->distinct()->pluck('category')->all()
        )));
    }

    public static function categoryPluralLabel(?string $category): ?string
    {
        return self::CATEGORIES[$category][1] ?? null;
    }

    /**
     * A sale applies when it has a price below the normal price and the
     * moment falls inside its optional start/end dates (end is exclusive).
     */
    public function isOnSale(?CarbonInterface $at = null): bool
    {
        if ($this->sale_price === null || (float) $this->sale_price <= 0 || (float) $this->sale_price >= (float) $this->price) {
            return false;
        }

        $at ??= now();

        if ($this->sale_starts_at && $at->lt($this->sale_starts_at)) {
            return false;
        }

        return !($this->sale_ends_at && $at->gte($this->sale_ends_at));
    }

    /**
     * The price a customer pays right now. Every page and calculation uses this.
     */
    public function getCurrentPriceAttribute(): float
    {
        return $this->isOnSale() ? (float) $this->sale_price : (float) $this->price;
    }

    public function getSalePercentOffAttribute(): int
    {
        return $this->isOnSale() ? (int) round((1 - (float) $this->sale_price / (float) $this->price) * 100) : 0;
    }

    /**
     * 'active', 'scheduled', 'ended' or null when no sale is set (admin display).
     */
    public function getSaleStatusAttribute(): ?string
    {
        if ($this->sale_price === null) {
            return null;
        }
        if ($this->isOnSale()) {
            return 'active';
        }

        return $this->sale_starts_at && now()->lt($this->sale_starts_at) ? 'scheduled' : 'ended';
    }

    /**
     * The size set this product is stocked in (kids or adult).
     */
    public function sizeOptions(): array
    {
        return ProductSize::sizesFor((bool) $this->is_kids);
    }

    // Used by the current CartController until Member 2's cart service is on main.
    public function availableStock(?string $size): int
    {
        return $this->stockFor($size);
    }

    public function hasSizes(): bool
    {
        return $this->sizes->isNotEmpty();
    }

    /**
     * Units available to buy: per size for sized products, product total otherwise.
     */
    public function stockFor(?string $size): int
    {
        if ($this->hasSizes()) {
            return (int) ($this->sizes->firstWhere('size', $size)?->stock_quantity ?? 0);
        }

        return max(0, (int) $this->stock_quantity);
    }

    public function isLowStock(): bool
    {
        if ($this->stock_quantity < self::LOW_STOCK_THRESHOLD) {
            return true;
        }

        return $this->sizes->contains(fn ($s) => $s->stock_quantity < self::LOW_STOCK_THRESHOLD);
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock_quantity <= 0) return 'out_of_stock';
        if ($this->stock_quantity < self::LOW_STOCK_THRESHOLD) return 'low_stock';
        return 'in_stock';
    }

    /**
     * Kids items fall under Kids, not the adult category: "Kids T-Shirt".
     */
    public function getCategoryLabelAttribute(): string
    {
        $label = self::CATEGORIES[$this->category][0] ?? ucfirst((string) $this->category);

        return $this->is_kids ? "Kids {$label}" : $label;
    }

    public function getCategoryPluralLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category][1] ?? ucfirst((string) $this->category);
    }

    public function getCategoryBadgeAttribute(): string
    {
        return $this->is_kids ? 'badge-kids' : (self::CATEGORIES[$this->category][2] ?? '');
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

    /**
     * Products whose sale is live right now — the same rule as isOnSale().
     */
    public function scopeOnSale($query)
    {
        $now = now();

        return $query->whereNotNull('sale_price')
            ->where('sale_price', '>', 0)
            ->whereColumn('sale_price', '<', 'price')
            ->where(fn ($q) => $q->whereNull('sale_starts_at')->orWhere('sale_starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('sale_ends_at')->orWhere('sale_ends_at', '>', $now));
    }

    public function scopeKids($query)
    {
        return $query->where('is_kids', true);
    }

    public function scopeAdult($query)
    {
        return $query->where('is_kids', false);
    }

    /**
     * SQL for the current (sale-aware) price, the same rule as isOnSale(),
     * so listings can filter and sort by the price customers actually pay.
     */
    private static function currentPriceSql(): array
    {
        $now = now()->format('Y-m-d H:i:s');

        return [
            'CASE WHEN sale_price IS NOT NULL AND sale_price > 0 AND sale_price < price'
            . ' AND (sale_starts_at IS NULL OR sale_starts_at <= ?)'
            . ' AND (sale_ends_at IS NULL OR sale_ends_at > ?)'
            . ' THEN sale_price ELSE price END',
            [$now, $now],
        ];
    }

    public function scopeCurrentPriceBetween($query, ?float $min, ?float $max)
    {
        [$sql, $bindings] = self::currentPriceSql();

        if ($min !== null) {
            // Cast the bound value: SQLite binds it as text, which would compare as a string.
            $query->whereRaw("($sql) >= CAST(? AS DECIMAL(10,2))", [...$bindings, $min]);
        }
        if ($max !== null) {
            $query->whereRaw("($sql) <= CAST(? AS DECIMAL(10,2))", [...$bindings, $max]);
        }

        return $query;
    }

    public function scopeOrderByCurrentPrice($query, string $direction = 'asc')
    {
        [$sql, $bindings] = self::currentPriceSql();

        return $query->orderByRaw("($sql) " . ($direction === 'desc' ? 'desc' : 'asc'), $bindings);
    }

    /**
     * Has the size in stock (any size set: adult or kids).
     */
    public function scopeInStockSize($query, string $size)
    {
        return $query->whereHas('sizes', fn ($s) => $s->where('size', $size)->where('stock_quantity', '>', 0));
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Products with low total stock or any size below the threshold.
     */
    public function scopeLowStock($query)
    {
        return $query->where(function ($q) {
            $q->where('stock_quantity', '<', self::LOW_STOCK_THRESHOLD)
              ->orWhereHas('sizes', fn ($s) => $s->where('stock_quantity', '<', self::LOW_STOCK_THRESHOLD));
        });
    }
}
