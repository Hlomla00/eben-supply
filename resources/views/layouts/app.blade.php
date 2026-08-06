<!DOCTYPE html>
{{-- PRT362S — Eben Supply | Group KN3 --}}
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Eben Supply' }} — Woodstock, Cape Town</title>

    @fluxAppearance
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-[#333333] min-h-screen flex flex-col antialiased">

    {{-- ── Announcement bar ── --}}
    <div class="bg-[#333333] text-white text-xs text-center py-2 tracking-wide font-heading">
        🚚 Free store pickup in Woodstock &nbsp;·&nbsp; Nationwide delivery R60 &nbsp;·&nbsp;
        <span class="hidden sm:inline">Pay securely via PayFast / Ozow</span>
    </div>

    {{-- ── Navigation ── --}}
    <header class="bg-white border-b border-stone-100 sticky top-0 z-50 shadow-soft">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-4">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3 flex-shrink-0">
                    <div class="w-9 h-9 rounded-full overflow-hidden ring-2 ring-[#D4C7B0]">
                        <img src="{{ asset('images/products/logo.jpg') }}" alt="Eben Supply" class="w-full h-full object-cover">
                    </div>
                    <div class="flex flex-col leading-none">
                        <span class="font-black text-base tracking-widest text-[#333333] font-heading uppercase">Eben Supply</span>
                        <span class="text-[10px] text-[#A3A380] tracking-widest uppercase font-heading hidden sm:block">Woodstock, Cape Town</span>
                    </div>
                </a>

                {{-- Desktop centre nav --}}
                <nav class="hidden lg:flex items-center gap-7 flex-1 justify-center">
                    <a href="{{ route('home') }}" class="nav-link pb-1 {{ request()->routeIs('home') ? 'nav-link-active' : '' }}">Home</a>
                    <a href="{{ route('products.index') }}" class="nav-link pb-1 {{ request()->routeIs('products.*') ? 'nav-link-active' : '' }}">Shop</a>
                    <a href="{{ route('products.index', ['category' => 'tshirt']) }}" class="nav-link pb-1 {{ request()->get('category') === 'tshirt' ? 'nav-link-active' : '' }}">T-Shirts</a>
                    <a href="{{ route('products.index', ['category' => 'cap']) }}" class="nav-link pb-1 {{ request()->get('category') === 'cap' ? 'nav-link-active' : '' }}">Caps</a>
                    <a href="{{ route('products.index', ['category' => 'tote_bag']) }}" class="nav-link pb-1 {{ request()->get('category') === 'tote_bag' ? 'nav-link-active' : '' }}">Tote Bags</a>
                </nav>

                {{-- Right icons --}}
                <div class="flex items-center gap-3 flex-shrink-0">

                    {{-- Search — Flux dropdown replaces the old slide-down div --}}
                    <flux:dropdown position="bottom" align="end">
                        <flux:button variant="ghost" icon="magnifying-glass" aria-label="Search" />
                        <flux:menu class="w-72 p-3">
                            <form action="{{ route('products.index') }}" method="GET">
                                <flux:input type="text" name="search" value="{{ request('search') }}"
                                            placeholder="Search for t-shirts, caps, tote bags…" />
                            </form>
                        </flux:menu>
                    </flux:dropdown>

                    {{-- Cart --}}
                    <a href="{{ route('cart.index') }}" class="relative text-stone-400 hover:text-[#333333] transition-colors p-1" aria-label="Cart">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4zM3 6h18M16 10a4 4 0 0 1-8 0"/>
                        </svg>
                        @php $cartCount = \App\Http\Controllers\CartController::getCount(); @endphp
                        @if($cartCount > 0)
                            <span id="cart-badge" class="absolute -top-0.5 -right-0.5 bg-[#D4C7B0] text-[#333333] text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center leading-none font-heading">{{ $cartCount }}</span>
                        @endif
                    </a>

                    {{-- Auth --}}
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="hidden md:flex text-[10px] font-bold text-[#A3A380] border border-[#D4C7B0] px-3 py-1.5 rounded-full hover:bg-[#F5F5F5] transition-colors font-heading uppercase tracking-widest">
                                Admin
                            </a>
                        @endif

                        {{-- User menu — Flux dropdown replaces the click-JS version --}}
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" icon-trailing="chevron-down">
                                <span class="hidden md:inline">{{ Str::before(auth()->user()->name, ' ') }}</span>
                            </flux:button>
                            <flux:menu>
                                <div class="px-4 py-3 border-b border-stone-50">
                                    <p class="text-xs font-semibold text-[#333333] truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[11px] text-stone-400 truncate">{{ auth()->user()->email }}</p>
                                </div>
                                <flux:menu.item href="{{ route('orders.index') }}" icon="clipboard-document-list">My Orders</flux:menu.item>
                                <flux:menu.item href="{{ route('profile.edit') }}" icon="user-circle">Profile</flux:menu.item>
                                <flux:menu.separator />
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <flux:menu.item as="button" type="submit" icon="arrow-right-on-rectangle" variant="danger">Sign Out</flux:menu.item>
                                </form>
                            </flux:menu>
                        </flux:dropdown>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:block text-sm text-stone-500 hover:text-[#333333] font-medium transition-colors">Sign In</a>
                        <a href="{{ route('register') }}" class="hidden sm:inline-flex btn-accent text-xs px-4 py-2">Register</a>
                    @endauth

                    {{-- Mobile hamburger — Flux navbar/menu handles the toggle state itself --}}
                    <flux:dropdown position="bottom" align="end" class="lg:hidden">
                        <flux:button variant="ghost" icon="bars-3" aria-label="Menu" />
                        <flux:menu class="w-64">
                            @foreach([
                                ['Home', route('home')],
                                ['Shop All', route('products.index')],
                                ['T-Shirts', route('products.index', ['category'=>'tshirt'])],
                                ['Caps', route('products.index', ['category'=>'cap'])],
                                ['Tote Bags', route('products.index', ['category'=>'tote_bag'])],
                            ] as [$label, $href])
                                <flux:menu.item href="{{ $href }}">{{ $label }}</flux:menu.item>
                            @endforeach
                            <flux:menu.separator />
                            @auth
                                @if(auth()->user()->isAdmin())
                                    <flux:menu.item href="{{ route('admin.dashboard') }}" icon="squares-2x2">Admin Panel</flux:menu.item>
                                @endif
                                <flux:menu.item href="{{ route('orders.index') }}">My Orders</flux:menu.item>
                                <flux:menu.item href="{{ route('profile.edit') }}">Profile</flux:menu.item>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <flux:menu.item as="button" type="submit" variant="danger">Sign Out</flux:menu.item>
                                </form>
                            @else
                                <flux:menu.item href="{{ route('login') }}">Sign In</flux:menu.item>
                                <flux:menu.item href="{{ route('register') }}">Create Account</flux:menu.item>
                            @endauth
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        </div>
    </header>

    {{-- ── Toast flash messages — Flux toast replaces the custom fade/dismiss JS ── --}}
    @if(session('success'))
        <flux:toast variant="success">{{ session('success') }}</flux:toast>
    @endif
    @if(session('error'))
        <flux:toast variant="danger">{{ session('error') }}</flux:toast>
    @endif

    {{-- Main content --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- ── Footer ── --}}
    <footer class="bg-[#333333] text-white mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-10">
                <div class="sm:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-[#D4C7B0]">
                            <img src="{{ asset('images/products/logo.jpg') }}" alt="" class="w-full h-full object-cover">
                        </div>
                        <span class="font-black text-lg tracking-widest font-heading uppercase text-white">Eben Supply</span>
                    </div>
                    <p class="text-stone-400 text-sm leading-relaxed max-w-xs font-accent italic">
                        "Premium branded merchandise crafted for the streets of Cape Town."
                    </p>
                    <div class="flex items-center gap-2 mt-4 text-stone-400 text-sm">
                        <svg class="w-4 h-4 text-[#D4C7B0] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                        Woodstock, Cape Town, 7925
                    </div>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-stone-400 uppercase tracking-widest mb-4 font-heading">Shop</h4>
                    <ul class="space-y-2 text-sm text-stone-300">
                        <li><a href="{{ route('products.index', ['category' => 'tshirt']) }}" class="hover:text-[#D4C7B0] transition-colors">T-Shirts</a></li>
                        <li><a href="{{ route('products.index', ['category' => 'cap']) }}" class="hover:text-[#D4C7B0] transition-colors">Caps</a></li>
                        <li><a href="{{ route('products.index', ['category' => 'tote_bag']) }}" class="hover:text-[#D4C7B0] transition-colors">Tote Bags</a></li>
                        <li><a href="{{ route('products.index') }}" class="hover:text-[#D4C7B0] transition-colors">All Products</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-stone-400 uppercase tracking-widest mb-4 font-heading">Account</h4>
                    <ul class="space-y-2 text-sm text-stone-300">
                        @auth
                            <li><a href="{{ route('orders.index') }}" class="hover:text-[#D4C7B0] transition-colors">My Orders</a></li>
                            <li><a href="{{ route('profile.edit') }}" class="hover:text-[#D4C7B0] transition-colors">Profile</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="hover:text-[#D4C7B0] transition-colors">Sign In</a></li>
                            <li><a href="{{ route('register') }}" class="hover:text-[#D4C7B0] transition-colors">Register</a></li>
                        @endauth
                        <li><a href="{{ route('cart.index') }}" class="hover:text-[#D4C7B0] transition-colors">Cart</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-stone-700 mt-10 pt-8 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-stone-500">
                <span>© {{ date('Y') }} Eben Supply. All rights reserved.</span>
                <span>CPUT PRT362S — Group KN3</span>
            </div>
        </div>
    </footer>

    {{-- Back-to-top stays vanilla — it's a tiny scroll listener, no need for Livewire --}}
    <button id="back-to-top" aria-label="Back to top"
            class="hidden fixed bottom-6 right-6 z-50 w-11 h-11 bg-[#333333] text-white rounded-full shadow-card hover:bg-[#444] transition-all duration-200 flex items-center justify-center">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
    </button>

    @fluxScripts
    @stack('scripts')
</body>
</html>