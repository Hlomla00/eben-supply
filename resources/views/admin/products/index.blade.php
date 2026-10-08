@extends('layouts.admin')
{{-- Phelo Mguca — 2330707726 | Group KN3 --}}
@section('title', 'Products')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-stone-400 font-medium">{{ $products->total() }} products</p>
    <a href="{{ route('admin.products.create') }}" class="btn-primary text-xs px-4 py-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Add Product
    </a>
</div>

<div class="bg-white rounded-xl border border-stone-100 shadow-soft overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[10px] font-heading font-bold text-stone-400 uppercase tracking-widest border-b border-stone-100 bg-stone-50">
                    <th class="px-6 py-3">Product</th>
                    <th class="px-6 py-3">Category</th>
                    <th class="px-6 py-3">Price</th>
                    <th class="px-6 py-3">Stock</th>
                    <th class="px-6 py-3">Featured</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-50">
                @forelse($products as $product)
                    @php
                        $catBadges = ['tshirt' => 'badge-tshirt', 'cap' => 'badge-cap', 'tote_bag' => 'badge-tote_bag'];
                        $catLabels = ['tshirt' => 'T-Shirt', 'cap' => 'Cap', 'tote_bag' => 'Tote Bag'];
                    @endphp
                    <tr class="hover:bg-stone-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-lg overflow-hidden bg-[#F5F5F5] flex-shrink-0">
                                    <img src="{{ asset($product->image_path ?: 'images/products/placeholder.jpg') }}" alt="" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="font-heading font-semibold text-[#333333] text-sm leading-tight">{{ $product->name }}</p>
                                    <p class="text-[10px] text-stone-400">#{{ $product->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4"><span class="badge {{ $catBadges[$product->category] ?? '' }}">{{ $catLabels[$product->category] ?? $product->category }}</span></td>
                        <td class="px-6 py-4 font-heading font-bold text-[#333333]">R{{ number_format($product->price, 2) }}</td>
                        <td class="px-6 py-4">
                            @if($product->stock_quantity === 0)
                                <span class="text-xs font-semibold text-red-500">Out of Stock</span>
                            @elseif($product->stock_quantity < 5)
                                <span class="text-xs font-semibold text-amber-500">Low ({{ $product->stock_quantity }})</span>
                            @else
                                <span class="text-xs font-medium text-emerald-600">{{ $product->stock_quantity }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.products.toggleFeatured', $product) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs font-semibold px-3 py-1 rounded-full transition-colors
                                    {{ $product->is_featured ? 'bg-[#D4C7B0]/40 text-[#A3A380] hover:bg-[#D4C7B0]/60' : 'bg-stone-100 text-stone-400 hover:bg-stone-200' }}">
                                    {{ $product->is_featured ? '★ Featured' : '☆ Feature' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-xs font-semibold text-stone-400 hover:text-[#333333] transition-colors">Edit</a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-stone-300 hover:text-red-500 transition-colors">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-stone-400">
                        No products yet. <a href="{{ route('admin.products.create') }}" class="text-[#A3A380] hover:text-[#333333] font-medium transition-colors">Add one</a>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $products->links() }}</div>
@endsection