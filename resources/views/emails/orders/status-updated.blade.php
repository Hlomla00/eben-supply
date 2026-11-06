{{-- Hlomla Magopeni 218070349 — Eben Supply | Group KN3 --}}
<x-mail::message>
# Order {{ $order->payment_reference }}: {{ $order->status_badge['label'] }}

Hi {{ $order->contact_name }},

{{ $statusMessage }}

<x-mail::button :url="route('orders.show', $order)">
View your order
</x-mail::button>

Eben Supply<br>
Woodstock, Cape Town
</x-mail::message>
