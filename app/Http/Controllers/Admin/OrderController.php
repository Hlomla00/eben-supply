<?php
// 230443370 Katlego Malaka | Group KN3

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders   = $query->paginate(20)->withQueryString();
        $statuses = ['pending', 'confirmed', 'ready', 'collected'];

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    public function show(Order $order)
    {
        $order->load('items.product', 'user');
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,ready,collected',
        ]);

        $order->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Order status updated.');
    }
}
