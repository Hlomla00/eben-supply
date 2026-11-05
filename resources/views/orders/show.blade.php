@extends('layouts.app')
{{-- Dumisane Madondo 230949703 | Group KN3 --}}
@section('title', 'Order Detail')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="section-label">Order Tracking</p>
            <h1 class="section-title">Order Detail</h1>
        </div>
        <a href="{{ route('orders.index') }}" class="flex items-center gap-1 text-sm text-stone-400 hover:text-[#333333] transition-colors font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7-7-7 7 7 7"/></svg>
            My Orders
        </a>
    </div>

    @php
        $steps = ['pending', 'confirmed', 'ready', 'collected'];
        $stepLabels = ['Pending', 'Confirmed', 'Ready', 'Collected'];
        $currentStep = array_search($order->status, $steps);
    @endphp

    {{-- Status timeline --}}
    <div class="card p-6 mb-6">
        <p class="form-label mb-5">Order Status</p>
        <div class="flex items-start">
            @foreach($steps as $i => $step)
                <div class="flex-1 flex flex-col items-center relative">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold font-heading z-10
                        {{ $i < $currentStep ? 'bg-[#333333] text-white' : ($i === $currentStep ? 'bg-[#D4C7B0] text-[#333333]' : 'bg-stone-100 text-stone-400 border border-stone-200') }}">
                        @if($i < $currentStep)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </div>
                    <span class="text-[10px] mt-2 font-heading font-semibold text-center
                        {{ $i <= $currentStep ? 'text-[#333333]' : 'text-stone-400' }}">
                        {{ $stepLabels[$i] }}
                    </span>
                    @if($i < count($steps) - 1)
                        <div class="absolute top-4 left-1/2 w-full h-0.5 {{ $i < $currentStep ? 'bg-[#333333]' : 'bg-stone-100' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
        <div class="card p-5">
            <p class="form-label mb-3">Order Info</p>
            <div class="space-y-1 text-sm text-stone-500">
                <p>Ref: <span class="font-heading font-bold text-[#333333]">{{ $order->payment_reference ?? '#' . $order->id }}</span></p>
                <p>Date: <span class="text-[#333333]">{{ $order->created_at->format('d M Y, H:i') }}</span></p>
                <p>Fulfillment: <span class="text-[#333333] capitalize">{{ $order->fulfillment }}</span></p>
            </div>
        </div>
        <div class="card p-5">
            <p class="form-label mb-3">Contact</p>
            <p class="font-heading font-bold text-sm text-[#333333]">{{ $order->contact_name }}</p>
            <p class="text-sm text-stone-500">{{ $order->contact_phone }}</p>
            <p class="text-sm text-stone-500">{{ $order->contact_email }}</p>
            @if($order->delivery_address)
                <p class="text-sm text-stone-500 mt-2 leading-relaxed">{{ $order->delivery_address }}</p>
            @endif
        </div>
    </div>

    {{-- Items --}}
    <div class="card p-6">
        <p class="form-label mb-5">Items Ordered</p>
        <div class="space-y-4">
            @foreach($order->items as $item)
                <div class="flex items-center gap-4 pb-4 border-b border-stone-100 last:border-0 last:pb-0">
                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-[#F5F5F5] flex-shrink-0">
                        <img loading="lazy" decoding="async" src="{{ $item->product->image_url }}" alt="" class="w-full h-full object-cover">
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

        @php $subtotal = $order->items->sum('line_total'); @endphp
        <div class="border-t border-stone-100 mt-4 pt-4 space-y-2 text-sm">
            <div class="flex justify-between text-stone-500"><span>Subtotal</span><span>R{{ number_format($subtotal, 2) }}</span></div>
            <div class="flex justify-between text-stone-500"><span>Delivery</span><span>{{ $order->fulfillment === 'delivery' ? 'R60.00' : 'Free (Pickup)' }}</span></div>
            <div class="flex justify-between font-heading font-black border-t border-stone-100 pt-2">
                <span>Total</span><span>R{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>
    
    </div>
</div>
@endsection