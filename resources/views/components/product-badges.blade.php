{{-- Badges over a product image. Used on every product card. --}}
@props(['product'])

<div class="absolute top-3 left-3 flex flex-col items-start gap-1">
    @if($product->isOnSale())
        <span class="bg-red-600 text-white text-[10px] font-heading font-black px-2 py-0.5 rounded-full uppercase tracking-wide">Sale −{{ $product->sale_percent_off }}%</span>
    @endif
    @if($product->is_featured)
        <span class="bg-[#D4C7B0] text-[#333333] text-[10px] font-heading font-black px-2 py-0.5 rounded-full uppercase tracking-wide">Featured</span>
    @endif
</div>
