<x-layouts.app title="Checkout">
{{-- Matuma Malapile 222904267 | Group KN3 --}}
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="mb-8">
        <p class="section-label">Secure Checkout</p>
        <h1 class="section-title">Your Details</h1>
        {{-- Progress --}}
        <div class="flex items-center gap-3 mt-5 text-xs font-heading">
            <div class="flex items-center gap-2 text-[#333333] font-semibold">
                <span class="w-6 h-6 rounded-full bg-[#333333] text-white flex items-center justify-center font-bold">1</span>
                <span>Details</span>
            </div>
            <div class="h-px w-8 bg-stone-200"></div>
            <div class="flex items-center gap-2 text-stone-400">
                <span class="w-6 h-6 rounded-full bg-stone-100 text-stone-400 flex items-center justify-center font-bold">2</span>
                <span>Payment</span>
            </div>
            <div class="h-px w-8 bg-stone-200"></div>
            <div class="flex items-center gap-2 text-stone-400">
                <span class="w-6 h-6 rounded-full bg-stone-100 text-stone-400 flex items-center justify-center font-bold">3</span>
                <span>Confirmation</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Form --}}
        <form action="{{ route('checkout.process') }}" method="POST" class="lg:col-span-2 space-y-5">
            @csrf

            {{-- Contact --}}
            <div class="card p-6">
                <h2 class="font-heading font-bold text-sm text-[#A3A380] uppercase tracking-wider mb-5">Contact Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="contact_name" value="{{ old('contact_name', $user->name ?? '') }}"
                               class="form-input @error('contact_name') ring-2 ring-red-300 border-red-300 @enderror"
                               placeholder="Your full name">
                        @error('contact_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="contact_phone" value="{{ old('contact_phone', $user->phone ?? '') }}"
                               class="form-input @error('contact_phone') ring-2 ring-red-300 border-red-300 @enderror"
                               placeholder="082 000 0000">
                        @error('contact_phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Email Address</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $user->email ?? '') }}"
                               class="form-input @error('contact_email') ring-2 ring-red-300 border-red-300 @enderror"
                               placeholder="you@email.com">
                        @error('contact_email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Fulfillment --}}
            <div class="card p-6">
                <h2 class="font-heading font-bold text-sm text-[#A3A380] uppercase tracking-wider mb-5">Fulfillment Method</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="fulfillment" value="pickup" class="sr-only peer"
                               {{ old('fulfillment', 'pickup') === 'pickup' ? 'checked' : '' }}>
                        <div class="border-2 rounded-xl p-4 peer-checked:border-[#333333] peer-checked:bg-[#F5F5F5] border-stone-200 transition-all">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-9 h-9 rounded-full bg-[#D4C7B0]/30 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-[#A3A380]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                                </div>
                                <div>
                                    <p class="font-heading font-bold text-sm text-[#333333]">Store Pickup</p>
                                    <p class="text-xs text-stone-400">Woodstock, Cape Town</p>
                                </div>
                            </div>
                            <p class="font-heading font-black text-emerald-600 text-sm">FREE</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="fulfillment" value="delivery" class="sr-only peer"
                               {{ old('fulfillment') === 'delivery' ? 'checked' : '' }}>
                        <div class="border-2 rounded-xl p-4 peer-checked:border-[#333333] peer-checked:bg-[#F5F5F5] border-stone-200 transition-all">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-9 h-9 rounded-full bg-[#D4C7B0]/30 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-[#A3A380]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-7-7m7 7-7 7"/></svg>
                                </div>
                                <div>
                                    <p class="font-heading font-bold text-sm text-[#333333]">Nationwide Delivery</p>
                                    <p class="text-xs text-stone-400">3–5 business days</p>
                                </div>
                            </div>
                            <p class="font-heading font-black text-[#333333] text-sm">R60.00</p>
                        </div>
                    </label>
                </div>

                <div id="delivery-address" class="{{ old('fulfillment') === 'delivery' ? '' : 'hidden' }} mt-4">
                    <label class="form-label">Delivery Address</label>
                    <textarea name="delivery_address" rows="3"
                              class="form-input @error('delivery_address') ring-2 ring-red-300 border-red-300 @enderror"
                              placeholder="Street, suburb, city, postal code">{{ old('delivery_address') }}</textarea>
                    @error('delivery_address')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <button type="submit" class="btn-primary w-full py-4 text-sm justify-center">
                Continue to Payment
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/></svg>
            </button>
        </form>

        {{-- Summary sidebar --}}
        <div>
            <div class="card p-5 sticky top-24">
                <h2 class="font-heading font-bold text-sm text-[#333333] mb-4 pb-3 border-b border-stone-100">
                    Order ({{ $cartItems->count() }} items)
                </h2>
                <div class="space-y-3 mb-4">
                    @foreach($cartItems as $item)
                        <div class="flex gap-3 items-center">
                            <div class="w-11 h-11 rounded-lg overflow-hidden bg-[#F5F5F5] flex-shrink-0">
                                <img src="{{ asset($item->product->image_path ?: 'images/products/placeholder.jpg') }}" alt="" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-[#333333] leading-tight truncate">{{ $item->product->name }}</p>
                                @if($item->size)<p class="text-[10px] text-stone-400">{{ $item->size }}</p>@endif
                                <p class="text-[10px] text-stone-400">Qty: {{ $item->quantity }}</p>
                            </div>
                            <p class="text-xs font-heading font-bold text-[#333333] flex-shrink-0">R{{ number_format($item->quantity * $item->product->price, 2) }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="border-t border-stone-100 pt-3 space-y-2 text-xs">
                    <div class="flex justify-between text-stone-500"><span>Subtotal</span><span>R{{ number_format($subtotal, 2) }}</span></div>
                    <div class="flex justify-between text-stone-500"><span>Delivery</span><span id="delivery-fee-display" class="text-emerald-600">Free</span></div>
                    <div class="flex justify-between font-heading font-black text-sm border-t border-stone-100 pt-2 mt-1">
                        <span>Total</span><span id="total-display">R{{ number_format($subtotal, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const radios = document.querySelectorAll('input[name="fulfillment"]');
    const addrDiv = document.getElementById('delivery-address');
    const feeEl   = document.getElementById('delivery-fee-display');
    const totalEl = document.getElementById('total-display');
    const sub     = {{ $subtotal }};

    radios.forEach(r => r.addEventListener('change', () => {
        const isDelivery = r.value === 'delivery' && r.checked;
        addrDiv.classList.toggle('hidden', !isDelivery);
        feeEl.textContent   = isDelivery ? 'R60.00' : 'Free';
        feeEl.className     = isDelivery ? 'font-medium' : 'text-emerald-600';
        totalEl.textContent = 'R' + (isDelivery ? sub + 60 : sub).toFixed(2);
    }));
</script>
@endpush
</x-layouts.app>