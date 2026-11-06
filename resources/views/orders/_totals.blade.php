{{-- Money breakdown stored on the order. Shared by the confirmation and order history pages. --}}
<div class="border-t border-stone-100 {{ $class ?? '' }} pt-4 space-y-2 text-sm">
    <div class="flex justify-between text-stone-500"><span>Subtotal</span><span>R{{ number_format($order->items_subtotal, 2) }}</span></div>
    @if($order->discount_amount > 0)
        <div class="flex justify-between text-emerald-700"><span>Discount{{ $order->promo_code ? ' (' . $order->promo_code . ')' : '' }}</span><span>−R{{ number_format($order->discount_amount, 2) }}</span></div>
    @endif
    <div class="flex justify-between text-stone-500"><span>Delivery</span><span>{{ $order->fulfillment === 'delivery' ? 'R' . number_format($order->delivery_fee, 2) : 'Free (Pickup)' }}</span></div>
    <div class="flex justify-between font-heading font-black text-base border-t border-stone-100 pt-2">
        <span>{{ $totalLabel ?? 'Total' }}</span><span>R{{ number_format($order->total_amount, 2) }}</span>
    </div>
</div>
