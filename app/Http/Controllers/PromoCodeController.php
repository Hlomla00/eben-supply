<?php
// PRT362S — Eben Supply | Group KN3

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Applying or removing the one promo code allowed per order. The code is only
 * remembered here; the discount is recalculated on every page and rechecked
 * when the order is placed.
 */
class PromoCodeController extends Controller
{
    /** Wrong codes allowed per shopper before the promo box locks. */
    public const MAX_FAILED_ATTEMPTS = 5;

    public const LOCKOUT_SECONDS = 600;

    public function apply(Request $request)
    {
        $request->validate(['code' => 'required|string|max:40'], ['code.required' => 'Enter a promo code.']);

        // Stop codes being guessed. Only unknown codes count, so a real code that
        // doesn't apply yet (e.g. below minimum spend) never locks anyone out.
        // Signed-in shoppers are limited per account, guests per IP address.
        $key = 'promo-attempts:' . ($request->user() ? 'user:' . $request->user()->id : 'ip:' . $request->ip());
        if (RateLimiter::tooManyAttempts($key, self::MAX_FAILED_ATTEMPTS)) {
            $minutes = max(1, (int) ceil(RateLimiter::availableIn($key) / 60));
            return $this->fail("Too many incorrect promo codes. Please try again in {$minutes} " . str('minute')->plural($minutes) . '.');
        }

        $items = CartItem::currentItems();
        $promo = PromoCode::findByCode($request->code);

        if ($items->isEmpty()) {
            return $this->fail('Add something to your cart before applying a promo code.');
        }
        if (!$promo) {
            RateLimiter::hit($key, self::LOCKOUT_SECONDS);
            return $this->fail("That promo code isn't valid.");
        }

        $subtotal = Order::totalsFor($items, 'pickup')['subtotal'];
        if ($reason = $promo->rejectionReason($subtotal, $request->user(), null, session('pending_order.contact_email'))) {
            return $this->fail($reason);
        }

        // Replaces any code already applied: one code per order.
        session([PromoCode::SESSION_KEY => $promo->code]);

        return redirect()->back()->with('success', "Promo code {$promo->code} applied: {$promo->label}.");
    }

    public function remove()
    {
        session()->forget(PromoCode::SESSION_KEY);

        return redirect()->back()->with('success', 'Promo code removed.');
    }

    private function fail(string $message)
    {
        return redirect()->back()->withInput()->with('promo_error', $message);
    }
}
