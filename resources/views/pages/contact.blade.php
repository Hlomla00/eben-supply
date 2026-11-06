{{-- PRT362S — Eben Supply | Group KN3 --}}
<x-info-page title="Contact Us">
    <section>
        <h2>Visit or collect</h2>
        <p>Eben Supply<br>{{ config('store.address') }}</p>
        <p class="mt-2">Pickup orders are collected here. We'll let you know when your order is ready.</p>
    </section>

    @if(config('store.email') || config('store.phone'))
        <section>
            <h2>Get in touch</h2>
            <ul>
                @if(config('store.email'))
                    <li>Email: <a href="mailto:{{ config('store.email') }}">{{ config('store.email') }}</a></li>
                @endif
                @if(config('store.phone'))
                    <li>Phone: <a href="tel:{{ preg_replace('/[^0-9+]/', '', config('store.phone')) }}">{{ config('store.phone') }}</a></li>
                @endif
            </ul>
        </section>
    @endif

    <section>
        <h2>About an order</h2>
        <p>
            Please have your order reference ready (it starts with <strong>ES-</strong>). You can find it in your
            confirmation email and under
            @auth <a href="{{ route('orders.index') }}">My Orders</a>, @else <a href="{{ route('login') }}">My Orders</a> once you sign in, @endauth
            where you can also follow your order's progress.
        </p>
    </section>

    <section>
        <h2>Helpful pages</h2>
        <ul>
            <li><a href="{{ route('pages.shipping') }}">Delivery and pickup</a></li>
            <li><a href="{{ route('pages.returns') }}">Returns and refunds</a></li>
            <li><a href="{{ route('pages.terms') }}">Terms and conditions</a></li>
            <li><a href="{{ route('pages.privacy') }}">Privacy policy</a></li>
        </ul>
    </section>
</x-info-page>
