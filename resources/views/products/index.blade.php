@extends('layouts.app')
{{-- Dumisane Madondo 230949703 | Group KN3 --}}
@section('title', 'Shop')

@section('content')

{{-- Page header --}}
<div class="bg-[#F5F5F5] border-b border-stone-200 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="section-label">Browse</p>
        <h1 class="section-title">
            @if($category === 'tshirt') T-Shirts
            @elseif($category === 'cap') Caps
            @elseif($category === 'tote_bag') Tote Bags
            @elseif(request('search')) Results for "{{ request('search') }}"
            @else All Products
            @endif
        </h1>
        @if(request('search'))
            <p class="text-stone-400 text-sm mt-1">{{ $products->total() }} result{{ $products->total() !== 1 ? 's' : '' }} found</p>
        @endif
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Filters bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-8">
        {{-- Category pills --}}
        <div class="flex flex-wrap gap-2 flex-1">
            @foreach([
                [null,       'All', $products->total() . ' items'],
                ['tshirt',   'T-Shirts', null],
                ['cap',      'Caps', null],
                ['tote_bag', 'Tote Bags', null],
            ] as [$val, $label, $hint])
            <a href="{{ route('products.index', array_filter(['category' => $val, 'search' => request('search')])) }}"
               class="px-4 py-2 rounded-full text-xs font-semibold font-heading transition-all
                   {{ $category === $val ? 'bg-[#333333] text-white shadow-soft' : 'bg-white border border-stone-200 text-stone-500 hover:border-[#D4C7B0] hover:text-[#333333]' }}">
                {{ $label }}
                @if($hint && $category === $val)<span class="opacity-60 ml-1">{{ $hint }}</span>@endif
            </a>
            @endforeach
        </div>

        {{-- Search --}}
        <form action="{{ route('products.index') }}" method="GET" class="flex gap-2 w-full sm:w-auto">
            @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
            <div class="relative flex-1 sm:flex-none">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search products…"
                       class="form-input pl-9 py-2 text-sm sm:w-52">
            </div>
            <button type="submit" class="btn-primary text-xs px-4 py-2 flex-shrink-0">Search</button>
            @if(request('search'))
                <a href="{{ route('products.index', $category ? ['category' => $category] : []) }}"
                   class="btn-secondary text-xs px-3 py-2 flex-shrink-0">✕</a>
            @endif
        </form>
    </div>

    {{-- Product grid --}}
    @if($products->count())
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
            @foreach($products as $i => $product)
                @php
                    $catBadges = ['tshirt' => 'badge-tshirt', 'cap' => 'badge-cap', 'tote_bag' => 'badge-tote_bag'];
                    $catLabels = ['tshirt' => 'T-Shirt', 'cap' => 'Cap', 'tote_bag' => 'Tote Bag'];
                @endphp
                <div class="card card-hover group overflow-hidden flex flex-col" data-reveal style="transition-delay: {{ ($i % 4) * 70 }}ms">
                    {{-- Image + hover actions --}}
                    <a href="{{ route('products.show', $product) }}" class="block relative aspect-square product-img-wrap bg-[#F5F5F5]">
                        <img src="{{ asset($product->image_path ?: 'images/products/placeholder.jpg') }}"
                             loading="{{ $i < 4 ? 'eager' : 'lazy' }}" decoding="async" alt="{{ $product->name }}">
                        {{-- Hover overlay --}}
                        <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center pb-4">
                            <span class="bg-white text-[#333333] text-xs font-heading font-bold px-4 py-2 rounded-full shadow-soft translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                                View Product →
                            </span>
                        </div>
                        {{-- Featured badge --}}
                        @if($product->is_featured)
                            <div class="absolute top-3 left-3">
                                <span class="bg-[#D4C7B0] text-[#333333] text-[10px] font-heading font-black px-2 py-0.5 rounded-full uppercase tracking-wide">Featured</span>
                            </div>
                        @endif
                    </a>

                    <div class="p-4 flex flex-col flex-1">
                        <div class="flex items-center justify-between mb-2">
                            <span class="badge {{ $catBadges[$product->category] ?? '' }}">{{ $catLabels[$product->category] ?? $product->category }}</span>
                            @if($product->stock_quantity === 0)
                                <span class="text-[10px] text-red-400 font-semibold">Out of Stock</span>
                            @elseif($product->stock_quantity < 5)
                                <span class="text-[10px] text-amber-500 font-semibold">Low Stock</span>
                            @else
                                <span class="text-[10px] text-emerald-600 font-semibold">In Stock</span>
                            @endif
                        </div>

                        <a href="{{ route('products.show', $product) }}" class="block py-1.5">
                            <h3 class="font-heading font-semibold text-sm text-[#333333] hover:text-[#A3A380] transition-colors leading-snug mb-2 line-clamp-2 flex-1">
                                {{ $product->name }}
                            </h3>
                        </a>

                        <div class="flex items-center justify-between mt-auto pt-2">
                            <span class="font-heading font-black text-base text-[#333333]">R{{ number_format($product->price, 2) }}</span>
                            <a href="{{ route('products.show', $product) }}"
                               class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-full bg-[#F5F5F5] hover:bg-[#333333] hover:text-white transition-all duration-200 text-[#A3A380]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-12">{{ $products->links() }}</div>

    @else
        <div class="text-center py-24 bg-white rounded-2xl border border-stone-100">
            <div class="w-16 h-16 bg-[#F5F5F5] rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0"/></svg>
            </div>
            <h3 class="font-heading font-bold text-xl text-stone-400 mb-2">No products found</h3>
            <p class="text-stone-400 text-sm mb-6">Try a different category or clear your search.</p>
            <a href="{{ route('products.index') }}" class="btn-secondary text-sm">Clear Filters</a>
        </div>
    @endif
</div>
@endsection