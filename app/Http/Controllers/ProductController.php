<?php
// Hlomla Magopeni 218070349 — Eben Supply | Group KN3

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('sizes')->withTrashed(false);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->latest()->paginate(12)->withQueryString();
        $category = $request->category;

        return view('products.index', compact('products', 'category'));
    }

    public function show(Product $product)
    {
        $product->load('sizes');
        $related = Product::with('sizes')
            ->where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();
        return view('products.show', compact('product', 'related'));
    }
}