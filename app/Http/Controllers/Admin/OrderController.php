<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private const STATUSES = ['pending', 'confirmed', 'ready', 'collected'];

    public function index(Request $request)
    {
        $statuses = self::STATUSES;

        $query = Order::query();
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    public function show(Order $order)
    {
        $order->load('items.product');

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', self::STATUSES),
        ]);

        $order->update(['status' => $request->status]);

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order status updated.');
    }
}
