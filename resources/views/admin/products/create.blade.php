@extends('layouts.admin')
{{-- Phelo Mguca — 2330707726 | Group KN3 --}}
@section('title', 'Add Product')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.products.index') }}" class="flex items-center gap-1 text-sm text-stone-400 hover:text-[#333333] mb-6 inline-flex transition-colors font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7-7-7 7 7 7"/></svg>
        Back to Products
    </a>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <div class="bg-white rounded-xl border border-stone-100 shadow-soft p-6 space-y-4">
            <h2 class="font-heading font-bold text-sm text-[#A3A380] uppercase tracking-wider mb-1">Product Details</h2>

            <div>
                <label class="form-label">Product Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-input @error('name') ring-2 ring-red-300 @enderror" placeholder="e.g. Eben Supply Graphic Tee – Black">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Category *</label>
                <select name="category" class="form-input @error('category') ring-2 ring-red-300 @enderror">
                    <option value="">Select category…</option>
                    <option value="tshirt"   {{ old('category') === 'tshirt'   ? 'selected' : '' }}>T-Shirt</option>
                    <option value="cap"      {{ old('category') === 'cap'      ? 'selected' : '' }}>Cap</option>
                    <option value="tote_bag" {{ old('category') === 'tote_bag' ? 'selected' : '' }}>Tote Bag</option>
                </select>
                @error('category')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-input" placeholder="Product description…">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Price (ZAR) *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 text-sm font-heading font-bold">R</span>
                        <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0"
                               class="form-input pl-7 @error('price') ring-2 ring-red-300 @enderror" placeholder="150.00">
                    </div>
                    @error('price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="w-4 h-4 accent-[#333333] rounded">
                        <span class="text-sm font-medium text-stone-600">Mark as Featured</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-stone-100 shadow-soft p-6">
            <h2 class="font-heading font-bold text-sm text-[#A3A380] uppercase tracking-wider mb-1">Sizes &amp; Stock</h2>
            <p class="text-xs text-stone-400 mb-4">Enter 0 if not applicable (e.g. caps, tote bags).</p>
            <div class="grid grid-cols-5 gap-3">
                @foreach(['S', 'M', 'L', 'XL', 'XXL'] as $size)
                    <div>
                        <label class="form-label text-center block">{{ $size }}</label>
                        <input type="number" name="sizes[{{ $size }}]" value="{{ old('sizes.' . $size, 0) }}" min="0"
                               class="form-input text-center px-2 font-heading font-bold">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl border border-stone-100 shadow-soft p-6">
            <h2 class="font-heading font-bold text-sm text-[#A3A380] uppercase tracking-wider mb-4">Product Image</h2>
            <input type="file" name="image" accept="image/*"
                   class="block w-full text-sm text-stone-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#F5F5F5] file:text-[#333333] file:font-semibold file:font-heading file:text-xs file:cursor-pointer hover:file:bg-[#D4C7B0]/40 file:transition-colors">
            @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary text-sm px-8 py-3">Create Product</button>
            <a href="{{ route('admin.products.index') }}" class="btn-secondary text-sm px-8 py-3">Cancel</a>
        </div>
    </form>
</div>
@endsection