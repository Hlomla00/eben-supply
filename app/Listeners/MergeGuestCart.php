<?php
// Hlomla Magopeni 218070349 — Eben Supply | Group KN3

namespace App\Listeners;

use App\Models\CartItem;
use Illuminate\Auth\Events\Login;

// Moves a guest's cart onto their account when they log in or register.
// The session ID has already changed by the time Login fires, so the
// guest's cart ID comes from the session data CartController stored.
class MergeGuestCart
{
    public function handle(Login $event): void
    {
        if (!app()->bound('session') || !session()->has('guest_cart_id')) {
            return;
        }

        $sessionId  = session()->pull('guest_cart_id');
        $guestItems = CartItem::where('session_id', $sessionId)->whereNull('user_id')->get();

        foreach ($guestItems as $guestItem) {
            $existing = CartItem::where('user_id', $event->user->id)
                ->where('product_id', $guestItem->product_id)
                ->where('size', $guestItem->size)
                ->first();

            if ($existing) {
                $existing->update(['quantity' => min(20, $existing->quantity + $guestItem->quantity)]);
                $guestItem->delete();
            } else {
                $guestItem->update(['user_id' => $event->user->id, 'session_id' => null]);
            }
        }
    }
}
