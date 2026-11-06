<?php
// Hlomla Magopeni 218070349 — Eben Supply | Group KN3

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Exceptions\InsufficientStockException;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = CartItem::currentItems();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        [$promo, $promoError] = $this->promo($cartItems);
        $totals   = Order::totalsFor($cartItems, 'pickup', $promo);
        $subtotal = $totals['subtotal'];
        $user     = auth()->user();

        return view('checkout.index', compact('cartItems', 'subtotal', 'totals', 'promo', 'promoError', 'user'));
    }

    public function process(Request $request)
    {
        $details = $request->validate([
            'contact_name'    => 'required|string|max:100',
            'contact_phone'   => 'required|string|max:20',
            'contact_email'   => 'required|email|max:100',
            'fulfillment'     => 'required|in:pickup,delivery',
            'delivery_address'=> 'required_if:fulfillment,delivery|nullable|string|max:255',
        ]);

        if (CartItem::currentItems()->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        if ($details['fulfillment'] === 'pickup') {
            $details['delivery_address'] = null;
        }

        // Only the customer's details are kept. Money is always recalculated
        // from the cart as it is, so items added later are charged (audit A1).
        session(['pending_order' => $details]);

        return redirect()->route('checkout.payment');
    }

    public function payment()
    {
        $pending = session('pending_order');
        if (!$pending) {
            return redirect()->route('checkout.index');
        }

        $cartItems = CartItem::currentItems();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        [$promo, $promoError] = $this->promo($cartItems, $pending['contact_email']);
        $totals = Order::totalsFor($cartItems, $pending['fulfillment'], $promo);

        return view('checkout.payment', compact('pending', 'cartItems', 'totals', 'promo', 'promoError'));
    }

    public function confirmPayment(Request $request)
    {
        $pending = session('pending_order');
        if (!$pending) {
            return redirect()->route('checkout.index');
        }

        $cartItems = CartItem::currentItems();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index');
        }

        try {
            $order = DB::transaction(function () use ($pending, $cartItems, $request) {
                $this->deductStock($cartItems);

                $promo  = $this->lockedPromo($cartItems, $pending['contact_email']);
                $totals = Order::totalsFor($cartItems, $pending['fulfillment'], $promo);

                // The shopper saw a total on the payment page; if prices, the
                // cart or the code changed since, show the new total first.
                $expected = $request->input('expected_total');
                if ($expected !== null && (int) round($expected * 100) !== (int) round($totals['total'] * 100)) {
                    throw new CheckoutException(
                        'Your order total changed to R' . number_format($totals['total'], 2) . '. Please check it before paying.',
                        'checkout.payment'
                    );
                }

                return $this->createOrder($pending, $cartItems, $totals, $promo);
            });
        } catch (CheckoutException $e) {
            return redirect()->route($e->redirectRoute)->with('error', $e->getMessage());
        }

        session()->forget(['pending_order', PromoCode::SESSION_KEY]);
        session(['last_order_id' => $order->id]);

        return redirect()->route('order.confirmation');
    }

    // Locks each product so concurrent orders can't oversell, then takes the
    // ordered quantity off the size row (if any) and the product total. The
    // locked, fresh product goes back on the cart line so prices are current.
    private function deductStock(Collection $cartItems): void
    {
        foreach ($cartItems as $item) {
            $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
            if (!$product) {
                throw new CheckoutException('An item in your cart is no longer available. Please review your cart.');
            }

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
            $item->setRelation('product', $product);
        }
    }

    private function createOrder(array $pending, Collection $cartItems, array $totals, ?PromoCode $promo): Order
    {
        $order = Order::create([
            'user_id'          => auth()->id(),
            'status'           => 'pending',
            'fulfillment'      => $pending['fulfillment'],
            'subtotal'         => $totals['subtotal'],
            'discount_amount'  => $totals['discount'],
            'delivery_fee'     => $totals['delivery_fee'],
            'promo_code_id'    => $promo?->id,
            'promo_code'       => $promo?->code,
            'total_amount'     => $totals['total'],
            'contact_name'     => $pending['contact_name'],
            'contact_phone'    => $pending['contact_phone'],
            'contact_email'    => $pending['contact_email'],
            'delivery_address' => $pending['delivery_address'] ?? null,
            'payment_reference'=> 'ES-' . strtoupper(uniqid()),
        ]);

        $lineDiscounts = $this->allocateDiscount($cartItems, (int) round($totals['discount'] * 100));

        foreach ($cartItems->values() as $i => $item) {
            OrderItem::create([
                'order_id'        => $order->id,
                'product_id'      => $item->product_id,
                'product_name'    => $item->product->name,
                'size'            => $item->size,
                'quantity'        => $item->quantity,
                'unit_price'      => $item->unit_price,
                'discount_amount' => $lineDiscounts[$i] / 100,
            ]);
        }

        // Counted in the same transaction as the order, under the row lock.
        $promo?->increment('times_used');

        CartItem::whereIn('id', $cartItems->pluck('id'))->delete();

        return $order;
    }

    /** The applied promo code, rechecked against the cart as it is now. */
    private function promo(Collection $cartItems, ?string $email = null): array
    {
        $subtotal = Order::totalsFor($cartItems, 'pickup')['subtotal'];

        return PromoCode::applied($subtotal, auth()->user(), $email);
    }

    /**
     * The applied code, locked for this transaction and rechecked, so two
     * orders using it at once can't go over its usage limits.
     */
    private function lockedPromo(Collection $cartItems, string $email): ?PromoCode
    {
        $code = session(PromoCode::SESSION_KEY);
        if (!$code) {
            return null;
        }

        $subtotal = Order::totalsFor($cartItems, 'pickup')['subtotal'];
        $promo    = PromoCode::where('code', PromoCode::normalise($code))->lockForUpdate()->first();
        $reason   = $promo ? $promo->rejectionReason($subtotal, auth()->user(), null, $email) : "The code {$code} isn't valid.";

        if ($reason) {
            throw new CheckoutException($reason . ' Remove the promo code or try another one, then check your total.', 'checkout.payment');
        }

        return $promo;
    }

    /**
     * Split the order discount across lines in proportion to their value, in
     * cents, with any rounding remainder on the last line so the lines add up.
     */
    private function allocateDiscount(Collection $cartItems, int $discountCents): array
    {
        $lines    = $cartItems->values()->map(fn ($item) => (int) round($item->unit_price * 100) * $item->quantity);
        $subtotal = $lines->sum();
        $last     = $lines->count() - 1;
        $left     = $discountCents;
        $shares   = [];

        foreach ($lines as $i => $line) {
            $share = $i === $last || $subtotal === 0 ? $left : intdiv($discountCents * $line, $subtotal);
            $shares[$i] = $share;
            $left -= $share;
        }

        return $shares;
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
}
