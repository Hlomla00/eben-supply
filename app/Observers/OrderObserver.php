<?php
// Hlomla Magopeni 218070349 — Eben Supply | Group KN3

namespace App\Observers;

use App\Mail\OrderPlaced;
use App\Mail\OrderStatusUpdated;
use App\Models\Order;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Throwable;

// Emails the customer when an order is placed and when an admin changes its
// status. Runs after the database transaction commits, so the order lines
// written in the same checkout transaction are already saved, and a
// rolled-back checkout sends nothing.
class OrderObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Order $order): void
    {
        $this->send($order, new OrderPlaced($order->fresh(['items.product'])));
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            $this->send($order, new OrderStatusUpdated($order));
        }
    }

    // A mail failure must never undo a paid order or a status change,
    // so it is logged and the request carries on.
    private function send(Order $order, Mailable $mail): void
    {
        if (blank($order->contact_email)) {
            return;
        }

        try {
            Mail::to($order->contact_email, $order->contact_name)->send($mail);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
