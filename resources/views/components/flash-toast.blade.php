{{-- Hlomla Magopeni 218070349 — Eben Supply | Group KN3 --}}
{{-- Flux toasts ignore slot content; they are shown by a "toast-show" event.
     Render one toast container and fire the event for any session flash. --}}
<flux:toast />

@php
    $flash = collect([
        ['message' => session('success'), 'variant' => 'success', 'duration' => 5000],
        ['message' => session('error'),   'variant' => 'danger',  'duration' => 8000],
    ])->filter(fn ($f) => filled($f['message']))->values();
@endphp

@if($flash->isNotEmpty())
    <script>
        document.addEventListener('alpine:initialized', () => {
            setTimeout(() => {
                @json($flash).forEach(f => document.dispatchEvent(new CustomEvent('toast-show', {
                    detail: { slots: { text: f.message }, dataset: { variant: f.variant }, duration: f.duration },
                })));
            }, 50);
        });
    </script>
@endif
