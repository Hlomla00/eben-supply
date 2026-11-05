<?php
// Hlomla Magopeni 218070349 — Eben Supply | Group KN3
// Paid and cancelled order statuses: an admin can mark orders paid, cancelling
// returns stock, and closed orders cannot change again.

namespace Tests\Feature;

use App\Mail\OrderStatusUpdated;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    // A tee with 2 x M sold: M stock 1 (was 3), product total 6 (was 8)
    private function soldOrder(string $status = 'paid', ?User $customer = null): array
    {
        $tee = Product::create(['name' => 'Status Tee', 'category' => 'tshirt', 'price' => 289, 'stock_quantity' => 6]);
        ProductSize::create(['product_id' => $tee->id, 'size' => 'M', 'stock_quantity' => 1]);

        $order = Order::create([
            'user_id' => $customer?->id, 'status' => $status, 'fulfillment' => 'pickup', 'total_amount' => 578,
            'contact_name' => 'Status Customer', 'contact_phone' => '1', 'contact_email' => 'status@test.co.za',
            'payment_reference' => 'ES-STATUS1',
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $tee->id, 'size' => 'M', 'quantity' => 2, 'unit_price' => 289]);

        return [$order, $tee];
    }

    public function test_admin_can_mark_a_pending_order_paid(): void
    {
        $customer = User::factory()->create();
        [$order] = $this->soldOrder('pending', $customer);

        $this->actingAs($customer)->get("/my-orders/{$order->id}")
            ->assertSeeInOrder(['Pending', 'Confirmed', 'Ready', 'Collected']);

        $this->actingAs($this->admin())
            ->put("/admin/orders/{$order->id}/status", ['status' => 'paid'])
            ->assertSessionHas('success');

        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_cancelling_returns_stock_and_emails_customer(): void
    {
        [$order, $tee] = $this->soldOrder();
        Mail::fake();

        $this->actingAs($this->admin())
            ->put("/admin/orders/{$order->id}/status", ['status' => 'cancelled'])
            ->assertSessionHas('success', 'Order cancelled and stock returned.');

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(3, ProductSize::where('product_id', $tee->id)->where('size', 'M')->value('stock_quantity'));
        $this->assertSame(8, $tee->fresh()->stock_quantity);
        Mail::assertSent(OrderStatusUpdated::class, fn ($m) => $m->hasTo('status@test.co.za'));
    }

    public function test_cancelled_order_cannot_be_reopened_or_restocked_twice(): void
    {
        [$order, $tee] = $this->soldOrder();
        $this->actingAs($this->admin());

        $this->put("/admin/orders/{$order->id}/status", ['status' => 'cancelled']);
        $this->put("/admin/orders/{$order->id}/status", ['status' => 'confirmed'])->assertSessionHas('error');

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(8, $tee->fresh()->stock_quantity);
    }

    public function test_collected_order_cannot_be_cancelled(): void
    {
        [$order, $tee] = $this->soldOrder('collected');

        $this->actingAs($this->admin())
            ->put("/admin/orders/{$order->id}/status", ['status' => 'cancelled'])
            ->assertSessionHas('error');

        $this->assertSame('collected', $order->fresh()->status);
        $this->assertSame(6, $tee->fresh()->stock_quantity);
    }

    public function test_admin_cannot_set_an_unknown_or_pending_status(): void
    {
        [$order] = $this->soldOrder();
        $this->actingAs($this->admin());

        $this->put("/admin/orders/{$order->id}/status", ['status' => 'shipped'])->assertSessionHasErrors('status');
        $this->put("/admin/orders/{$order->id}/status", ['status' => 'pending'])->assertSessionHasErrors('status');

        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_customer_sees_cancelled_notice_instead_of_timeline(): void
    {
        $customer = User::factory()->create();
        [$order] = $this->soldOrder('cancelled', $customer);

        $this->actingAs($customer)->get("/my-orders/{$order->id}")
            ->assertOk()
            ->assertSee('Order Cancelled')
            ->assertDontSee('Order Status');
    }

    public function test_customer_timeline_starts_at_paid(): void
    {
        $customer = User::factory()->create();
        [$order] = $this->soldOrder('paid', $customer);

        $this->actingAs($customer)->get("/my-orders/{$order->id}")
            ->assertOk()
            ->assertSeeInOrder(['Paid', 'Confirmed', 'Ready', 'Collected']);
    }

    public function test_admin_order_page_hides_status_form_for_closed_orders(): void
    {
        [$order] = $this->soldOrder('cancelled');

        $this->actingAs($this->admin())->get("/admin/orders/{$order->id}")
            ->assertOk()
            ->assertSee('can no longer be changed')
            ->assertDontSee('name="status"', false);
    }

    public function test_dashboard_counts_paid_orders_as_awaiting_action(): void
    {
        $this->soldOrder('paid');

        $this->actingAs($this->admin())->get('/admin/admin-dashboard')
            ->assertOk()
            ->assertSee('1 awaiting action');
    }
}
