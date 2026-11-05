<?php
// 230443370 Katlego Malaka | Group KN3

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders   = $query->paginate(20)->withQueryString();
        $statuses = Order::STATUSES; // paid/cancelled added — Hlomla Magopeni 218070349

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    public function show(Order $order)
    {
        $order->load('items.product', 'user');
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        // Paid/cancelled statuses, closed orders and restock on cancel —
        // Hlomla Magopeni 218070349
        $request->validate([
            'status' => ['required', Rule::in(Order::ADMIN_STATUSES)],
        ]);

        if ($request->status === $order->status) {
            return redirect()->back();
        }

        if ($order->isClosed()) {
            return redirect()->back()->with('error', "This order is {$order->status} and can no longer be changed.");
        }

        DB::transaction(function () use ($request, $order) {
            if ($request->status === 'cancelled') {
                $order->restock();
            }
            $order->update(['status' => $request->status]);
        });

        $message = $request->status === 'cancelled'
            ? 'Order cancelled and stock returned.'
            : 'Order status updated.';

        return redirect()->back()->with('success', $message);
    }
}
