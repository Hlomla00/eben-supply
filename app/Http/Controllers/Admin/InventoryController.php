<?php
// 230443370 Katlego Malaka | Group KN3

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        return view('admin.inventory.index', compact('products'));
        $request->validate([
            'stock'    => 'required|array',
            'stock.*'  => 'integer|min:0',
            'sizes'    => 'nullable|array',
            'sizes.*'  => 'integer|min:0',
        ]);

        // Update product-level stock
        foreach ($request->stock as $productId => $qty) {
            Product::where('id', $productId)->update(['stock_quantity' => $qty]);
        }

        // Update size-level stock
        foreach ($request->sizes ?? [] as $sizeId => $qty) {
            ProductSize::where('id', $sizeId)->update(['stock_quantity' => $qty]);
        }

        // Recalculate totals for products that have sizes
        $products = Product::with('sizes')->whereHas('sizes')->get();
        foreach ($products as $product) {
            $product->update(['stock_quantity' => $product->sizes->sum('stock_quantity')]);
        }

        return redirect()->route('admin.inventory')->with('success', 'Stock levels updated.');
    }
}
