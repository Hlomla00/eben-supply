<x-layouts.app title="Home">
{{-- Dumisane Madondo 230949703 | Group KN3 --}}

{{-- ── Hero ── --}}
<section class="relative min-h-[90vh] flex items-center overflow-hidden bg-[#F5F5F5]">
    <div class="absolute inset-0">
        <img src="{{ asset('images/products/background image.jpeg') }}"
             alt="Eben Supply" class="w-full h-full object-cover object-center">
        <div class="absolute inset-0 bg-white/85 sm:bg-transparent"></div>
        <div class="absolute inset-0 hidden sm:block bg-gradient-to-r from-white/92 via-white/65 to-transparent"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-28">
        <div class="max-w-xl">
            <p class="font-heading font-bold text-[#333333] uppercase tracking-widest text-xs mb-4">Woodstock, Cape Town</p>
            <h1 class="font-heading font-black text-5xl md:text-6xl lg:text-7xl leading-[1.05] text-[#333333] mb-6">
                Wear<br>
                <em class="font-accent font-normal not-italic text-[#A3A380]">the</em><br>
                Culture.
            </h1>
            <p class="text-[#333333] font-semibold text-lg leading-relaxed mb-10 max-w-sm">
                Premium branded merchandise — graphic tees, caps &amp; tote bags — from the heart of Woodstock.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('products.index') }}" class="btn-primary text-sm px-8 py-3.5">
                    Shop Now
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/></svg>
                </a>
                <a href="{{ route('products.index', ['category' => 'tshirt']) }}" class="btn-secondary text-sm px-8 py-3.5">View T-Shirts</a>
            </div>
        </div>
    </div>

    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 text-stone-400 animate-bounce">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/>
        </svg>
    </div>
</section>

{{-- ── Category strip ── --}}
<section class="border-y border-stone-100 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-3 gap-4 md:gap-8 max-w-xl mx-auto">
            @foreach([
                ['tshirt',   'T-Shirts',  'M9 3H5L2 8v1h3v12h14V9h3V8l-3-5h-4m-6 0v4m6-4v4m-6 0h6'],
                ['cap',      'Caps',      'M12 4C8 4 5 6.5 4 10h16c-1-3.5-4-6-8-6zM4 10v3h16v-3M4 13l2 5h12l2-5'],
                ['tote_bag', 'Tote Bags', 'M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0'],
            ] as [$cat, $label, $path])
            <a href="{{ route('products.index', ['category' => $cat]) }}"
               class="group flex flex-col items-center gap-3 p-5 rounded-xl hover:bg-[#F5F5F5] transition-all duration-200">
                <div class="w-14 h-14 bg-[#F5F5F5] group-hover:bg-[#D4C7B0]/40 rounded-xl flex items-center justify-center transition-colors duration-200">
                    <svg class="w-7 h-7 text-[#A3A380] group-hover:text-[#333333] transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-stone-500 group-hover:text-[#333333] uppercase tracking-widest font-heading transition-colors duration-200">{{ $label }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Featured Products ── --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="flex items-end justify-between mb-10" data-reveal>
        <div>
            <p class="section-label">Our Merch</p>
            <h2 class="section-title">Featured <span class="text-[#A3A380]">Drops</span></h2>
        </div>
        <a href="{{ route('products.index') }}" class="hidden md:flex items-center gap-1 text-sm font-semibold text-[#A3A380] hover:text-[#333333] transition-colors font-heading">
            View All
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/></svg>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-7">
        @forelse($featuredProducts as $i => $product)
            @php
                $catBadges = ['tshirt' => 'badge-tshirt', 'cap' => 'badge-cap', 'tote_bag' => 'badge-tote_bag'];
                $catLabels = ['tshirt' => 'T-Shirt', 'cap' => 'Cap', 'tote_bag' => 'Tote Bag'];
            @endphp
            <a href="{{ route('products.show', $product) }}"
               class="card card-hover group overflow-hidden block"
               data-reveal style="transition-delay: {{ $i * 80 }}ms">
                <div class="aspect-square product-img-wrap bg-[#F5F5F5]">
                    <img src="{{ asset($product->image_path ?: 'images/products/placeholder.jpg') }}"
                         alt="{{ $product->name }}">
                </div>
                <div class="p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="badge {{ $catBadges[$product->category] ?? '' }}">{{ $catLabels[$product->category] ?? $product->category }}</span>
                        @if($product->stock_quantity === 0)
                            <span class="text-[10px] text-stone-400 font-medium">Out of Stock</span>
                        @elseif($product->stock_quantity < 5)
                            <span class="text-[10px] text-amber-600 font-semibold">Low Stock</span>
                        @endif
                    </div>
                    <h3 class="font-heading font-bold text-[#333333] text-base leading-snug mb-2 group-hover:text-[#A3A380] transition-colors line-clamp-2">
                        {{ $product->name }}
                    </h3>
                    <div class="flex items-center justify-between">
                        <span class="font-heading font-black text-lg text-[#333333]">R{{ number_format($product->price, 2) }}</span>
                        <span class="w-8 h-8 flex items-center justify-center rounded-full bg-[#F5F5F5] group-hover:bg-[#D4C7B0] transition-colors">
                            <svg class="w-4 h-4 text-[#A3A380] group-hover:text-[#333333] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/></svg>
                        </span>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-3 text-center py-16 text-stone-400">No featured products yet.</div>
        @endforelse
    </div>

    <div class="mt-10 text-center md:hidden">
        <a href="{{ route('products.index') }}" class="btn-secondary">View All Products</a>
    </div>
</section>

{{-- ── Stats strip ── --}}
<section class="bg-[#333333] text-white py-14" data-reveal>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            @foreach([
                ['15+', 'Products'],
                ['3', 'Categories'],
                ['R60', 'Flat Delivery'],
                ['100%', 'Cape Town Made'],
            ] as [$num, $label])
            <div>
                <p class="font-heading font-black text-4xl text-[#D4C7B0] mb-1">{{ $num }}</p>
                <p class="text-stone-400 text-xs uppercase tracking-widest font-heading">{{ $label }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Brand Story ── --}}
