{{-- Hlomla Magopeni 218070349 — Eben Supply | Group KN3 --}}
<x-mail::message>
# Thank you, {{ $order->contact_name }}!

We have received your order. Keep this reference to track it:

**{{ $order->payment_reference }}** · placed {{ $order->created_at->format('d M Y, H:i') }}

<x-mail::table>
| Item | Qty | Price |
|:-----|:---:|------:|
@foreach($order->items as $item)
| {{ $item->product?->name ?? 'Product' }}{{ $item->size ? ' ('.$item->size.')' : '' }} | {{ $item->quantity }} | R{{ number_format($item->quantity * $item->unit_price, 2) }} |
@endforeach
| Subtotal | | R{{ number_format($subtotal, 2) }} |
| Delivery | | {{ $order->fulfillment === 'pickup' ? 'Free (pickup)' : 'R'.number_format($deliveryFee, 2) }} |
| **Total paid** | | **R{{ number_format($order->total_amount, 2) }}** |
</x-mail::table>

@if($order->fulfillment === 'pickup')
**Store pickup:** we will email you when your order is ready to collect at our Woodstock, Cape Town store.
@else
**Delivery to:** {{ $order->delivery_address }}
@endif

<x-mail::button :url="route('orders.show', $order)">
Track your order
</x-mail::button>

Eben Supply<br>
Woodstock, Cape Town
</x-mail::message>
