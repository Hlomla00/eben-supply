{{-- PRT362S — Eben Supply | Group KN3 --}}
{{-- DRAFT: the store owner must confirm delivery areas and times before launch. --}}
<x-info-page title="Delivery & Pickup" updated="6 November 2026">
    <section>
        <h2>Store pickup: free</h2>
        <p>
            Choose <strong>Pickup</strong> at checkout and collect your order from our store in
            {{ config('store.address') }}. You'll get an email when your order is ready. Please bring your order
            reference (it starts with <strong>ES-</strong>).
        </p>
    </section>

    <section>
        <h2>Delivery: R{{ number_format(\App\Models\Order::DELIVERY_FEE, 2) }}</h2>
        <p>
            Choose <strong>Delivery</strong> at checkout and enter your address. A flat delivery fee of
            R{{ number_format(\App\Models\Order::DELIVERY_FEE, 2) }} is added to your order. Promo codes apply to
            the items only, not to delivery.
        </p>
    </section>

    <section>
        <h2>Following your order</h2>
        <p>
            Every order moves through these steps: <strong>Paid</strong>, <strong>Confirmed</strong>,
            <strong>Ready</strong>, then <strong>Collected</strong> (or <strong>Delivered</strong>). You can follow
            it under My Orders, and we email you each time the status changes.
        </p>
    </section>
</x-info-page>
