<?php
// PRT362S — Eben Supply | Group KN3

namespace App\Http\Controllers;

use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        // Pull one featured product from each category first, then fill remaining
        // slots randomly — guarantees mix of tees, caps & totes on every load.
        $categories = Product::categoryKeys();
        $pinned = collect();
        foreach ($categories as $cat) {
            $pick = Product::featured()
                ->where('stock_quantity', '>', 0)
                ->where('category', $cat)
                ->adult()
                ->with('sizes')
                ->inRandomOrder()
                ->first();
            if ($pick) $pinned->push($pick);
        }
        $pinnedIds = $pinned->pluck('id');

        $extras = Product::featured()
            ->where('stock_quantity', '>', 0)
            ->whereNotIn('id', $pinnedIds)
            ->with('sizes')
            ->inRandomOrder()
            ->take(6 - $pinned->count())
            ->get();

        $featuredProducts = $pinned->merge($extras)->shuffle();

        $productCount  = Product::count();
        $categoryCount = count(Product::CATEGORIES);

        return view('home', compact('featuredProducts', 'productCount', 'categoryCount'));
    }
}
