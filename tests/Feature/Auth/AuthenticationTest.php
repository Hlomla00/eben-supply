<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home'));
    }

    public function test_admins_are_sent_to_the_admin_dashboard_after_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admins_ignore_a_storefront_intended_url_after_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get('/checkout')->assertRedirect('/login');

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_admins_return_to_the_admin_page_they_asked_for_after_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get('/admin/orders')->assertRedirect('/login');

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.orders.index'));
    }

    public function test_customers_return_to_the_storefront_page_they_asked_for_after_login(): void
    {
        $user = User::factory()->create();

        $this->get('/checkout')->assertRedirect('/login');

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('checkout.index'));
    }

    public function test_signed_in_admins_opening_the_login_page_go_to_the_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/login')->assertRedirect(route('admin.dashboard'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
