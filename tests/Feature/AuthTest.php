<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_without_forgot_password_link(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Ingat Saya');
        $response->assertDontSee('Lupa Password?');
        $response->assertDontSee('/auth/forgot-password');
    }

    public function test_forgot_password_routes_are_removed_and_return_404(): void
    {
        $getResponse = $this->get('/auth/forgot-password');
        $getResponse->assertStatus(404);

        $postResponse = $this->post('/auth/forgot-password', [
            'email' => 'admin@sitoko.com',
        ]);
        $postResponse->assertStatus(404);
    }

    public function test_password_reset_tokens_table_does_not_exist(): void
    {
        $this->assertFalse(Schema::hasTable('password_reset_tokens'));
    }

    public function test_user_can_login_successfully(): void
    {
        $user = User::create([
            'name'      => 'Test User',
            'email'     => 'test@sitoko.com',
            'password'  => Hash::make('password123'),
            'role'      => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email'    => 'test@sitoko.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
}
