{{-- Shared by admin/products/create and edit. Expects $product (unsaved on create). --}}
@php
    $sizeStock = $product->sizes->pluck('stock_quantity', 'size');
    $sizing    = old('sizing', $product->exists ? ($product->sizes->isNotEmpty() ? 'sized' : 'one_size') : 'sized');
    $isKids    = (bool) old('is_kids', $product->is_kids);
@endphp

<div class="space-y-5" x-data="{ sizing: '{{ $sizing }}', isKids: {{ $isKids ? 'true' : 'false' }} }">

<div class="bg-white rounded-xl border border-stone-100 shadow-soft p-6 space-y-4">
    <h2 class="font-heading font-bold text-sm text-[#A3A380] uppercase tracking-wider mb-1">Product Details</h2>

    <div>
        <label class="form-label" for="name">Product Name *</label>
        <input id="name" type="text" name="name" value="{{ old('name', $product->name) }}"
               class="form-input @error('name') ring-2 ring-red-300 @enderror" placeholder="e.g. Eben Supply Graphic Tee – Black">
        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="form-label" for="category">Category *</label>
        <select id="category" name="category" class="form-input @error('category') ring-2 ring-red-300 @enderror">
            <option value="">Select category…</option>
            @foreach(\App\Models\Product::CATEGORIES as $key => [$label])
                <option value="{{ $key }}" {{ old('category', $product->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('category')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="form-label" for="description">Description</label>
        <textarea id="description" name="description" rows="3" class="form-input" placeholder="Product description…">{{ old('description', $product->description) }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="form-label" for="price">Price (ZAR) *</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 text-sm font-heading font-bold">R</span>
                <input id="price" type="number" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0.01"
                       class="form-input pl-7 @error('price') ring-2 ring-red-300 @enderror" placeholder="150.00">
            </div>
            @error('price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-col justify-end gap-2 pb-1">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }} class="w-4 h-4 accent-[#333333] rounded">
                <span class="text-sm font-medium text-stone-600">Featured</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_kids" value="1" x-model="isKids" {{ $isKids ? 'checked' : '' }} class="w-4 h-4 accent-[#333333] rounded">
                <span class="text-sm font-medium text-stone-600">Kids section (uses kids sizes)</span>
            </label>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-stone-100 shadow-soft p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="font-heading font-bold text-sm text-[#A3A380] uppercase tracking-wider">Sale</h2>
        @if($product->sale_status)
            <span class="badge {{ ['active' => 'bg-red-600 text-white', 'scheduled' => 'bg-amber-100 text-amber-800', 'ended' => 'bg-stone-100 text-stone-600'][$product->sale_status] }}">{{ ucfirst($product->sale_status) }}</span>
        @endif
    </div>
    <p class="text-xs text-stone-400">Optional. The sale price must be below the normal price. Leave dates empty to start now and run until removed; sales start and end automatically on their dates.</p>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="form-label" for="sale_price">Sale price (ZAR)</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 text-sm font-heading font-bold">R</span>
                <input id="sale_price" type="number" name="sale_price" step="0.01" min="0.01" value="{{ old('sale_price', $product->sale_price) }}"
                       class="form-input pl-7 @error('sale_price') ring-2 ring-red-300 @enderror">
            </div>
            @error('sale_price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label" for="sale_starts_at">Starts</label>
            <input id="sale_starts_at" type="datetime-local" name="sale_starts_at" value="{{ old('sale_starts_at', $product->sale_starts_at?->format('Y-m-d\TH:i')) }}"
                   class="form-input @error('sale_starts_at') ring-2 ring-red-300 @enderror">
            @error('sale_starts_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label" for="sale_ends_at">Ends</label>
            <input id="sale_ends_at" type="datetime-local" name="sale_ends_at" value="{{ old('sale_ends_at', $product->sale_ends_at?->format('Y-m-d\TH:i')) }}"
                   class="form-input @error('sale_ends_at') ring-2 ring-red-300 @enderror">
            @error('sale_ends_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    @if($product->sale_price !== null)
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="remove_sale" value="1" class="w-4 h-4 accent-[#333333] rounded">
            <span class="text-sm font-medium text-stone-600">Remove this sale</span>
        </label>
    @endif
</div>
<div class="bg-white rounded-xl border border-stone-100 shadow-soft p-6">
    <h2 class="font-heading font-bold text-sm text-[#A3A380] uppercase tracking-wider mb-4">Sizes &amp; Stock</h2>

    <div class="flex flex-wrap gap-4 mb-5">
        <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-stone-600">
            <input type="radio" name="sizing" value="sized" x-model="sizing" class="accent-[#333333]"> Sized (stock per size)
        </label>
        <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-stone-600">
            <input type="radio" name="sizing" value="one_size" x-model="sizing" class="accent-[#333333]"> One size (e.g. caps, tote bags)
        </label>
    </div>
    @error('sizing')<p class="text-red-500 text-xs mb-3">{{ $message }}</p>@enderror

    <div x-show="sizing === 'sized'" @if($sizing !== 'sized') style="display:none" @endif>
        {{-- Only the visible set is submitted: the other set's inputs are disabled. --}}
        @foreach(['adult' => false, 'kids' => true] as $set => $kidsSet)
            <div x-show="isKids === {{ $kidsSet ? 'true' : 'false' }}" @if($isKids !== $kidsSet) style="display:none" @endif>
                <p class="text-xs text-stone-400 mb-3">{{ $kidsSet ? 'Kids sizes (age in years)' : 'Adult sizes' }}</p>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
                    @foreach(\App\Models\ProductSize::sizesFor($kidsSet) as $size)
                        <div>
                            <label class="form-label text-center block" for="size-{{ $size }}">{{ $size }}</label>
                            <input id="size-{{ $size }}" type="number" name="sizes[{{ $size }}]" min="0"
                                   value="{{ old('sizes.' . $size, $sizeStock[$size] ?? 0) }}"
                                   :disabled="isKids !== {{ $kidsSet ? 'true' : 'false' }}" @if($isKids !== $kidsSet) disabled @endif
                                   class="form-input text-center px-2 font-heading font-bold">
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
        @error('sizes')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
        @error('sizes.*')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
    </div>

    <div x-show="sizing === 'one_size'" @if($sizing !== 'one_size') style="display:none" @endif>
        <label class="form-label" for="stock_quantity">Stock</label>
        <input id="stock_quantity" type="number" name="stock_quantity" min="0"
               value="{{ old('stock_quantity', $product->exists && $product->sizes->isEmpty() ? $product->stock_quantity : 0) }}"
               class="form-input w-32 font-heading font-bold @error('stock_quantity') ring-2 ring-red-300 @enderror">
        @error('stock_quantity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div class="bg-white rounded-xl border border-stone-100 shadow-soft p-6">
    <h2 class="font-heading font-bold text-sm text-[#A3A380] uppercase tracking-wider mb-4">Product Image</h2>
    @if($product->image_path)
        <div class="mb-4 flex items-center gap-4">
            <img src="{{ $product->image_url }}" alt="" class="w-24 h-24 rounded-xl object-cover">
            <p class="text-xs text-stone-400">Current image</p>
        </div>
    @endif
    <input type="file" name="image" accept="image/*"
           class="block w-full text-sm text-stone-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#F5F5F5] file:text-[#333333] file:font-semibold file:font-heading file:text-xs file:cursor-pointer hover:file:bg-[#D4C7B0]/40 file:transition-colors">
    @if($product->image_path)<p class="text-xs text-stone-400 mt-1">Leave empty to keep the current image</p>@endif
    @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
</div>
</div>
