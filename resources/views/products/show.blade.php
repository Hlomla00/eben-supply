@extends('layouts.app')
{{-- Dumisane Madondo 230949703 | Group KN3 --}}
@section('title', $product->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-stone-400 mb-8 font-heading overflow-x-auto whitespace-nowrap">
        <a href="{{ route('home') }}" class="inline-block px-1 py-2 hover:text-[#333333] transition-colors">Home</a>
        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
        @unless($product->is_kids)
            <a href="{{ route('products.index') }}" class="inline-block px-1 py-2 hover:text-[#333333] transition-colors">Shop</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
        @endunless
        @if($product->is_kids)
            <a href="{{ route('kids.index') }}" class="inline-block px-1 py-2 hover:text-[#333333] transition-colors">Kids</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
        @endif
        <a href="{{ route($product->is_kids ? 'kids.index' : 'products.index', ['category' => $product->category]) }}" class="inline-block px-1 py-2 hover:text-[#333333] transition-colors capitalize">
            {{ $product->category_plural_label }}
        </a>
        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
        <span class="text-[#333333] font-medium truncate max-w-[180px] sm:max-w-xs">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16">

        {{-- ── Product Image ── --}}
        <div class="bg-[#F5F5F5] rounded-2xl overflow-hidden aspect-square shadow-soft product-img-wrap cursor-zoom-in">
            <img src="{{ asset($product->image_path ?: 'images/products/placeholder.jpg') }}"
                 alt="{{ $product->name }}">
        </div>

        {{-- ── Product Details ── --}}
        <div class="flex flex-col">
            @php
                $catBadges = ['tshirt' => 'badge-tshirt', 'cap' => 'badge-cap', 'tote_bag' => 'badge-tote_bag'];
                $catLabels = ['tshirt' => 'T-Shirt', 'cap' => 'Cap', 'tote_bag' => 'Tote Bag'];
            @endphp

            <div class="flex items-center gap-3 mb-4">
                @if($product->is_kids)
                    <a href="{{ route('kids.index') }}" class="badge {{ $product->category_badge }}">{{ $product->category_label }}</a>
                @else
                    <span class="badge {{ $product->category_badge }}">{{ $product->category_label }}</span>
                @endif
                @if($product->isOnSale())
                    <a href="{{ route('sale.index') }}" class="badge bg-red-600 text-white">Sale −{{ $product->sale_percent_off }}%</a>
                @endif
                @if($product->is_featured)
                    <span class="badge bg-[#D4C7B0] text-[#333333]">Featured</span>
                @endif
            </div>

            <h1 class="font-heading font-black text-3xl md:text-4xl text-[#333333] leading-tight mb-3">
                {{ $product->name }}
            </h1>

            <div class="mb-4">
                <x-product-price :product="$product" price-class="text-3xl" />
                @if($product->isOnSale())
                    <p class="text-xs font-semibold text-red-600 mt-1">
                        Sale — save R{{ number_format($product->price - $product->current_price, 2) }} ({{ $product->sale_percent_off }}% off){{ $product->sale_ends_at ? ' · ends ' . $product->sale_ends_at->format('d M Y, H:i') : '' }}
                    </p>
                @endif
            </div>

            {{-- Stock status --}}
            @if($product->stock_quantity === 0)
                <div class="flex items-center gap-2 mb-5">
                    <span class="w-2 h-2 rounded-full bg-red-400 flex-shrink-0"></span>
                    <span class="text-sm font-semibold text-red-500">Out of Stock</span>
                </div>
            @elseif($product->stock_quantity < 5)
                <div class="flex items-center gap-2 mb-5">
                    <span class="w-2 h-2 rounded-full bg-amber-400 flex-shrink-0"></span>
                    <span class="text-sm font-semibold text-amber-600">Only {{ $product->stock_quantity }} left — order soon</span>
                </div>
            @else
                <div class="flex items-center gap-2 mb-5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                    <span class="text-sm font-semibold text-emerald-600">In Stock</span>
                </div>
            @endif

            <p class="text-stone-500 leading-relaxed mb-8 text-sm">{{ $product->description }}</p>

            @if($product->stock_quantity > 0)
                <form action="{{ route('cart.add') }}" method="POST" class="space-y-6" data-add-cart>
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    {{-- Size selector --}}
                    @if($product->sizes->count() > 0)
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <p class="form-label mb-0">Select Size</p>
                                <button type="button" id="size-guide-btn" class="py-2 text-xs text-[#A3A380] hover:text-[#333333] font-medium underline underline-offset-2 transition-colors">Size Guide</button>
                            </div>
                            <input type="hidden" name="size" id="selected-size" value="{{ $product->sizes->first(fn($s) => $s->stock_quantity > 0)?->size }}">
                            <div class="flex flex-wrap gap-2" data-size-group>
                                @foreach($product->sizes as $sz)
                                    <button type="button"
                                            data-size="{{ $sz->size }}"
                                            class="size-btn {{ $sz->stock_quantity === 0 ? 'size-btn-disabled' : '' }} {{ ($loop->first && $sz->stock_quantity > 0) ? 'size-btn-active' : '' }}"
                                            {{ $sz->stock_quantity === 0 ? 'disabled' : '' }}>
                                        {{ $sz->size }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Quantity stepper --}}
                    <div>
                        <p class="form-label mb-3">Quantity</p>
                        <div class="flex items-center gap-0 w-fit rounded-xl border border-stone-200 overflow-hidden">
                            <button type="button" data-qty-dec="product-qty"
                                    class="w-11 h-11 flex items-center justify-center text-stone-500 hover:bg-[#F5F5F5] hover:text-[#333333] transition-colors text-lg font-bold select-none">
                                −
                            </button>
                            <input type="number" id="product-qty" name="quantity" value="1" min="1" max="10"
                                   class="w-14 text-center border-x border-stone-200 text-[#333333] py-2.5 focus:outline-none font-heading font-bold text-sm bg-white">
                            <button type="button" data-qty-inc="product-qty"
                                    class="w-11 h-11 flex items-center justify-center text-stone-500 hover:bg-[#F5F5F5] hover:text-[#333333] transition-colors text-lg font-bold select-none">
                                +
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary w-full py-4 text-sm">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0"/></svg>
                        Add to Cart
                    </button>
                </form>
            @else
                <button disabled class="w-full py-4 rounded-xl bg-stone-100 text-stone-400 font-heading font-bold text-sm cursor-not-allowed">
                    Out of Stock
                </button>
            @endif

            {{-- Trust strips --}}
            <div class="mt-8 pt-8 border-t border-stone-100 grid grid-cols-1 gap-3">
                @foreach([
                    ['Free store pickup — Woodstock, Cape Town', 'M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z'],
                    ['Nationwide delivery — R60 flat rate', 'M5 12h14m0 0-7-7m7 7-7 7'],
                    ['Secure checkout via PayFast / Ozow (demo)', 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                ] as [$text, $iconPath])
                    <div class="flex items-center gap-3 text-sm text-stone-500">
                        <div class="w-8 h-8 rounded-full bg-[#D4C7B0]/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-[#A3A380]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
                            </svg>
                        </div>
                        {{ $text }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Related Products ── --}}
    @if($related->count())
    <div class="mt-20" data-reveal>
        <div class="flex items-end justify-between mb-8">
            <div>
                <p class="section-label">More Like This</p>
                <h2 class="font-heading font-bold text-2xl text-[#333333]">You Might Also Like</h2>
            </div>
            <a href="{{ route('products.index', ['category' => $product->category]) }}"
               class="inline-block py-2 text-sm font-semibold text-[#A3A380] hover:text-[#333333] transition-colors font-heading">
                View All →
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($related as $rel)
                @php
                    $catBadges = ['tshirt' => 'badge-tshirt', 'cap' => 'badge-cap', 'tote_bag' => 'badge-tote_bag'];
                    $catLabels = ['tshirt' => 'T-Shirt', 'cap' => 'Cap', 'tote_bag' => 'Tote Bag'];
                @endphp
                <a href="{{ route('products.show', $rel) }}" class="card card-hover group overflow-hidden block">
                    <div class="aspect-square product-img-wrap bg-[#F5F5F5] relative">
                        <img src="{{ $rel->image_url }}"
                             loading="lazy" decoding="async" alt="{{ $rel->name }}">
                        <x-product-badges :product="$rel" />
                    </div>
                    <div class="p-4">
                        <h3 class="font-heading font-semibold text-sm text-[#333333] group-hover:text-[#A3A380] transition-colors leading-snug mb-1 line-clamp-2">{{ $rel->name }}</h3>
                        <x-product-price :product="$rel" price-class="text-sm" />
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- ── Size Guide Modal ── --}}
<div id="size-guide-modal" class="hidden fixed inset-0 z-[200] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" id="size-modal-bg"></div>
    <div class="relative bg-white rounded-2xl shadow-hover max-w-md w-full p-8 z-10">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-heading font-bold text-lg text-[#333333]">Size Guide</h3>
            <button id="size-guide-close" class="w-8 h-8 flex items-center justify-center rounded-full bg-[#F5F5F5] text-stone-500 hover:bg-[#333333] hover:text-white transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        @if($product->is_kids)
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-stone-100">
                    <th class="text-left pb-3 font-heading font-bold text-xs text-stone-400 uppercase tracking-wider">Size (age)</th>
                    <th class="text-left pb-3 font-heading font-bold text-xs text-stone-400 uppercase tracking-wider">Child's height (cm)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-50">
                @foreach([['2-3Y','92–98'],['4-5Y','104–110'],['6-7Y','116–122'],['8-9Y','128–134'],['10-11Y','140–146'],['12-13Y','152–158']] as [$s,$h])
                <tr>
                    <td class="py-3 font-heading font-bold text-[#333333]">{{ $s }}</td>
                    <td class="py-3 text-stone-500">{{ $h }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-stone-100">
                    <th class="text-left pb-3 font-heading font-bold text-xs text-stone-400 uppercase tracking-wider">Size</th>
                    <th class="text-left pb-3 font-heading font-bold text-xs text-stone-400 uppercase tracking-wider">Chest (cm)</th>
                    <th class="text-left pb-3 font-heading font-bold text-xs text-stone-400 uppercase tracking-wider">Length (cm)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-50">
                @foreach([['S','88–93','69'],['M','94–99','72'],['L','100–107','74'],['XL','108–116','77'],['XXL','117–126','79']] as [$s,$c,$l])
                <tr>
                    <td class="py-3 font-heading font-bold text-[#333333]">{{ $s }}</td>
                    <td class="py-3 text-stone-500">{{ $c }}</td>
                    <td class="py-3 text-stone-500">{{ $l }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        <p class="text-xs text-stone-400 mt-4">Measurements are approximate. When in doubt, size up.</p>
    </div>
</div>

@push('scripts')
<script>
    // Size guide modal
    const modal    = document.getElementById('size-guide-modal');
    const openBtn  = document.getElementById('size-guide-btn');
    const closeBtn = document.getElementById('size-guide-close');
    const bg       = document.getElementById('size-modal-bg');

    const openModal  = () => modal?.classList.remove('hidden');
    const closeModal = () => modal?.classList.add('hidden');

    openBtn?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    bg?.addEventListener('click', closeModal);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
</script>
@endpush
@endsection