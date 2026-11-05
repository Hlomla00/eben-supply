<?php
// Hlomla Magopeni 218070349 — Eben Supply | Group KN3

namespace Tests\Feature;

use App\Mail\OrderPlaced;
use App\Mail\OrderStatusUpdated;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderEmailTest extends TestCase
{
    use RefreshDatabase;

    private function tote(int $stock = 3): Product
    {
        return Product::create(['name' => 'Mail Tote', 'category' => 'tote_bag', 'price' => 150.00, 'stock_quantity' => $stock]);
    }

    private function placeOrder(User $user): void
    {
        $this->actingAs($user)->post('/checkout', [
            'contact_name'  => 'Mail Customer',
            'contact_phone' => '082 000 0002',
            'contact_email' => 'mail@test.co.za',
            'fulfillment'   => 'pickup',
        ]);
        $this->post('/checkout/payment');
    }

    private function order(): Order
    {
        $order = Order::create([
            'status' => 'pending', 'fulfillment' => 'pickup', 'total_amount' => 150,
            'contact_name' => 'Mail Customer', 'contact_phone' => '1', 'contact_email' => 'mail@test.co.za',
            'payment_reference' => 'ES-MAIL1',
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $this->tote()->id, 'quantity' => 1, 'unit_price' => 150]);

        return $order;
    }

    public function test_customer_is_emailed_when_order_is_placed(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        CartItem::create(['user_id' => $user->id, 'product_id' => $this->tote()->id, 'quantity' => 2]);

        $this->placeOrder($user);

        $order = Order::firstOrFail();
        Mail::assertSent(OrderPlaced::class, function (OrderPlaced $mail) use ($order) {
            return $mail->hasTo('mail@test.co.za')
                && $mail->order->is($order)
                && $mail->order->items->count() === 1;
        });
        Mail::assertSentCount(1);
    }

    public function test_order_email_lists_items_and_total(): void
    {
        $order = $this->order();

        $html = (new OrderPlaced($order))->render();

        $this->assertStringContainsString('ES-MAIL1', $html);
        $this->assertStringContainsString('Mail Tote', $html);
        $this->assertStringContainsString('R150.00', $html);
        $this->assertStringContainsString('Free (pickup)', $html);
    }

    public function test_no_email_when_checkout_fails_for_stock(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $tote = $this->tote(stock: 1);
        CartItem::create(['user_id' => $user->id, 'product_id' => $tote->id, 'quantity' => 1]);

        $this->actingAs($user)->post('/checkout', [
            'contact_name' => 'Mail Customer', 'contact_phone' => '1',
            'contact_email' => 'mail@test.co.za', 'fulfillment' => 'pickup',
        ]);
        $tote->update(['stock_quantity' => 0]);
        $this->post('/checkout/payment');

        $this->assertDatabaseCount('orders', 0);
        Mail::assertNothingSent();
    }

    public function test_customer_is_emailed_when_admin_changes_status(): void
    {
        $order = $this->order();
        Mail::fake();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put("/admin/orders/{$order->id}/status", ['status' => 'ready']);

        Mail::assertSent(OrderStatusUpdated::class, fn ($mail) => $mail->hasTo('mail@test.co.za'));
        $this->assertStringContainsString('ready for collection', (new OrderStatusUpdated($order->fresh()))->render());
    }

    public function test_no_status_email_when_status_is_unchanged(): void
    {
        $order = $this->order();
        Mail::fake();

        $order->update(['contact_phone' => '082 111 1111']);

        Mail::assertNothingSent();
    }
}
