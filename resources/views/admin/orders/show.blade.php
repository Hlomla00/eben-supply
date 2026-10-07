<x-layouts.admin title="Order Detail">
{{-- Dumisane Madondo 230949703 | Group KN3 --}}
<div class="max-w-3xl">
    <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-1 text-sm text-stone-400 hover:text-[#333333] mb-6 inline-flex transition-colors font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7-7-7 7 7 7"/></svg>
        Back to Orders
    </a>

    @php
        $statusClasses = [
            'pending'   => 'status-pending',
            'confirmed' => 'status-confirmed',
            'ready'     => 'status-ready',
            'collected' => 'status-collected',
        ];
    @endphp

    {{-- Header card: reference + status update --}}
    <div class="bg-white rounded-xl border border-stone-100 shadow-soft p-6 mb-5">
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div>
                <p class="text-[10px] font-heading font-bold text-stone-400 uppercase tracking-widest mb-1">Order Reference</p>
                <p class="font-heading font-black text-2xl text-[#333333]">{{ $order->payment_reference ?? '#' . $order->id }}</p>
                <p class="text-xs text-stone-400 mt-1">{{ $order->created_at->format('d M Y, H:i') }}</p>
                <span class="badge {{ $statusClasses[$order->status] ?? 'bg-stone-100 text-stone-500' }} mt-2 inline-block">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
            <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" class="flex items-center gap-2">
                @csrf @method('PUT')
                <select name="status" class="form-input py-2 text-sm pr-8">
                    @foreach(['pending', 'confirmed', 'ready', 'collected'] as $s)
                        <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-xs px-5 py-2">Update</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
        {{-- Customer --}}
        <div class="bg-white rounded-xl border border-stone-100 shadow-soft p-5">
            <h3 class="font-heading font-bold text-xs text-[#A3A380] uppercase tracking-wider mb-3">Customer</h3>
            <p class="font-heading font-semibold text-[#333333] text-sm">{{ $order->contact_name }}</p>
            <p class="text-sm text-stone-500 mt-1">{{ $order->contact_email }}</p>
            <p class="text-sm text-stone-500">{{ $order->contact_phone }}</p>
        </div>

        {{-- Fulfillment --}}
        <div class="bg-white rounded-xl border border-stone-100 shadow-soft p-5">
            <h3 class="font-heading font-bold text-xs text-[#A3A380] uppercase tracking-wider mb-3">Fulfillment</h3>
            <p class="font-heading font-semibold text-[#333333] text-sm capitalize mb-1">{{ $order->fulfillment }}</p>
            @if($order->delivery_address)
                <p class="text-sm text-stone-500 leading-relaxed">{{ $order->delivery_address }}</p>
            @else
                <p class="text-sm text-stone-400">Store pickup — Woodstock, Cape Town</p>
            @endif
        </div>
    </div>

    {{-- Order items --}}
    <div class="bg-white rounded-xl border border-stone-100 shadow-soft overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
            <h3 class="font-heading font-bold text-sm text-[#333333]">Order Items</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[10px] font-heading font-bold text-stone-400 uppercase tracking-widest border-b border-stone-100 bg-stone-50">
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Size</th>
                        <th class="px-6 py-3">Qty</th>
                        <th class="px-6 py-3">Unit Price</th>
                        <th class="px-6 py-3 text-right">Line Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50">
                    @foreach($order->items as $item)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden bg-[#F5F5F5] flex-shrink-0">
                                        <img src="{{ asset($item->product->image_path ?: 'images/products/placeholder.jpg') }}"
                                             alt="" class="w-full h-full object-cover">
                                    </div>
                                    <span class="font-medium text-[#333333]">{{ $item->product->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-stone-500 font-heading font-bold text-xs">{{ $item->size ?: '—' }}</td>
                            <td class="px-6 py-4 text-stone-500">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-stone-500">R{{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-6 py-4 text-right font-heading font-bold text-[#333333]">R{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-stone-100">
                        <td colspan="4" class="px-6 py-3 text-right text-xs text-stone-400 font-heading font-bold uppercase tracking-wider">Subtotal</td>
                        <td class="px-6 py-3 text-right font-heading font-bold text-[#333333]">R{{ number_format($order->items->sum('line_total'), 2) }}</td>
                    </tr>
                    @if($order->fulfillment === 'delivery')
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-right text-xs text-stone-400 font-heading font-bold uppercase tracking-wider">Delivery</td>
                            <td class="px-6 py-3 text-right font-heading font-bold text-[#333333]">R60.00</td>
                        </tr>
                    @endif
                    <tr class="border-t border-stone-200 bg-stone-50">
                        <td colspan="4" class="px-6 py-4 text-right font-heading font-black text-xs uppercase tracking-widest text-[#333333]">Total</td>
                        <td class="px-6 py-4 text-right font-heading font-black text-lg text-[#333333]">R{{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
</x-layouts.admin>