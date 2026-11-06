@extends('layouts.app')
{{-- Dumisane Madondo 230949703 | Group KN3 --}}
@section('title', 'My Orders')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <p class="section-label">Your Account</p>
    <h1 class="section-title mb-8">My Orders</h1>

    @if($orders->count())
        <div class="space-y-4">
            @foreach($orders as $order)
                @php
                    $statusClasses = [
                        'pending'   => 'status-pending',
                        'confirmed' => 'status-confirmed',
                        'ready'     => 'status-ready',
                        'collected' => 'status-collected',
                        'paid'      => 'status-paid',      // paid/cancelled — Hlomla Magopeni 218070349
                        'cancelled' => 'status-cancelled',
                    ];
                @endphp
                <a href="{{ route('orders.show', $order) }}"
                   class="card card-hover block p-5 group">
                    <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
                        <div>
                            <p class="text-[10px] text-stone-400 font-heading uppercase tracking-widest mb-0.5">Reference</p>
                            <p class="font-heading font-black text-[#333333]">{{ $order->payment_reference ?? '#' . $order->id }}</p>
                        </div>
                        <span class="badge {{ $statusClasses[$order->status] ?? 'bg-stone-100 text-stone-600' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs border-t border-stone-100 pt-4">
                        <div>
                            <p class="text-stone-400 mb-0.5 font-heading uppercase tracking-wider text-[10px]">Date</p>
                            <p class="font-medium text-[#333333]">{{ $order->created_at->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-stone-400 mb-0.5 font-heading uppercase tracking-wider text-[10px]">Items</p>
                            <p class="font-medium text-[#333333]">{{ $order->items->sum('quantity') }}</p>
                        </div>
                        <div>
                            <p class="text-stone-400 mb-0.5 font-heading uppercase tracking-wider text-[10px]">Fulfillment</p>
                            <p class="font-medium text-[#333333] capitalize">{{ $order->fulfillment }}</p>
                        </div>
                        <div>
                            <p class="text-stone-400 mb-0.5 font-heading uppercase tracking-wider text-[10px]">Total</p>
                            <p class="font-heading font-black text-[#333333]">R{{ number_format($order->total_amount, 2) }}</p>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-end gap-1 text-xs text-[#A3A380] group-hover:text-[#333333] font-semibold transition-colors">
                        View Details
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/></svg>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $orders->links() }}</div>

    @else
        <div class="text-center py-24 bg-white rounded-2xl border border-stone-100 max-w-sm mx-auto">
            <div class="w-16 h-16 bg-[#F5F5F5] rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="font-heading font-bold text-xl text-[#333333] mb-2">No orders yet</h3>
            <p class="text-stone-400 text-sm mb-8">Your orders will appear here once you shop.</p>
            <a href="{{ route('products.index') }}" class="btn-primary text-sm">Start Shopping</a>
        </div>
    @endif
</div>
@endsection