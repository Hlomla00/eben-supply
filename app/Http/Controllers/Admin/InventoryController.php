<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        $products = Product::with('sizes')->orderBy('name')->get();

        return view('admin.inventory.index', compact('products'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'stock'    => 'array',
            'stock.*'  => 'integer|min:0',
            'sizes'    => 'array',
            'sizes.*'  => 'integer|min:0',
        ]);

        // Products without size variants — stock is edited directly.
        foreach ($request->input('stock', []) as $productId => $quantity) {
            Product::whereKey($productId)->update(['stock_quantity' => $quantity]);
        }

        // Products with size variants — update each size, then re-total the
        // parent product's stock_quantity so dashboard/low-stock badges stay accurate.
        $touchedProductIds = [];
        foreach ($request->input('sizes', []) as $sizeId => $quantity) {
            $size = ProductSize::find($sizeId);
            if (! $size) {
                continue;
            }
            $size->update(['stock_quantity' => $quantity]);
            $touchedProductIds[$size->product_id] = true;
        }

        foreach (array_keys($touchedProductIds) as $productId) {
            $total = ProductSize::where('product_id', $productId)->sum('stock_quantity');
            Product::whereKey($productId)->update(['stock_quantity' => $total]);
        }

        return redirect()->route('admin.inventory')->with('success', 'Inventory updated.');
    }
}
