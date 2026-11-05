<?php
// Hlomla Magopeni 218070349 — Eben Supply | Group KN3

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
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
        $cartItems = $this->getCartQuery()->with('product.sizes')->get();
        $subtotal  = $cartItems->sum(fn($item) => $item->quantity * $item->product->price);

        return view('cart.index', compact('cartItems', 'subtotal'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'size'       => 'nullable|string',
            'quantity'   => 'required|integer|min:1|max:20',
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
            'quantity'     => 'required|integer|min:1|max:20',
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

    public static function getCount(): int
    {
        if (auth()->check()) {
            return CartItem::where('user_id', auth()->id())->sum('quantity');
        }
        return CartItem::where('session_id', session()->getId())->sum('quantity');
    }
}
