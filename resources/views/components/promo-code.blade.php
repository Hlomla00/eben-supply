{{-- Apply / remove the order's promo code. Used in the cart and at checkout. --}}
@props(['promo' => null, 'error' => null])

@php
    $appliedCode = session(\App\Models\PromoCode::SESSION_KEY);
    $message     = session('promo_error') ?? $error;
@endphp

<div class="border-t border-stone-100 pt-4 mt-1">
    @if($appliedCode)
        <div class="flex items-center justify-between gap-3 text-sm">
            <div class="min-w-0">
                <p class="font-heading font-bold {{ $promo ? 'text-emerald-700' : 'text-stone-400 line-through' }} truncate">{{ $appliedCode }}</p>
                @if($promo)<p class="text-xs text-emerald-600">{{ $promo->label }} applied</p>@endif
            </div>
            <form action="{{ route('promo.remove') }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-stone-400 hover:text-red-500 font-medium transition-colors">Remove</button>
            </form>
        </div>
    @else
        <form action="{{ route('promo.apply') }}" method="POST" class="flex gap-2">
            @csrf
            <label for="promo-code-input" class="sr-only">Promo code</label>
            <input id="promo-code-input" type="text" name="code" value="{{ old('code') }}" placeholder="Promo code" autocomplete="off"
                   class="form-input py-2 text-sm uppercase {{ $message ? 'ring-2 ring-red-300' : '' }}">
            <button type="submit" class="btn-secondary text-xs px-4 py-2 flex-shrink-0">Apply</button>
        </form>
    @endif

    @if($message)
        <p class="text-red-500 text-xs mt-2" role="alert">{{ $message }}</p>
    @endif
    @error('code')<p class="text-red-500 text-xs mt-2" role="alert">{{ $message }}</p>@enderror

    <p class="text-[11px] text-stone-400 mt-2 leading-snug">{{ \App\Models\PromoCode::ONE_USE_RULE }}</p>
</div>
