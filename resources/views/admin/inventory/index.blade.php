<x-layouts.admin title="Inventory">
{{-- Hillay Itlhabanyeng 230777465 | Group KN3 --}}
<form action="{{ route('admin.inventory.update') }}" method="POST">
    @csrf

    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-stone-400 font-medium">Inline stock editor — update quantities and save all at once.</p>
        <button type="submit" class="btn-primary text-xs px-6 py-2.5">Save All Changes</button>
    </div>

    <div class="space-y-4">
        @foreach($products as $product)
            @php
                $isLow   = $product->stock_quantity < 5;
                $catLabels = ['tshirt' => 'T-Shirt', 'cap' => 'Cap', 'tote_bag' => 'Tote Bag'];
            @endphp
            <div class="bg-white rounded-xl border {{ $isLow ? 'border-amber-200' : 'border-stone-100' }} shadow-soft p-5">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-[#F5F5F5] flex-shrink-0">
                        <img src="{{ asset($product->image_path ?: 'images/products/placeholder.jpg') }}"
                             alt="" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-heading font-semibold text-[#333333] text-sm">{{ $product->name }}</h3>
                            @if($isLow && $product->stock_quantity === 0)
                                <span class="text-[10px] font-heading font-bold bg-red-100 text-red-500 px-2 py-0.5 rounded-full uppercase tracking-wide">Out of Stock</span>
                            @elseif($isLow)
                                <span class="text-[10px] font-heading font-bold bg-amber-100 text-amber-600 px-2 py-0.5 rounded-full uppercase tracking-wide">Low Stock</span>
                            @endif
                        </div>
                        <p class="text-xs text-stone-400 mt-0.5">{{ $catLabels[$product->category] ?? $product->category }} · R{{ number_format($product->price, 2) }}</p>
                    </div>

                    {{-- Total stock (no sizes) --}}
                    @if($product->sizes->isEmpty())
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <label class="text-xs font-heading font-bold text-stone-400 uppercase tracking-wider">Stock</label>
                            <input type="number" name="stock[{{ $product->id }}]"
                                   value="{{ $product->stock_quantity }}" min="0"
                                   class="w-20 form-input text-center px-2 py-2 text-sm font-heading font-bold {{ $isLow ? 'ring-2 ring-amber-200' : '' }}">
                        </div>
                    @else
                        <div class="text-sm text-stone-400 flex-shrink-0">
                            Total: <span class="font-heading font-bold text-[#333333]">{{ $product->stock_quantity }}</span>
                        </div>
                    @endif
                </div>

                {{-- Size breakdown --}}
                @if($product->sizes->isNotEmpty())
                    <div class="grid grid-cols-5 gap-3 border-t border-stone-100 pt-4">
                        @foreach($product->sizes as $size)
                            @php $sizeLow = $size->stock_quantity < 5; @endphp
                            <div class="text-center">
                                <label class="block text-xs font-heading font-bold text-stone-400 uppercase tracking-wider mb-1.5">{{ $size->size }}</label>
                                <input type="number" name="sizes[{{ $size->id }}]"
                                       value="{{ $size->stock_quantity }}" min="0"
                                       class="form-input text-center px-2 py-2 text-sm font-heading font-bold {{ $sizeLow ? 'ring-2 ring-amber-200' : '' }}">
                                @if($sizeLow)
                                    <p class="text-[10px] text-amber-500 font-semibold mt-1">Low</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-6 flex justify-end">
        <button type="submit" class="btn-primary text-sm px-8 py-3">Save All Changes</button>
    </div>
</form>
</x-layouts.admin>