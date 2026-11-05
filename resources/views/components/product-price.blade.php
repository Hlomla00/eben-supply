{{-- Current price, with the normal price struck through while a sale is live. --}}
@props(['product', 'priceClass' => 'text-base'])

<span {{ $attributes->class('inline-flex items-baseline gap-2 flex-wrap') }}>
    @if($product->isOnSale())
        <span class="font-heading font-black {{ $priceClass }} text-red-600">R{{ number_format($product->current_price, 2) }}</span>
        <s class="text-stone-400 text-xs font-medium" aria-label="Was R{{ number_format($product->price, 2) }}">R{{ number_format($product->price, 2) }}</s>
    @else
        <span class="font-heading font-black {{ $priceClass }} text-[#333333]">R{{ number_format($product->price, 2) }}</span>
    @endif
</span>
