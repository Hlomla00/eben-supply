<?php
// Hlomla Magopeni 218070349 — Eben Supply | Group KN3

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = $this->getCartItems();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $subtotal = $cartItems->sum(fn($i) => $i->quantity * $i->product->price);
        $user     = auth()->user();

        return view('checkout.index', compact('cartItems', 'subtotal', 'user'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'contact_name'    => 'required|string|max:100',
            'contact_phone'   => 'required|string|max:20',
            'contact_email'   => 'required|email|max:100',
            'fulfillment'     => 'required|in:pickup,delivery',
            'delivery_address'=> 'required_if:fulfillment,delivery|nullable|string|max:255',
        ]);

        $cartItems = $this->getCartItems();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $subtotal     = $cartItems->sum(fn($i) => $i->quantity * $i->product->price);
        $deliveryFee  = $request->fulfillment === 'delivery' ? 60.00 : 0.00;
        $total        = $subtotal + $deliveryFee;

        // Store pending order data in session for payment step
        session([
            'pending_order' => [
                'contact_name'     => $request->contact_name,
                'contact_phone'    => $request->contact_phone,
                'contact_email'    => $request->contact_email,
                'fulfillment'      => $request->fulfillment,
                'delivery_address' => $request->delivery_address,
                'subtotal'         => $subtotal,
                'delivery_fee'     => $deliveryFee,
                'total'            => $total,
            ]
        ]);

        return redirect()->route('checkout.payment');
    }

    public function payment()
    {
        $pending = session('pending_order');
        if (!$pending) {
            return redirect()->route('checkout.index');
        }

        $cartItems = $this->getCartItems();
        return view('checkout.payment', compact('pending', 'cartItems'));
    }

    public function confirmPayment(Request $request)
    {
        $pending = session('pending_order');
        if (!$pending) {
            return redirect()->route('checkout.index');
        }

        $cartItems = $this->getCartItems();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index');
        }

        try {
            DB::transaction(function () use ($pending, $cartItems) {
                $this->deductStock($cartItems);
                $this->createOrder($pending, $cartItems);
            });
        } catch (InsufficientStockException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        session()->forget('pending_order');
        return redirect()->route('order.confirmation');
    }

    // Locks each product so concurrent orders can't oversell, then takes the
    // ordered quantity off the size row (if any) and the product total.
    private function deductStock($cartItems): void
    {
        foreach ($cartItems as $item) {
            $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();

            if ($item->size !== null) {
                $size = ProductSize::where('product_id', $product->id)
                    ->where('size', $item->size)
                    ->lockForUpdate()
                    ->first();

                if (!$size || $size->stock_quantity < $item->quantity) {
                    throw new InsufficientStockException("Sorry, {$product->name} ({$item->size}) no longer has enough stock. Please update your cart.");
                }
                $size->decrement('stock_quantity', $item->quantity);
            } elseif ($product->stock_quantity < $item->quantity) {
                throw new InsufficientStockException("Sorry, {$product->name} no longer has enough stock. Please update your cart.");
            }

            $product->decrement('stock_quantity', $item->quantity);
        }
    }

    private function createOrder(array $pending, $cartItems): void
    {
        $order = Order::create([
            'user_id'          => auth()->id(),
            'status'           => 'pending',
            'fulfillment'      => $pending['fulfillment'],
            'total_amount'     => $pending['total'],
            'contact_name'     => $pending['contact_name'],
            'contact_phone'    => $pending['contact_phone'],
            'contact_email'    => $pending['contact_email'],
            'delivery_address' => $pending['delivery_address'],
            'payment_reference'=> 'ES-' . strtoupper(uniqid()),
        ]);

        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $item->product_id,
                'size'       => $item->size,
                'quantity'   => $item->quantity,
                'unit_price' => $item->product->price,
            ]);
        }

        // Clear cart
        if (auth()->check()) {
            CartItem::where('user_id', auth()->id())->delete();
        } else {
            CartItem::where('session_id', session()->getId())->delete();
        }

        session(['last_order_id' => $order->id]);
    }

    public function confirmation()
    {
        $orderId = session('last_order_id');
        if (!$orderId) {
            return redirect()->route('home');
        }

        $order = Order::with('items.product')->findOrFail($orderId);
        return view('checkout.confirmation', compact('order'));
    }

    private function getCartItems()
    {
        if (auth()->check()) {
            return CartItem::where('user_id', auth()->id())->with('product')->get();
        }
        return CartItem::where('session_id', session()->getId())->with('product')->get();
    }
}