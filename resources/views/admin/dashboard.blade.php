<x-layouts.admin title="Dashboard">
{{-- Matuma Malapile 222904267 | Group KN3 --}}

{{-- Stat cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
    @foreach([
        ['Total Orders',    $totalOrders,   'text-blue-500',   'bg-blue-50',   'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['Pending Orders',  $pendingOrders, 'text-amber-500',  'bg-amber-50',  'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Total Products',  $totalProducts, 'text-violet-500', 'bg-violet-50', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10'],
        ['Low Stock',       $lowStockCount, 'text-rose-500',   'bg-rose-50',   'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
    ] as [$label, $value, $iconColor, $iconBg, $path])
    <div class="bg-white rounded-xl border border-stone-100 p-5 shadow-soft">
        <div class="flex items-center justify-between mb-4">
            <p class="text-xs font-heading font-bold text-stone-400 uppercase tracking-wider">{{ $label }}</p>
            <div class="w-9 h-9 {{ $iconBg }} rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
                </svg>
            </div>
        </div>
        <p class="font-heading font-black text-4xl text-[#333333]">{{ $value }}</p>
        @if($label === 'Low Stock' && $value > 0)
            <a href="{{ route('admin.inventory') }}" class="text-xs text-[#A3A380] hover:text-[#333333] mt-1 inline-block font-medium transition-colors">View inventory →</a>
        @endif
    </div>
    @endforeach
</div>

{{-- Quick actions --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <a href="{{ route('admin.products.create') }}"
       class="bg-[#333333] text-white rounded-xl p-5 flex items-center gap-4 hover:bg-[#444444] transition-colors group shadow-soft">
        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        </div>
        <div>
            <p class="font-heading font-bold text-sm">Add Product</p>
            <p class="text-white/60 text-xs">Create new listing</p>
        </div>
    </a>
    <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}"
       class="bg-white rounded-xl border border-stone-100 p-5 flex items-center gap-4 hover:border-[#D4C7B0] hover:shadow-card transition-all group shadow-soft">
        <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p class="font-heading font-bold text-sm text-[#333333]">Pending Orders</p>
            <p class="text-stone-400 text-xs">{{ $pendingOrders }} awaiting action</p>
        </div>
    </a>
    <a href="{{ route('admin.inventory') }}"
       class="bg-white rounded-xl border border-stone-100 p-5 flex items-center gap-4 hover:border-[#D4C7B0] hover:shadow-card transition-all group shadow-soft">
        <div class="w-10 h-10 bg-[#D4C7B0]/30 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-[#A3A380]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
            <p class="font-heading font-bold text-sm text-[#333333]">Stock Levels</p>
            <p class="text-stone-400 text-xs">Update inventory</p>
        </div>
    </a>
</div>

{{-- Recent orders table --}}
<div class="bg-white rounded-xl border border-stone-100 shadow-soft overflow-hidden">
    <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
        <h2 class="font-heading font-bold text-sm text-[#333333]">Recent Orders</h2>
        <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-[#A3A380] hover:text-[#333333] transition-colors font-heading">View All →</a>
    </div>
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
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-50">
                @forelse($recentOrders as $order)
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
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-heading font-bold text-[#333333] hover:text-[#A3A380] transition-colors">
                                {{ $order->payment_reference ?? '#' . $order->id }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-stone-600">{{ $order->contact_name }}</td>
                        <td class="px-6 py-4 font-heading font-bold">R{{ number_format($order->total_amount, 2) }}</td>
                        <td class="px-6 py-4 text-stone-500 capitalize">{{ $order->fulfillment }}</td>
                        <td class="px-6 py-4">
                            <span class="badge {{ $statusClasses[$order->status] ?? 'bg-stone-100 text-stone-500' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-stone-400 text-xs">{{ $order->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-stone-400 text-sm">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-layouts.admin>