<?php
// 230443370 Katlego Malaka | Group KN3 

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOrders    = Order::count();
        // New orders start as "paid"; both still need the store to act — Hlomla Magopeni 218070349
        $pendingOrders  = Order::whereIn('status', ['pending', 'paid'])->count();
        $totalProducts  = Product::count();
        $lowStockCount  = Product::where('stock_quantity', '<', 5)->count();
        $recentOrders   = Order::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalOrders', 'pendingOrders', 'totalProducts',
            'lowStockCount', 'recentOrders'
        ));
    }
}
