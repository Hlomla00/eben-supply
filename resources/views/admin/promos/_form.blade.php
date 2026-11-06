{{-- Shared by admin/promos/create and edit. --}}
<div class="bg-white rounded-xl border border-stone-100 shadow-soft p-6 space-y-4">
    <h2 class="font-heading font-bold text-sm text-[#A3A380] uppercase tracking-wider mb-1">Promo Code</h2>

    <div>
        <label class="form-label" for="code">Code *</label>
        <input id="code" type="text" name="code" value="{{ old('code', $promo->code) }}" maxlength="40"
               class="form-input uppercase @error('code') ring-2 ring-red-300 @enderror" placeholder="e.g. WELCOME10">
        <p class="text-xs text-stone-400 mt-1">Not case-sensitive for customers. Letters, numbers, dashes and underscores.</p>
        @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="form-label" for="type">Type *</label>
            <select id="type" name="type" class="form-input @error('type') ring-2 ring-red-300 @enderror">
                @foreach(\App\Models\PromoCode::TYPES as $key => $label)
                    <option value="{{ $key }}" {{ old('type', $promo->type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label" for="value">Value * <span class="normal-case font-normal text-stone-400">(% or R)</span></label>
            <input id="value" type="number" name="value" step="0.01" min="0.01" value="{{ old('value', $promo->value) }}"
                   class="form-input @error('value') ring-2 ring-red-300 @enderror">
            @error('value')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="form-label" for="min_spend">Minimum spend (R)</label>
        <input id="min_spend" type="number" name="min_spend" step="0.01" min="0" value="{{ old('min_spend', $promo->min_spend) }}"
               class="form-input sm:w-48 @error('min_spend') ring-2 ring-red-300 @enderror" placeholder="Optional">
        <p class="text-xs text-stone-400 mt-1">Checked against the item subtotal at current (sale) prices, before delivery.</p>
        @error('min_spend')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div class="bg-white rounded-xl border border-stone-100 shadow-soft p-6 space-y-4">
    <h2 class="font-heading font-bold text-sm text-[#A3A380] uppercase tracking-wider mb-1">Dates &amp; Limits</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="form-label" for="starts_at">Starts</label>
            <input id="starts_at" type="datetime-local" name="starts_at" value="{{ old('starts_at', $promo->starts_at?->format('Y-m-d\TH:i')) }}"
                   class="form-input @error('starts_at') ring-2 ring-red-300 @enderror">
            @error('starts_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label" for="ends_at">Ends</label>
            <input id="ends_at" type="datetime-local" name="ends_at" value="{{ old('ends_at', $promo->ends_at?->format('Y-m-d\TH:i')) }}"
                   class="form-input @error('ends_at') ring-2 ring-red-300 @enderror">
            @error('ends_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label" for="usage_limit">Total uses allowed</label>
            <input id="usage_limit" type="number" name="usage_limit" min="1" value="{{ old('usage_limit', $promo->usage_limit) }}"
                   class="form-input @error('usage_limit') ring-2 ring-red-300 @enderror" placeholder="Unlimited">
            @if($promo->exists)<p class="text-xs text-stone-400 mt-1">Used {{ $promo->times_used }} {{ Str::plural('time', $promo->times_used) }} so far.</p>@endif
            @error('usage_limit')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <p class="form-label">Uses per customer</p>
            <p class="text-sm text-stone-500 py-2">Once — for every code. A customer is matched by account and by email address.</p>
        </div>
    </div>

    <label class="flex items-center gap-3 cursor-pointer">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $promo->is_active) ? 'checked' : '' }} class="w-4 h-4 accent-[#333333] rounded">
        <span class="text-sm font-medium text-stone-600">Active</span>
    </label>
</div>
