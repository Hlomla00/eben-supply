<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOrders   = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalProducts = Product::count();
        $lowStockCount = Product::where('stock_quantity', '<', 5)->count();
        $recentOrders  = Order::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalOrders', 'pendingOrders', 'totalProducts', 'lowStockCount', 'recentOrders'
        ));
    }
}