<section class="bg-[#F5F5F5] py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-14 items-center">
            <div data-reveal>
                <p class="section-label">About Us</p>
                <h2 class="section-title mb-6">
                    From Woodstock<br>
                    <span class="text-[#A3A380]">To the World</span>
                </h2>
                <p class="text-stone-500 leading-relaxed mb-4">
                    Eben Supply is a Cape Town-based branded merchandise brand rooted in Woodstock's creative community.
                    Every piece is designed with intent — bold, minimal, built to last.
                </p>
                <p class="font-accent italic text-[#A3A380] text-lg mb-8">"Street-ready gear for the culture."</p>
                <div class="flex flex-wrap gap-6 text-sm text-stone-500">
                    @foreach([
                        ['Free store pickup', 'M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z'],
                        ['R60 flat delivery', 'M5 12h14M12 5l7 7-7 7'],
                        ['Secure checkout', 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                    ] as [$text, $iconPath])
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-[#D4C7B0]/40 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-[#A3A380]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
                            </svg>
                        </div>
                        {{ $text }}
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4" data-reveal>
                <img src="{{ asset('images/products/models.jpeg') }}"
                     alt="Eben Supply models" class="w-full aspect-square object-cover rounded-xl shadow-soft">
                <img src="{{ asset('images/products/street model.jpeg') }}"
                     alt="Street style" class="w-full aspect-square object-cover rounded-xl shadow-soft mt-8">
            </div>
        </div>
    </div>
</section>

{{-- ── Process strip ── --}}
<section class="bg-white py-16 border-y border-stone-100" data-reveal>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-10 text-center">
            @foreach([
                ['1', 'Browse & Pick', 'Choose from our collection of tees, caps and tote bags.', 'M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0'],
                ['2', 'Pay Securely', 'Quick checkout via PayFast or Ozow — fast & safe.', 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                ['3', 'Pick Up or Deliver', 'Collect free in Woodstock or get it delivered for R60.', 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
            ] as [$num, $title, $desc, $icon])
            <div class="flex flex-col items-center gap-4">
                <div class="relative">
                    <div class="w-16 h-16 bg-[#F5F5F5] rounded-2xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-[#A3A380]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                        </svg>
                    </div>
                    <span class="absolute -top-2 -right-2 w-6 h-6 bg-[#333333] text-white rounded-full text-xs font-heading font-black flex items-center justify-center">{{ $num }}</span>
                </div>
                <h3 class="font-heading font-bold text-[#333333] text-base">{{ $title }}</h3>
                <p class="text-stone-400 text-sm leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── CTA ── --}}
<section class="bg-[#333333] text-white py-20 text-center" data-reveal>
    <div class="max-w-2xl mx-auto px-4">
        <p class="section-label text-stone-400 mb-3">Ready?</p>
        <h2 class="font-heading font-black text-4xl md:text-5xl mb-4">Shop the Collection</h2>
        <p class="font-accent italic text-[#D4C7B0] text-xl mb-10">Gear up. Stand out.</p>
        <div class="flex flex-wrap gap-4 justify-center">
            <a href="{{ route('products.index') }}" class="btn-accent px-10 py-4 text-base">
                Browse All Products
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/></svg>
            </a>
            @guest
            <a href="{{ route('register') }}" class="btn-secondary bg-transparent border-stone-500 text-stone-300 hover:bg-white/10 hover:text-white px-10 py-4 text-base">
                Create Account
            </a>
            @endguest
        </div>
    </div>
</section>

</x-layouts.app>