{{-- Shared frame for the store information pages (contact, shipping, returns, terms, privacy). --}}
@props(['title', 'label' => 'Help', 'updated' => null])

<x-layouts.app :title="$title">
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <p class="section-label">{{ $label }}</p>
        <h1 class="section-title">{{ $title }}</h1>
        @if($updated)
            <p class="text-xs text-stone-400 mt-2">Last updated {{ $updated }}</p>
        @endif
    </div>

    <div class="card p-6 sm:p-8 text-sm text-stone-600 leading-relaxed space-y-6
                [&_h2]:font-heading [&_h2]:font-bold [&_h2]:text-base [&_h2]:text-[#333333] [&_h2]:mb-2
                [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:space-y-1 [&_a]:text-[#A3A380] [&_a]:underline hover:[&_a]:text-[#333333]">
        {{ $slot }}
    </div>

    <p class="text-xs text-stone-400 mt-6">
        Questions? See our <a href="{{ route('pages.contact') }}" class="underline hover:text-[#333333]">contact page</a>.
    </p>
</div>
</x-layouts.app>
