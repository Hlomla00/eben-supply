<?php
// Hlomla Magopeni 218070349 — Eben Supply | Group KN3

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Eben Supply order {$this->order->payment_reference} is now {$this->order->status_badge['label']}",
        );
    }

    public function content(): Content
    {
        $pickup = $this->order->fulfillment === 'pickup';

        $message = match ($this->order->status) {
            'paid'      => 'We have received your payment.',
            'confirmed' => 'We have confirmed your order and are getting it ready.',
            'cancelled' => 'Your order has been cancelled. Please contact the store about your payment or if you did not expect this.',
            'ready'     => $pickup
                ? 'Your order is ready for collection at our Woodstock, Cape Town store.'
                : 'Your order is packed and on its way to you.',
            'collected' => $pickup
                ? 'Your order has been collected. Thank you for shopping with Eben Supply.'
                : 'Your order has been delivered. Thank you for shopping with Eben Supply.',
            default     => 'The status of your order has changed.',
        };

        return new Content(
            markdown: 'emails.orders.status-updated',
            with: ['order' => $this->order, 'statusMessage' => $message],
        );
    }
}
