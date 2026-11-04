<x-layouts.app title="Order Confirmed">
{{-- Hlomla Magopeni 218070349 - Eben Supply | Group KN3 --}}
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="mb-8 text-center">
        <p class="section-label justify-center flex">Step 3 of 3</p>
        <div class="flex items-center justify-center gap-3 mt-2 mb-6 text-xs font-heading">
            <span class="text-stone-400">1 Details</span>
            <div class="h-px w-8 bg-stone-200"></div>
            <span class="text-stone-400">2 Payment</span>
            <div class="h-px w-8 bg-stone-200"></div>
            <div class="flex items-center gap-2 text-[#333333] font-semibold">
                <span class="w-6 h-6 rounded-full bg-[#333333] text-white flex items-center justify-center font-bold">3</span>
                <span>Confirmation</span>
            </div>
        </div>

        <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 ring-8 ring-emerald-50/50 flex items-center justify-center mb-5">
            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="section-title text-center">Thank you, {{ $order->contact_name }}!</h1>
        <p class="text-sm text-stone-500 mt-2">
            Your payment was successful and your order has been placed.
            Keep your order reference below to track your order.
        </p>
    </div>

    {{-- Reference --}}
    <div class="card p-6 mb-5 text-center">
        <p class="text-stone-400 text-xs font-heading uppercase tracking-wider mb-1">Order Reference</p>
        <p class="font-heading font-black text-2xl text-[#333333] tracking-wide">{{ $order->payment_reference ?? '#' . $order->id }}</p>
        <div class="flex items-center justify-center gap-3 mt-3 text-xs text-stone-400">
            <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
            <span class="w-1 h-1 rounded-full bg-stone-300"></span>
            <span class="px-2 py-0.5 rounded-full font-semibold {{ $order->status_badge['class'] }}">{{ $order->status_badge['label'] }}</span>
        </div>
    </div>

    {{-- Fulfillment + contact --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
        <div class="card p-5">
            <p class="form-label mb-3">{{ $order->fulfillment === 'delivery' ? 'Delivery' : 'Store Pickup' }}</p>
            @if($order->fulfillment === 'delivery')
                <p class="text-sm text-stone-500 leading-relaxed">{{ $order->delivery_address }}</p>
                <p class="text-xs text-stone-400 mt-2">We'll contact you to arrange a delivery time.</p>
            @else
                <p class="font-heading font-bold text-sm text-[#333333]">Eben Supply</p>
                <p class="text-sm text-stone-500">Woodstock, Cape Town</p>
                <p class="text-xs text-stone-400 mt-2">We'll let you know when your order is ready to collect.</p>
            @endif
        </div>
        <div class="card p-5">
            <p class="form-label mb-3">Contact</p>
            <p class="font-heading font-bold text-sm text-[#333333]">{{ $order->contact_name }}</p>
            <p class="text-sm text-stone-500">{{ $order->contact_phone }}</p>
            <p class="text-sm text-stone-500">{{ $order->contact_email }}</p>
        </div>
    </div>

    {{-- Items --}}
    <div class="card p-6 mb-8">
        <p class="form-label mb-5">Order Summary</p>
        <div class="space-y-4">
            @foreach($order->items as $item)
                <div class="flex items-center gap-4 pb-4 border-b border-stone-100 last:border-0 last:pb-0">
                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-[#F5F5F5] flex-shrink-0">
                        <img src="{{ asset($item->product->image_path ?: 'images/products/placeholder.jpg') }}" alt="" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1">
                        <p class="font-heading font-semibold text-sm text-[#333333]">{{ $item->product->name }}</p>
                        @if($item->size)<p class="text-xs text-stone-400">Size: {{ $item->size }}</p>@endif
                        <p class="text-xs text-stone-400">R{{ number_format($item->unit_price, 2) }} × {{ $item->quantity }}</p>
                    </div>
                    <p class="font-heading font-black text-sm text-[#333333]">R{{ number_format($item->line_total, 2) }}</p>
                </div>
            @endforeach
        </div>

        <div class="border-t border-stone-100 mt-4 pt-4 space-y-2 text-sm">
            <div class="flex justify-between text-stone-500"><span>Subtotal</span><span>R{{ number_format($order->items->sum('line_total'), 2) }}</span></div>
            <div class="flex justify-between text-stone-500"><span>Delivery</span><span>{{ $order->fulfillment === 'delivery' ? 'R' . number_format($order->delivery_fee, 2) : 'Free (Pickup)' }}</span></div>
            <div class="flex justify-between font-heading font-black text-[#333333] border-t border-stone-100 pt-2">
                <span>Total Paid</span><span>R{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ route('orders.show', $order) }}" class="btn-primary justify-center">Track Order</a>
        <a href="{{ route('products.index') }}" class="btn-secondary justify-center">Continue Shopping</a>
    </div>
</div>
</x-layouts.app>
