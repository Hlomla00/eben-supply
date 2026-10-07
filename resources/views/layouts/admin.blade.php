<!DOCTYPE html>
{{-- PRT362S — Eben Supply | Group KN3 --}}
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $title ?? 'Admin') — Eben Supply</title>

    @fluxAppearance
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-50 text-[#333333] min-h-screen antialiased">

    <div class="flex min-h-screen">

        {{-- ── Sidebar ── --}}
        <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:flex-shrink-0 bg-[#333333] text-white">
            <div class="h-16 flex items-center gap-3 px-6 border-b border-white/10">
                <div class="w-8 h-8 rounded-full overflow-hidden ring-2 ring-[#D4C7B0] flex-shrink-0">
                    <img src="{{ asset('images/products/logo.jpg') }}" alt="Eben Supply" class="w-full h-full object-cover">
                </div>
                <span class="font-black text-sm tracking-widest font-heading uppercase">Eben Supply</span>
            </div>

            <nav class="flex-1 px-3 py-6 space-y-1">
                @foreach([
                    ['Dashboard', route('admin.dashboard'), 'admin.dashboard', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['Products', route('admin.products.index'), 'admin.products.*', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10'],
                    ['Orders', route('admin.orders.index'), 'admin.orders.*', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ['Inventory', route('admin.inventory'), 'admin.inventory', 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4'],
                ] as [$label, $href, $pattern, $iconPath])
                    <a href="{{ $href }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                              {{ request()->routeIs($pattern) ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
                        </svg>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="px-3 py-4 border-t border-white/10 space-y-1">
                <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/60 hover:bg-white/5 hover:text-white transition-colors">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to store
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/60 hover:bg-white/5 hover:text-white transition-colors">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sign out
                    </button>
                </form>
            </div>
        </aside>

        {{-- ── Main column ── --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- Mobile top bar (sidebar is desktop-only above) --}}
            <header class="lg:hidden bg-[#333333] text-white h-14 flex items-center justify-between px-4">
                <span class="font-black text-sm tracking-widest font-heading uppercase">Eben Supply Admin</span>
                <a href="{{ route('home') }}" class="text-xs text-white/60 hover:text-white">Store →</a>
            </header>

            {{-- Page header --}}
            <div class="bg-white border-b border-stone-100 px-4 sm:px-8 py-5">
                <h1 class="font-heading font-black text-xl text-[#333333]">@yield('title', $title ?? 'Admin')</h1>
            </div>

            {{-- Flash messages --}}
            @if(session('success'))
                <flux:toast variant="success">{{ session('success') }}</flux:toast>
            @endif
            @if(session('error'))
                <flux:toast variant="danger">{{ session('error') }}</flux:toast>
            @endif

            <main class="flex-1 px-4 sm:px-8 py-8">
                @yield('content', $slot ?? '')
            </main>
        </div>
    </div>

    @fluxScripts
    @stack('scripts')
</body>
</html>
