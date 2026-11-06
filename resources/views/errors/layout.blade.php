<!DOCTYPE html>
{{-- PRT362S — Eben Supply | Group KN3 --}}
{{-- Standalone on purpose: no database calls, so it still renders when the database is down. --}}
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>@yield('title') — Eben Supply</title>
    @include('partials.fonts')
    @vite(['resources/css/app.css'])
</head>
<body class="bg-[#F5F5F5] text-[#333333] min-h-screen antialiased flex flex-col">
    <header class="bg-white border-b border-stone-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-full overflow-hidden ring-2 ring-[#D4C7B0]">
                    <img src="{{ asset('images/products/logo.jpg') }}" alt="" class="w-full h-full object-cover">
                </span>
                <span class="font-black text-sm tracking-widest font-heading uppercase">Eben Supply</span>
            </a>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-16">
        <div class="text-center max-w-md">
            <p class="font-heading font-black text-7xl text-[#D4C7B0] mb-4">@yield('code')</p>
            <h1 class="font-heading font-black text-2xl mb-3">@yield('title')</h1>
            <p class="text-stone-500 text-sm leading-relaxed mb-8">@yield('message')</p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                @section('actions')
                    <a href="{{ url('/') }}" class="btn-primary text-sm justify-center">Back to the store</a>
                    <a href="{{ url('/products') }}" class="btn-secondary text-sm justify-center">Shop all products</a>
                @show
            </div>
        </div>
    </main>

    <footer class="text-center text-xs text-stone-400 py-6">© {{ date('Y') }} Eben Supply · Woodstock, Cape Town</footer>
</body>
</html>
