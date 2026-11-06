<?php
// Hlomla Magopeni 218070349 — Eben Supply | Group KN3

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\PromoCode;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function getCartQuery()
    {
        if (auth()->check()) {
            return CartItem::where('user_id', auth()->id());
        }
        return CartItem::where('session_id', session()->getId());
    }

    public function index()
    {
        $cartItems = CartItem::currentItems();
        $subtotal  = Order::totalsFor($cartItems, 'pickup')['subtotal'];

        [$promo, $promoError] = PromoCode::applied($subtotal, auth()->user(), session('pending_order.contact_email'));
        $totals = Order::totalsFor($cartItems, 'pickup', $promo);

        return view('cart.index', compact('cartItems', 'subtotal', 'totals', 'promo', 'promoError'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'size'       => 'nullable|string',
            'quantity'   => 'required|integer|min:1|max:' . CartItem::MAX_QUANTITY,
        ]);

        $product = Product::with('sizes')->findOrFail($request->product_id);
        $size    = $product->sizes->isEmpty() ? null : $request->size;

        if ($product->sizes->isNotEmpty() && !$product->sizes->contains('size', $size)) {
            return redirect()->back()->with('error', 'Please choose an available size.');
        }

        $attributes = [
            'product_id' => $product->id,
            'size'       => $size,
        ];

        if (auth()->check()) {
            $attributes['user_id'] = auth()->id();
        } else {
            $attributes['session_id'] = session()->getId();
            // Login gives the session a new ID; remember this one so
            // MergeGuestCart can find these items afterwards.
            session(['guest_cart_id' => session()->getId()]);
        }

        $existing  = CartItem::where($attributes)->first();
        $inCart    = $existing?->quantity ?? 0;
        $available = $product->availableStock($size);

        if ($inCart + $request->quantity > $available) {
            return redirect()->back()->with('error', $this->stockMessage($product, $size, $available, $inCart));
        }

        if ($inCart + $request->quantity > CartItem::MAX_QUANTITY) {
            return redirect()->back()->with('error', 'You can have up to ' . CartItem::MAX_QUANTITY . ' of each item in your cart' . ($inCart ? " and you already have {$inCart}." : '.'));
        }

        if ($existing) {
            $existing->increment('quantity', $request->quantity);
        } else {
            CartItem::create(array_merge($attributes, ['quantity' => $request->quantity]));
        }

        return redirect()->back()->with('success', 'Item added to cart!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'quantity'     => 'required|integer|min:1|max:' . CartItem::MAX_QUANTITY,
        ]);

        $item = CartItem::with('product.sizes')->findOrFail($request->cart_item_id);
        $this->authorizeCartItem($item);

        $available = $item->product->availableStock($item->size);
        if ($request->quantity > $available) {
            return redirect()->route('cart.index')->with('error', $this->stockMessage($item->product, $item->size, $available));
        }

        $item->update(['quantity' => $request->quantity]);

        return redirect()->route('cart.index')->with('success', 'Cart updated.');
    }

    public function remove(Request $request)
    {
        $request->validate(['cart_item_id' => 'required|exists:cart_items,id']);

        $item = CartItem::findOrFail($request->cart_item_id);
        $this->authorizeCartItem($item);
        $item->delete();

        return redirect()->route('cart.index')->with('success', 'Item removed.');
    }

    private function authorizeCartItem(CartItem $item): void
    {
        if (auth()->check()) {
            abort_unless($item->user_id === auth()->id(), 403);
        } else {
            abort_unless($item->session_id === session()->getId(), 403);
        }
    }

    private function stockMessage(Product $product, ?string $size, int $available, int $inCart = 0): string
    {
        $name = $product->name . ($size ? " ({$size})" : '');

        if ($available <= 0) {
            return "{$name} is out of stock.";
        }
        if ($inCart > 0) {
            return "Only {$available} of {$name} in stock and you already have {$inCart} in your cart.";
        }
        return "Only {$available} of {$name} in stock.";
    }

    // Lines whose product was deleted are not counted.
    public static function getCount(): int
    {
        return (int) CartItem::forCurrentShopper()->whereHas('product')->sum('quantity');
    }
}
