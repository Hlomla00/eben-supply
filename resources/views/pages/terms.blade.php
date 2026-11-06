{{-- PRT362S — Eben Supply | Group KN3 --}}
{{-- DRAFT: the store owner must review these terms (ideally with legal advice) before launch. --}}
<x-info-page title="Terms & Conditions" label="Legal" updated="6 November 2026">
    <section>
        <h2>1. About these terms</h2>
        <p>
            These terms apply when you use this website or buy from Eben Supply, {{ config('store.address') }}.
            By placing an order you agree to them. Your rights under the Consumer Protection Act and the
            Electronic Communications and Transactions Act are not limited by anything here.
        </p>
    </section>

    <section>
        <h2>2. Prices and payment</h2>
        <ul>
            <li>Prices are in South African rand (R) and include VAT where it applies.</li>
            <li>Sale prices apply only during the sale dates shown on the product.</li>
            <li>You pay the total shown on the payment page. If prices, stock or your cart change before you pay, we show you the new total first.</li>
            <li>Payments are handled by our payment provider. We never see or store your full card details.</li>
        </ul>
    </section>

    <section>
        <h2>3. Orders and stock</h2>
        <p>
            An order is accepted when payment succeeds and you get an order reference. Stock is checked when you
            pay; if an item sells out first, you aren't charged for it. You can have up to
            {{ \App\Models\CartItem::MAX_QUANTITY }} of each item in one order.
        </p>
    </section>

    <section>
        <h2>4. Promo codes</h2>
        <p>
            {{ \App\Models\PromoCode::ONE_USE_RULE }} Codes apply to items, not delivery, can have a minimum spend
            and end date, and have no cash value.
        </p>
    </section>

    <section>
        <h2>5. Delivery, returns and privacy</h2>
        <p>
            See <a href="{{ route('pages.shipping') }}">Delivery &amp; Pickup</a>,
            <a href="{{ route('pages.returns') }}">Returns &amp; Refunds</a> and our
            <a href="{{ route('pages.privacy') }}">Privacy Policy</a>, which form part of these terms.
        </p>
    </section>

    <section>
        <h2>6. Your account</h2>
        <p>
            Keep your password private. You can update your details or delete your account at any time from your
            profile. Past orders are kept for our tax and accounting records.
        </p>
    </section>
</x-info-page>
