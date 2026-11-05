<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders_all_three_forms(): void
    {
        $user = User::factory()->create(['phone' => '082 111 2222']);

        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertSee('Profile Information')
            ->assertSee('name="email"', false)
            ->assertSee('value="082 111 2222"', false)
            ->assertSee('Update Password')
            ->assertSee('Delete Account');
    }

    public function test_phone_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '021 555 0000',
        ])->assertRedirect('/profile');

        $this->assertSame('021 555 0000', $user->fresh()->phone);
    }

    public function test_password_reset_pages_use_the_store_layout(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        foreach (['/forgot-password', '/reset-password/' . $token . '?email=' . urlencode($user->email)] as $url) {
            $this->get($url)->assertOk()->assertSee('Woodstock, Cape Town')->assertSee('form-input', false);
        }
    }
}
