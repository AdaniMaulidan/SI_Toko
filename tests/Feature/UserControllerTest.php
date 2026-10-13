<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'role'      => User::ROLE_CASHIER,
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'role'      => User::ROLE_STAFF,
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('users.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_users_management(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('users.index'));
        $response->assertForbidden();

        $responseStaff = $this->actingAs($this->staff)->get(route('users.index'));
        $responseStaff->assertForbidden();
    }

    public function test_admin_can_view_users_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.index'));
        $response->assertOk();
        $response->assertViewIs('users.index');
        $response->assertSee($this->admin->name);
        $response->assertSee($this->cashier->name);
    }

    public function test_admin_can_create_new_user(): void
    {
        $userData = [
            'name'      => 'Kasir Baru',
            'email'     => 'kasirbaru@toko.test',
            'password'  => 'password123',
            'role'      => User::ROLE_CASHIER,
            'phone'     => '08123456789',
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->admin)->post(route('users.store'), $userData);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name'      => 'Kasir Baru',
            'email'     => 'kasirbaru@toko.test',
            'role'      => User::ROLE_CASHIER,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $response = $this->actingAs($this->admin)->put(route('users.update', $this->cashier), [
            'name'      => 'Kasir Terupdate',
            'email'     => 'kasir_updated@toko.test',
            'role'      => User::ROLE_STAFF,
            'phone'     => '0899999999',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->cashier->refresh();
        $this->assertEquals('Kasir Terupdate', $this->cashier->name);
        $this->assertEquals(User::ROLE_STAFF, $this->cashier->role);
    }

    public function test_admin_cannot_deactivate_or_demote_self(): void
    {
        $response = $this->actingAs($this->admin)->put(route('users.update', $this->admin), [
            'name'  => $this->admin->name,
            'email' => $this->admin->email,
            'role'  => User::ROLE_CASHIER, // Coba turunkan role sendiri
        ]);

        $response->assertSessionHas('error');
        $this->admin->refresh();
        $this->assertEquals(User::ROLE_ADMIN, $this->admin->role);
    }

    public function test_admin_can_update_user_password(): void
    {
        $response = $this->actingAs($this->admin)->put(route('users.update-password', $this->cashier), [
            'new_password'              => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->cashier->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->cashier->password));
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $this->assertTrue($this->cashier->is_active);

        $response = $this->actingAs($this->admin)->patch(route('users.toggle', $this->cashier));
        $response->assertRedirect(route('users.index'));

        $this->cashier->refresh();
        $this->assertFalse($this->cashier->is_active);
    }

    public function test_admin_cannot_toggle_own_status(): void
    {
        $response = $this->actingAs($this->admin)->patch(route('users.toggle', $this->admin));
        $response->assertSessionHas('error');
        $this->admin->refresh();
        $this->assertTrue($this->admin->is_active);
    }

    public function test_admin_can_delete_another_user(): void
    {
        $targetUser = User::factory()->create([
            'role'      => User::ROLE_CASHIER,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $targetUser));
        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $this->admin));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }
}
