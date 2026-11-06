@extends('layouts.admin')
{{-- PRT362S — Eben Supply | Group KN3 --}}
@section('title', 'Promo Codes')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-stone-400 font-medium">{{ $promos->total() }} promo codes</p>
    <a href="{{ route('admin.promos.create') }}" class="btn-primary text-xs px-4 py-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Add Promo Code
    </a>
</div>

<div class="bg-white rounded-xl border border-stone-100 shadow-soft overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[10px] font-heading font-bold text-stone-400 uppercase tracking-widest border-b border-stone-100 bg-stone-50">
                    <th class="px-6 py-3">Code</th>
                    <th class="px-6 py-3">Discount</th>
                    <th class="px-6 py-3">Min. spend</th>
                    <th class="px-6 py-3">Dates</th>
                    <th class="px-6 py-3">Used</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-50">
                @forelse($promos as $promo)
                    @php
                        $statusClass = [
                            'active'    => 'bg-emerald-50 text-emerald-700',
                            'scheduled' => 'bg-amber-100 text-amber-800',
                            'expired'   => 'bg-stone-100 text-stone-600',
                            'used up'   => 'bg-stone-100 text-stone-600',
                            'inactive'  => 'bg-red-50 text-red-600',
                        ][$promo->status];
                    @endphp
                    <tr class="hover:bg-stone-50 transition-colors">
                        <td class="px-6 py-4 font-heading font-bold text-[#333333]">{{ $promo->code }}</td>
                        <td class="px-6 py-4 text-stone-600">{{ $promo->label }}</td>
                        <td class="px-6 py-4 text-stone-500">{{ $promo->min_spend !== null ? 'R' . number_format($promo->min_spend, 2) : '—' }}</td>
                        <td class="px-6 py-4 text-stone-500 text-xs">
                            {{ $promo->starts_at?->format('d M Y H:i') ?? 'Now' }} → {{ $promo->ends_at?->format('d M Y H:i') ?? 'No end' }}
                        </td>
                        <td class="px-6 py-4 text-stone-500 text-xs">
                            {{ $promo->times_used }}{{ $promo->usage_limit !== null ? ' / ' . $promo->usage_limit : '' }}
                        </td>
                        <td class="px-6 py-4"><span class="badge {{ $statusClass }}">{{ ucfirst($promo->status) }}</span></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.promos.edit', $promo) }}" class="text-xs font-semibold text-stone-400 hover:text-[#333333] transition-colors py-2 px-1">Edit</a>
                                <form action="{{ route('admin.promos.toggle', $promo) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="py-2 px-1 text-xs font-semibold {{ $promo->is_active ? 'text-red-400 hover:text-red-600' : 'text-emerald-600 hover:text-emerald-800' }} transition-colors">
                                        {{ $promo->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-stone-400">
                        No promo codes yet. <a href="{{ route('admin.promos.create') }}" class="text-[#A3A380] hover:text-[#333333] font-medium transition-colors">Add one</a>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-6">{{ $promos->links() }}</div>
@endsection
