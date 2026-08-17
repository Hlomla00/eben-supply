<x-layouts.app title="Shopping Cart">
{{-- Hlomla Magopeni 218070349 | Group KN3 --}}
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="mb-8">
        <p class="section-label">Your Bag</p>
        <h1 class="section-title">Shopping Cart</h1>
    </div>

    @if($cartItems->count())
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Items --}}
            <div class="lg:col-span-2 space-y-4">
                @foreach($cartItems as $item)
                    <div class="card p-5 flex gap-5">
                        <div class="w-24 h-24 rounded-xl overflow-hidden bg-[#F5F5F5] flex-shrink-0">
                            <img src="{{ asset($item->product->image_path ?: 'images/products/placeholder.jpg') }}"
                                 alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-heading font-bold text-[#333333] leading-snug mb-1">{{ $item->product->name }}</h3>
                            @if($item->size)
                                <span class="inline-block text-xs text-stone-400 bg-stone-50 border border-stone-100 rounded-full px-2 py-0.5 mb-2">Size: {{ $item->size }}</span>
                            @endif
                            <p class="font-heading font-black text-[#333333]">R{{ number_format($item->product->price, 2) }}</p>
                        </div>
                        <div class="flex flex-col items-end justify-between">
                            <form action="{{ route('cart.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                <select name="quantity" onchange="this.form.submit()"
                                        class="border border-stone-200 rounded-lg text-sm text-[#333333] px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-[#D4C7B0] bg-white font-heading">
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ $item->quantity == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </form>
                            <p class="font-heading font-bold text-sm text-stone-600">R{{ number_format($item->quantity * $item->product->price, 2) }}</p>
                            <form action="{{ route('cart.remove') }}" method="POST">
                                @csrf
                                <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                <button type="submit" class="flex items-center gap-1 text-xs text-stone-400 hover:text-red-500 transition-colors font-medium">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18m-2 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                    Remove
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Summary --}}
            <div class="lg:col-span-1">
                <div class="card p-6 sticky top-24">
                    <h2 class="font-heading font-bold text-base text-[#333333] mb-5 pb-4 border-b border-stone-100">Order Summary</h2>

                    <div class="space-y-3 text-sm mb-5">
                        <div class="flex justify-between text-stone-500">
                            <span>Subtotal ({{ $cartItems->sum('quantity') }} items)</span>
                            <span>R{{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-stone-500">
                            <span>Delivery</span>
                            <span class="text-emerald-600 font-medium">Calculated at checkout</span>
                        </div>
                    </div>

                    <div class="border-t border-stone-100 pt-4 mb-5 flex justify-between font-heading font-black text-base">
                        <span>Subtotal</span>
                        <span class="text-[#333333]">R{{ number_format($subtotal, 2) }}</span>
                    </div>

                    @auth
                        <a href="{{ route('checkout.index') }}" class="btn-primary w-full py-3.5 justify-center text-sm">
                            Proceed to Checkout
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}?redirect={{ urlencode(route('checkout.index')) }}"
                           class="btn-primary w-full py-3.5 justify-center text-sm">
                            Sign In to Checkout
                        </a>
                        <a href="{{ route('register') }}" class="btn-secondary w-full py-3 mt-2 justify-center text-sm">
                            Create Account
                        </a>
                    @endauth

                    <a href="{{ route('products.index') }}"
                       class="flex items-center justify-center gap-1 text-xs text-stone-400 hover:text-[#333333] mt-4 transition-colors font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7-7-7 7 7 7"/></svg>
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>

    @else
        <div class="text-center py-28 bg-white rounded-2xl border border-stone-100 max-w-md mx-auto">
            <div class="w-20 h-20 bg-[#F5F5F5] rounded-full flex items-center justify-center mx-auto mb-5">
                <svg class="w-9 h-9 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0"/>
                </svg>
            </div>
            <h3 class="font-heading font-bold text-xl text-[#333333] mb-2">Your cart is empty</h3>
            <p class="text-stone-400 text-sm mb-8">Add some Eben Supply merch to get started.</p>
            <a href="{{ route('products.index') }}" class="btn-primary text-sm">Shop Now</a>
        </div>
    @endif
</div>
</x-layouts.app>