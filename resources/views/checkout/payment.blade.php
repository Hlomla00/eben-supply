<x-layouts.app title="Secure Payment">
{{-- Hlomla Magopeni 218070349 - Eben Supply | Group KN3 --}}
<div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="mb-8 text-center">
        <p class="section-label justify-center flex">Step 2 of 3</p>
        <h1 class="section-title text-center">Secure Payment</h1>
        <div class="flex items-center justify-center gap-3 mt-4 text-xs font-heading">
            <span class="text-stone-400">1 Details</span>
            <div class="h-px w-8 bg-stone-200"></div>
            <div class="flex items-center gap-2 text-[#333333] font-semibold">
                <span class="w-6 h-6 rounded-full bg-[#333333] text-white flex items-center justify-center font-bold">2</span>
                <span>Payment</span>
            </div>
            <div class="h-px w-8 bg-stone-200"></div>
            <span class="text-stone-400">3 Confirmation</span>
        </div>
    </div>

    {{-- PayFast mock card --}}
    <div class="card overflow-hidden">

        {{-- Provider header --}}
        <div class="bg-[#F5F5F5] px-6 py-4 border-b border-stone-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-[#333333] rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                        <path d="M2 10h20"/>
                    </svg>
                </div>
                <div>
                    <p class="font-heading font-bold text-sm text-[#333333]">PayFast</p>
                    <p class="text-xs text-stone-400">Secure checkout gateway (demo)</p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 text-emerald-600 text-xs font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                256-bit SSL
            </div>
        </div>

        <div class="p-6 space-y-6">

            {{-- Merchant --}}
            <div class="flex items-center gap-3 pb-5 border-b border-stone-100">
                <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-[#D4C7B0]">
                    <img src="{{ asset('images/products/logo.jpg') }}" alt="" class="w-full h-full object-cover">
                </div>
                <div>
                    <p class="font-heading font-bold text-sm text-[#333333]">Eben Supply</p>
                    <p class="text-xs text-stone-400">Woodstock, Cape Town</p>
                </div>
            </div>

            {{-- Amount --}}
            <div class="text-center py-2">
                <p class="text-stone-400 text-xs font-heading uppercase tracking-wider mb-1">Amount Due</p>
                <p class="font-heading font-black text-5xl text-[#333333]">R{{ number_format($pending['total'], 2) }}</p>
                <p class="text-xs text-stone-400 mt-1">
                    {{ $pending['fulfillment'] === 'delivery' ? 'Includes R60.00 delivery' : 'Store pickup — no delivery fee' }}
                </p>
            </div>

            {{-- Order summary --}}
            <div class="bg-[#F5F5F5] rounded-xl p-4 space-y-2 text-xs">
                @foreach($cartItems as $item)
                    <div class="flex justify-between text-stone-600">
                        <span>{{ $item->product->name }}{{ $item->size ? ' ('.$item->size.')' : '' }} × {{ $item->quantity }}</span>
                        <span class="font-semibold">R{{ number_format($item->quantity * $item->product->price, 2) }}</span>
                    </div>
                @endforeach
                @if($pending['fulfillment'] === 'delivery')
                    <div class="flex justify-between text-stone-600 border-t border-stone-200 pt-2 mt-2">
                        <span>Delivery</span><span class="font-semibold">R60.00</span>
                    </div>
                @endif
                <div class="flex justify-between font-heading font-bold text-[#333333] border-t border-stone-200 pt-2 mt-2">
                    <span>Total</span><span>R{{ number_format($pending['total'], 2) }}</span>
                </div>
            </div>

            {{-- Demo card form --}}
            <div class="space-y-4">
                <div>
                    <label class="form-label">Card Number</label>
                    <input type="text" value="4111 1111 1111 1111" readonly
                           class="form-input bg-stone-50 text-stone-400 font-mono cursor-not-allowed">
                    <p class="text-[10px] text-stone-400 mt-1">Demo card — no real transaction</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Expiry</label>
                        <input type="text" value="12/28" readonly class="form-input bg-stone-50 text-stone-400 font-mono cursor-not-allowed">
                    </div>
                    <div>
                        <label class="form-label">CVV</label>
                        <input type="text" value="***" readonly class="form-input bg-stone-50 text-stone-400 font-mono cursor-not-allowed">
                    </div>
                </div>
            </div>

            {{-- Pay button --}}
            <form action="{{ route('checkout.confirm') }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary w-full py-4 text-sm justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Pay Now — R{{ number_format($pending['total'], 2) }}
                </button>
            </form>

            <p class="text-center text-[10px] text-stone-400 leading-relaxed">
                Simulated payment for demonstration purposes only.<br>No real money is processed. CPUT PRT362S Demo.
            </p>
        </div>
    </div>

    <div class="mt-5 text-center">
        <a href="{{ route('checkout.index') }}" class="flex items-center justify-center gap-1 text-xs text-stone-400 hover:text-[#333333] transition-colors font-medium">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7-7-7 7 7 7"/></svg>
            Back to details
        </a>
    </div>
</div>
</x-layouts.app>