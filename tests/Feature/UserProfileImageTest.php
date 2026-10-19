<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProfileImageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->admin = User::create([
            'name'      => 'Admin Testing',
            'email'     => 'admin@sitoko.com',
            'password'  => Hash::make('password123'),
            'role'      => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->cashier = User::create([
            'name'      => 'Kasir Testing',
            'email'     => 'kasir@sitoko.com',
            'password'  => Hash::make('password123'),
            'role'      => User::ROLE_CASHIER,
            'is_active' => true,
        ]);
    }

    public function test_users_table_has_image_column(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'image'));
    }

    public function test_user_image_url_accessor_returns_default_when_no_image(): void
    {
        $this->assertNull($this->admin->image);
        $this->assertStringContainsString('assets/img/avatars/1.png', $this->admin->image_url);
    }

    public function test_user_image_url_accessor_returns_storage_url_when_image_exists(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        $path = $file->store('users', 'public');

        $this->admin->update(['image' => $path]);

        $this->assertTrue($this->admin->hasCustomImage());
        $this->assertStringContainsString('storage/' . $path, $this->admin->image_url);
    }

    public function test_admin_can_create_user_with_profile_image(): void
    {
        $file = UploadedFile::fake()->image('staff_photo.jpg', 200, 200);

        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name'      => 'Staf Baru',
            'email'     => 'stafbaru@sitoko.com',
            'password'  => 'secret123',
            'role'      => User::ROLE_STAFF,
            'phone'     => '0812999888',
            'image'     => $file,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $newUser = User::where('email', 'stafbaru@sitoko.com')->first();
        $this->assertNotNull($newUser);
        $this->assertNotNull($newUser->image);
        Storage::disk('public')->assertExists($newUser->image);
    }

    public function test_admin_can_update_user_and_replace_profile_image(): void
    {
        $oldFile = UploadedFile::fake()->image('old_photo.jpg');
        $oldPath = $oldFile->store('users', 'public');
        $this->cashier->update(['image' => $oldPath]);

        Storage::disk('public')->assertExists($oldPath);

        $newFile = UploadedFile::fake()->image('new_photo.jpg');

        $response = $this->actingAs($this->admin)->put(route('users.update', $this->cashier), [
            'name'      => 'Kasir Diperbarui',
            'email'     => $this->cashier->email,
            'role'      => User::ROLE_CASHIER,
            'image'     => $newFile,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->cashier->refresh();

        $this->assertNotEquals($oldPath, $this->cashier->image);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($this->cashier->image);
    }

    public function test_admin_can_remove_user_profile_image(): void
    {
        $file = UploadedFile::fake()->image('test_remove.jpg');
        $path = $file->store('users', 'public');
        $this->cashier->update(['image' => $path]);

        $response = $this->actingAs($this->admin)->put(route('users.update', $this->cashier), [
            'name'         => $this->cashier->name,
            'email'        => $this->cashier->email,
            'role'         => User::ROLE_CASHIER,
            'remove_image' => '1',
            'is_active'    => '1',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->cashier->refresh();

        $this->assertNull($this->cashier->image);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_user_can_view_profile_page(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('profile.index'));

        $response->assertStatus(200);
        $response->assertSee('Profil Pengguna');
        $response->assertSee($this->cashier->name);
        $response->assertSee($this->cashier->email);
    }

    public function test_user_can_update_own_profile_and_photo(): void
    {
        $file = UploadedFile::fake()->image('my_profile.png');

        $response = $this->actingAs($this->cashier)->put(route('profile.update'), [
            'name'  => 'Kasir Nama Baru',
            'phone' => '0899123456',
            'image' => $file,
        ]);

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHas('success');

        $this->cashier->refresh();
        $this->assertEquals('Kasir Nama Baru', $this->cashier->name);
        $this->assertEquals('0899123456', $this->cashier->phone);
        $this->assertNotNull($this->cashier->image);
        Storage::disk('public')->assertExists($this->cashier->image);
    }

    public function test_user_can_remove_own_profile_photo(): void
    {
        $file = UploadedFile::fake()->image('delete_me.jpg');
        $path = $file->store('users', 'public');
        $this->cashier->update(['image' => $path]);

        $response = $this->actingAs($this->cashier)->delete(route('profile.remove-image'));

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHas('success');

        $this->cashier->refresh();
        $this->assertNull($this->cashier->image);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_user_can_update_own_password(): void
    {
        $response = $this->actingAs($this->cashier)->put(route('profile.update-password'), [
            'current_password'          => 'password123',
            'new_password'              => 'newpassword321',
            'new_password_confirmation' => 'newpassword321',
        ]);

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHas('success');

        $this->cashier->refresh();
        $this->assertTrue(Hash::check('newpassword321', $this->cashier->password));
    }

    public function test_navbar_displays_user_profile_link_and_avatar(): void
    {
        $file = UploadedFile::fake()->image('nav_avatar.jpg');
        $path = $file->store('users', 'public');
        $this->admin->update(['image' => $path]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('storage/' . $path);
        $response->assertSee('Profil Saya');
    }
}
