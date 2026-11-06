<x-layouts.app title="Order Confirmed">
{{-- Matuma Malapile 222904267 | Group KN3 --}}
@php
    $isPickup    = $order->fulfillment === 'pickup';
    $statusClasses = [
        'pending'   => 'status-pending',
        'confirmed' => 'status-confirmed',
        'ready'     => 'status-ready',
        'collected' => 'status-collected',
    ];
@endphp

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="text-center mb-8">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-50 flex items-center justify-center">
            <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <p class="section-label">Payment Confirmed</p>
        <h1 class="section-title">Order Placed!</h1>
        <p class="text-stone-400 text-sm mt-2">We've received your order and will process it shortly.</p>
    </div>

    <div class="card p-6 mb-5">
        <div class="flex items-start justify-between mb-5 pb-4 border-b border-stone-100">
            <div>
                <p class="text-[10px] font-heading font-bold text-stone-400 uppercase tracking-widest mb-1">Reference</p>
                <p class="font-heading font-black text-lg text-[#333333]">{{ $order->payment_reference ?? '#' . $order->id }}</p>
            </div>
            <span class="badge {{ $statusClasses[$order->status] ?? 'bg-stone-100 text-stone-500' }}">{{ ucfirst($order->status) }}</span>
        </div>

        <div class="space-y-4 mb-5">
            @foreach($order->items as $item)
                <div class="flex gap-3 items-center">
                    <div class="w-14 h-14 rounded-lg overflow-hidden bg-[#F5F5F5] flex-shrink-0">
                        <img src="{{ $item->image_url }}" alt="" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-[#333333] truncate">{{ $item->name }}</p>
                        @if($item->size)<p class="text-xs text-stone-400">Size: {{ $item->size }}</p>@endif
                        <p class="text-xs text-stone-400">Quantity: {{ $item->quantity }}</p>
                    </div>
                    <p class="font-heading font-bold text-sm text-[#333333]">R{{ number_format($item->quantity * $item->unit_price, 2) }}</p>
                </div>
            @endforeach
        </div>

        @include('orders._totals', ['totalLabel' => 'Total Paid'])
    </div>

    <div class="card p-6 mb-8 bg-stone-50">
        @if($isPickup)
            <p class="text-[10px] font-heading font-bold text-stone-400 uppercase tracking-widest mb-2">Pickup Info</p>
            <p class="text-sm text-stone-600">Ready for collection at our <strong>Woodstock, Cape Town</strong> store. We will notify you when it's ready.</p>
        @else
            <p class="text-[10px] font-heading font-bold text-stone-400 uppercase tracking-widest mb-2">Delivery Info</p>
            <p class="text-sm text-stone-600">{{ $order->delivery_address }}</p>
            <p class="text-xs text-stone-400 mt-1">Estimated 3–5 business days.</p>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <a href="{{ route('orders.show', $order) }}" class="btn-primary py-3 text-sm justify-center">Track Order</a>
        <a href="{{ route('products.index') }}" class="border border-stone-300 rounded-lg py-3 text-sm font-heading font-bold text-[#333333] text-center hover:bg-stone-50 transition-colors">Continue Shopping</a>
    </div>
</div>
</x-layouts.app>