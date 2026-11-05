@extends('layouts.app')
{{-- Dumisane Madondo 230949703 | Group KN3 --}}
@section('title', $sectionTitle ?? 'Shop')

@section('content')
@php
    $isKidsSection = $listingRoute === 'kids.index';
    $categoryTitle = $category === 'kids' ? 'Kids' : \App\Models\Product::categoryPluralLabel($category);
    // Pills: inside Kids they pick a type; elsewhere Kids is its own category next to the others.
    $pills = [[null, $isKidsSection ? 'All Kids' : 'All']];
    foreach (\App\Models\Product::categoriesInUse($isKidsSection) as $key) {
        $pills[] = [$key, \App\Models\Product::categoryPluralLabel($key)];
    }
    if (!$isKidsSection) {
        $pills[] = ['kids', 'Kids'];
    }
    $activeFilters = collect($filters)->except('sort')->filter(fn ($v) => $v !== null && $v !== false)->count();
    $keep = array_filter(['search' => request('search'), 'sort' => $filters['sort'] !== 'newest' ? $filters['sort'] : null]);
@endphp

{{-- Page header --}}
<div class="bg-[#F5F5F5] border-b border-stone-200 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="section-label">Browse</p>
        <h1 class="section-title">
            @if($category) {{ trim(($sectionTitle ?? '') . ' ' . $categoryTitle) }}
            @elseif(request('search')) Results for "{{ request('search') }}"{{ $sectionTitle ? ' in ' . $sectionTitle : '' }}
            @else {{ $sectionTitle ?? 'All Products' }}
            @endif
        </h1>
        @if($listingRoute === 'sale.index')
            <p class="text-stone-400 text-sm mt-1">Sale prices end automatically on each item's end date.</p>
        @endif
        <p class="text-stone-400 text-sm mt-1">{{ $products->total() }} {{ Str::plural('product', $products->total()) }}</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Category pills + search --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-4">
        <div class="flex flex-wrap gap-2 flex-1">
            @foreach($pills as [$val, $label])
                @php
                    // On the shop page the Kids pill opens the Kids page itself.
                    $href = ($val === 'kids' && $listingRoute === 'products.index')
                        ? route('kids.index', $keep)
                        : route($listingRoute, array_filter(['category' => $val] + $keep));
                @endphp
                <a href="{{ $href }}"
                   class="px-4 py-2 rounded-full text-xs font-semibold font-heading transition-all
                       {{ $category === $val ? 'bg-[#333333] text-white shadow-soft' : 'bg-white border border-stone-200 text-stone-500 hover:border-[#D4C7B0] hover:text-[#333333]' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <form action="{{ route($listingRoute) }}" method="GET" class="flex gap-2 w-full sm:w-auto">
            @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
            <div class="relative flex-1 sm:flex-none">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search products…"
                       class="form-input pl-9 py-2 text-sm sm:w-52">
            </div>
            <button type="submit" class="btn-primary text-xs px-4 py-2 flex-shrink-0">Search</button>
            @if(request('search'))
                <a href="{{ route($listingRoute, $category ? ['category' => $category] : []) }}"
                   class="btn-secondary text-xs px-3 py-2 flex-shrink-0" aria-label="Clear search">✕</a>
            @endif
        </form>
    </div>

    {{-- Filters & sort --}}
    <details class="card mb-8 group/filters" @if($activeFilters) open @endif>
        <summary class="flex items-center justify-between gap-3 px-5 py-3 cursor-pointer select-none list-none">
            <span class="flex items-center gap-2 font-heading font-bold text-sm text-[#333333]">
                <svg class="w-4 h-4 text-[#A3A380]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18M6 12h12M10 19h4"/></svg>
                Filter &amp; Sort
                @if($activeFilters)<span class="badge bg-[#333333] text-white normal-case">{{ $activeFilters }} active</span>@endif
            </span>
            <span class="hidden sm:inline text-xs text-stone-400">Sorted by {{ \App\Http\Controllers\ProductController::SORTS[$filters['sort']] }}</span>
        </summary>

        <form action="{{ route($listingRoute) }}" method="GET" class="border-t border-stone-100 px-5 py-4 grid grid-cols-2 lg:grid-cols-6 gap-4 items-end">
            @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
            @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif

            <div>
                <label class="form-label" for="filter-size">Size in stock</label>
                <select id="filter-size" name="size" class="form-input py-2 text-sm">
                    <option value="">Any size</option>
                    @foreach($sizeOptions as $size)
                        <option value="{{ $size }}" {{ $filters['size'] === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label" for="filter-min">Min price (R)</label>
                <input id="filter-min" type="number" name="min_price" min="0" step="1" inputmode="numeric"
                       value="{{ $filters['min_price'] !== null ? (int) $filters['min_price'] : '' }}" placeholder="0"
                       class="form-input py-2 text-sm">
            </div>

            <div>
                <label class="form-label" for="filter-max">Max price (R)</label>
                <input id="filter-max" type="number" name="max_price" min="0" step="1" inputmode="numeric"
                       value="{{ $filters['max_price'] !== null ? (int) $filters['max_price'] : '' }}" placeholder="Any"
                       class="form-input py-2 text-sm">
            </div>

            <div>
                <label class="form-label" for="filter-sort">Sort by</label>
                <select id="filter-sort" name="sort" class="form-input py-2 text-sm">
                    @foreach(\App\Http\Controllers\ProductController::SORTS as $key => $label)
                        <option value="{{ $key }}" {{ $filters['sort'] === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-2 pb-1">
                <label class="flex items-center gap-2 cursor-pointer text-sm text-stone-600">
                    <input type="checkbox" name="in_stock" value="1" {{ $filters['in_stock'] ? 'checked' : '' }} class="w-4 h-4 accent-[#333333] rounded"> In stock only
                </label>
                @if($listingRoute !== 'sale.index')
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-stone-600">
                        <input type="checkbox" name="on_sale" value="1" {{ $filters['on_sale'] ? 'checked' : '' }} class="w-4 h-4 accent-[#333333] rounded"> On sale only
                    </label>
                @endif
            </div>

            <div class="flex gap-2 col-span-2 lg:col-span-1">
                <button type="submit" class="btn-primary text-xs px-4 py-2 flex-1">Apply</button>
                @if($activeFilters || $filters['sort'] !== 'newest')
                    <a href="{{ route($listingRoute, array_filter(['category' => $category, 'search' => request('search')])) }}"
                       class="btn-secondary text-xs px-3 py-2">Clear</a>
                @endif
            </div>
        </form>
    </details>

    {{-- Product grid --}}
    @if($products->count())
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
            @foreach($products as $i => $product)
                @php
                @endphp
                <div class="card card-hover group overflow-hidden flex flex-col" data-reveal style="transition-delay: {{ ($i % 4) * 70 }}ms">
                    {{-- Image + hover actions --}}
                    <a href="{{ route('products.show', $product) }}" class="block relative aspect-square product-img-wrap bg-[#F5F5F5]">
                        <img src="{{ $product->image_url }}"
                             loading="{{ $i < 4 ? 'eager' : 'lazy' }}" decoding="async" alt="{{ $product->name }}">
                        {{-- Hover overlay --}}
                        <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center pb-4">
                            <span class="bg-white text-[#333333] text-xs font-heading font-bold px-4 py-2 rounded-full shadow-soft translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                                View Product →
                            </span>
                        </div>
                        <x-product-badges :product="$product" />
                    </a>

                    <div class="p-4 flex flex-col flex-1">
                        <div class="flex items-center justify-between mb-2">
                            <span class="badge {{ $product->category_badge }}">{{ $product->category_label }}</span>
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
                            <x-product-price :product="$product" />
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
            <a href="{{ route($listingRoute) }}" class="btn-secondary text-sm">Clear Filters</a>
        </div>
    @endif
</div>
@endsection