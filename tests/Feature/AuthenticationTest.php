<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads_with_security_headers(): void
    {
        $this->withoutVite();

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Sign in to your workspace');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_users_can_sign_in_and_view_dashboard(): void
    {
        $this->withoutVite();

        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'SecurePass#2026',
            'is_locked' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'SecurePass#2026',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->get('/')->assertOk()->assertSee('Dashboard');
    }

    public function test_locked_users_cannot_sign_in(): void
    {
        User::factory()->create([
            'email' => 'locked@example.com',
            'password' => 'SecurePass#2026',
            'is_locked' => true,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'locked@example.com',
            'password' => 'SecurePass#2026',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
