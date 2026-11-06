<?php
// Hlomla Magopeni 218070349 — Eben Supply | Group KN3

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlaced extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing('items.product');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Eben Supply order {$this->order->payment_reference} received",
        );
    }

    public function content(): Content
    {
        $subtotal = $this->order->items->sum(fn ($i) => $i->quantity * $i->unit_price);

        return new Content(
            markdown: 'emails.orders.placed',
            with: [
                'order'       => $this->order,
                'subtotal'    => $subtotal,
                'deliveryFee' => $this->order->total_amount - $subtotal,
            ],
        );
    }
}
