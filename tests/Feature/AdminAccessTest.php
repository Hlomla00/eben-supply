<?php
// Hlomla Magopeni 218070349 — Eben Supply | Group KN3
// The admin area is closed to logged-in customers (EnsureUserIsAdmin, PR #15)
// and open to admins.

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private function order(?User $user = null): Order
    {
        return Order::create([
            'user_id' => $user?->id, 'status' => 'pending', 'fulfillment' => 'pickup',
            'total_amount' => 150, 'contact_name' => 'A', 'contact_phone' => '1', 'contact_email' => 'a@test.co.za',
        ]);
    }

    public function test_customer_cannot_access_admin_routes(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order    = $this->order($customer);

        $this->actingAs($customer);
        $this->get('/admin/admin-dashboard')->assertForbidden();
        $this->get('/admin/orders')->assertForbidden();
        $this->get('/admin/products')->assertForbidden();
        $this->get('/admin/inventory')->assertForbidden();
        $this->put("/admin/orders/{$order->id}/status", ['status' => 'collected'])->assertForbidden();

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_admin_can_update_order_status(): void
    {
        $order = $this->order();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put("/admin/orders/{$order->id}/status", ['status' => 'ready'])
            ->assertSessionHas('success');

        $this->assertSame('ready', $order->fresh()->status);
    }
}
