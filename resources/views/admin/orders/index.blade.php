<x-layouts.admin title="Orders">
{{-- Dumisane Madondo 230949703 | Group KN3 --}}

{{-- Status filter tabs --}}
<div class="flex flex-wrap gap-2 mb-6">
    <a href="{{ route('admin.orders.index') }}"
       class="px-4 py-1.5 text-xs font-heading font-bold rounded-full transition-colors
           {{ !request('status') ? 'bg-[#333333] text-white' : 'bg-white text-stone-400 border border-stone-200 hover:border-[#D4C7B0] hover:text-[#333333]' }}">
        All Orders
    </a>
    @foreach($statuses as $s)
        <a href="{{ route('admin.orders.index', ['status' => $s]) }}"
           class="px-4 py-1.5 text-xs font-heading font-bold rounded-full transition-colors capitalize
               {{ request('status') === $s ? 'bg-[#333333] text-white' : 'bg-white text-stone-400 border border-stone-200 hover:border-[#D4C7B0] hover:text-[#333333]' }}">
            {{ ucfirst($s) }}
        </a>
    @endforeach
</div>

<div class="bg-white rounded-xl border border-stone-100 shadow-soft overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[10px] font-heading font-bold text-stone-400 uppercase tracking-widest border-b border-stone-100 bg-stone-50">
                    <th class="px-6 py-3">Reference</th>
                    <th class="px-6 py-3">Customer</th>
                    <th class="px-6 py-3">Total</th>
                    <th class="px-6 py-3">Fulfillment</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-50">
                @forelse($orders as $order)
                    @php
                        $statusClasses = [
                            'pending'   => 'status-pending',
                            'confirmed' => 'status-confirmed',
                            'ready'     => 'status-ready',
                            'collected' => 'status-collected',
                        ];
                    @endphp
                    <tr class="hover:bg-stone-50 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.orders.show', $order) }}"
                               class="font-heading font-bold text-[#333333] hover:text-[#A3A380] transition-colors">
                                {{ $order->payment_reference ?? '#' . $order->id }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-[#333333]">{{ $order->contact_name }}</p>
                            <p class="text-xs text-stone-400">{{ $order->contact_email }}</p>
                        </td>
                        <td class="px-6 py-4 font-heading font-bold text-[#333333]">R{{ number_format($order->total_amount, 2) }}</td>
                        <td class="px-6 py-4 text-stone-500 capitalize">{{ $order->fulfillment }}</td>
                        <td class="px-6 py-4">
                            <span class="badge {{ $statusClasses[$order->status] ?? 'bg-stone-100 text-stone-500' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-stone-400 text-xs">{{ $order->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}"
                               class="text-xs font-semibold text-stone-400 hover:text-[#333333] transition-colors">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-stone-400 text-sm">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $orders->links() }}</div>
</x-layouts.admin>