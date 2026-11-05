<?php
// PRT362S — Eben Supply | Group KN3

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        return $this->listing($request, Product::query(), 'products.index', null);
    }

    public function kids(Request $request)
    {
        return $this->listing($request, Product::kids(), 'kids.index', 'Kids');
    }

    /**
     * Only products whose sale is live right now; sales start and end by date.
     */
    public function sale(Request $request)
    {
        return $this->listing($request, Product::onSale(), 'sale.index', 'Sale');
    }

    public function show(Product $product)
    {
        $product->load('sizes');
        $related = Product::with('sizes')
            ->where('category', $product->category)
            ->where('is_kids', $product->is_kids)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();
        return view('products.show', compact('product', 'related'));
    }

    /** Sort options shown on every listing: key => label. */
    public const SORTS = [
        'newest'     => 'Newest',
        'price_asc'  => 'Price: low to high',
        'price_desc' => 'Price: high to low',
        'name'       => 'Name A–Z',
    ];

    /**
     * One listing page for every section (shop, kids, sale): category pills,
     * search, filters, sorting and pagination all stay within the section.
     * Kids is its own category: kids items never appear under the adult
     * T-Shirts, Caps or Tote Bags pages.
     */
    private function listing(Request $request, Builder $query, string $listingRoute, ?string $sectionTitle)
    {
        $query->with('sizes');
        $isKidsSection = $listingRoute === 'kids.index';

        // Inside Kids the pills pick a type; elsewhere "kids" is a category of its own.
        $allowed = $isKidsSection ? Product::categoryKeys() : [...Product::categoryKeys(), 'kids'];
        $category = in_array($request->category, $allowed, true) ? $request->category : null;

        if ($category === 'kids') {
            $query->kids();
        } elseif ($category) {
            $query->where('category', $category);
            if (!$isKidsSection) {
                $query->adult();
            }
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('description', 'like', $term));
        }

        // Filters: invalid values are ignored rather than emptying the page.
        $filters = $this->filters($request, $isKidsSection);

        if ($filters['size']) {
            $query->inStockSize($filters['size']);
        }
        $query->currentPriceBetween($filters['min_price'], $filters['max_price']);
        if ($filters['in_stock']) {
            $query->where('stock_quantity', '>', 0);
        }
        if ($filters['on_sale']) {
            $query->onSale();
        }

        match ($filters['sort']) {
            'price_asc'  => $query->orderByCurrentPrice('asc'),
            'price_desc' => $query->orderByCurrentPrice('desc'),
            'name'       => $query->orderBy('name'),
            default      => $query->latest(),
        };
        $query->orderBy('id');

        $products = $query->paginate(12)->withQueryString();
        $sizeOptions = $isKidsSection
            ? ProductSize::KIDS_SIZES
            : ($category === 'kids' ? ProductSize::KIDS_SIZES : [...ProductSize::ADULT_SIZES, ...($category ? [] : ProductSize::KIDS_SIZES)]);

        return view('products.index', compact('products', 'category', 'listingRoute', 'sectionTitle', 'filters', 'sizeOptions'));
    }

    private function filters(Request $request, bool $kidsOnly): array
    {
        $sizes = $kidsOnly ? ProductSize::KIDS_SIZES : [...ProductSize::ADULT_SIZES, ...ProductSize::KIDS_SIZES];
        $price = fn ($v) => is_numeric($v) && $v >= 0 ? (float) $v : null;

        $min = $price($request->min_price);
        $max = $price($request->max_price);
        if ($min !== null && $max !== null && $min > $max) {
            [$min, $max] = [$max, $min];
        }

        return [
            'size'      => in_array($request->size, $sizes, true) ? $request->size : null,
            'min_price' => $min,
            'max_price' => $max,
            'in_stock'  => $request->boolean('in_stock'),
            'on_sale'   => $request->boolean('on_sale'),
            'sort'      => array_key_exists((string) $request->sort, self::SORTS) ? $request->sort : 'newest',
        ];
    }
}
