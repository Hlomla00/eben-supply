<?php
// PRT362S — Eben Supply | Group KN3

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSize extends Model
{
    /** Display and admin-form order of sizes. */
    public const ADULT_SIZES = ['S', 'M', 'L', 'XL', 'XXL'];

    /** Kids sizes by age in years. */
    public const KIDS_SIZES = ['2-3Y', '4-5Y', '6-7Y', '8-9Y', '10-11Y', '12-13Y'];

    public static function sizesFor(bool $kids): array
    {
        return $kids ? self::KIDS_SIZES : self::ADULT_SIZES;
    }

    protected $fillable = [
        'product_id',
        'size',
        'stock_quantity',
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
