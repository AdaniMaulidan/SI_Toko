<?php

namespace Tests\Feature;

use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'      => 'Admin Toko',
            'role'      => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'name'      => 'Kasir Toko',
            'role'      => User::ROLE_CASHIER,
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'name'      => 'Staff Gudang',
            'role'      => User::ROLE_STAFF,
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('settings.index'));
        $response->assertRedirect(route('login'));

        $putResponse = $this->put(route('settings.update'), [
            'store_name' => 'Toko Ilegal',
        ]);
        $putResponse->assertRedirect(route('login'));
    }

    public function test_cashier_and_staff_cannot_access_settings(): void
    {
        $this->actingAs($this->cashier)
            ->get(route('settings.index'))
            ->assertForbidden();

        $this->actingAs($this->staff)
            ->get(route('settings.index'))
            ->assertForbidden();

        $this->actingAs($this->cashier)
            ->put(route('settings.update'), ['store_name' => 'Coba Ubah'])
            ->assertForbidden();
    }

    public function test_admin_can_view_settings_page(): void
    {
        StoreSetting::create([
            'store_name'      => 'Toko Serba Ada',
            'address'         => 'Jl. Sudirman No. 10',
            'phone'           => '08123456789',
            'email'           => 'admin@serbaada.com',
            'header_receipt'  => 'Cabang Utama',
            'footer_receipt'  => 'Terima kasih atas kunjungannya',
            'currency_symbol' => 'Rp',
            'tax_percentage'  => 11,
        ]);

        $response = $this->actingAs($this->admin)->get(route('settings.index'));

        $response->assertOk();
        $response->assertViewIs('settings.index');
        $response->assertViewHas('setting');
        $response->assertSee('Toko Serba Ada');
        $response->assertSee('Jl. Sudirman No. 10');
        $response->assertSee('08123456789');
    }

    public function test_admin_can_update_store_profile_and_receipt(): void
    {
        $response = $this->actingAs($this->admin)->put(route('settings.update'), [
            'store_name'      => 'Toko Maju Berkah',
            'phone'           => '081987654321',
            'address'         => 'Jl. Pahlawan No. 99, Surabaya',
            'email'           => 'owner@majuberkah.com',
            'header_receipt'  => 'Pusat Grosir & Eceran Termurah',
            'footer_receipt'  => 'Barang yang dibeli tidak dapat ditukar kecuali rusak.',
            'currency_symbol' => 'Rp',
            'tax_percentage'  => 0,
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('store_settings', [
            'store_name'      => 'Toko Maju Berkah',
            'phone'           => '081987654321',
            'address'         => 'Jl. Pahlawan No. 99, Surabaya',
            'email'           => 'owner@majuberkah.com',
            'header_receipt'  => 'Pusat Grosir & Eceran Termurah',
            'footer_receipt'  => 'Barang yang dibeli tidak dapat ditukar kecuali rusak.',
            'currency_symbol' => 'Rp',
            'tax_percentage'  => 0,
        ]);
    }

    public function test_admin_can_upload_and_replace_store_logo(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo_toko.png', 300, 300);

        $response = $this->actingAs($this->admin)->put(route('settings.update'), [
            'store_name' => 'Toko Logo Keren',
            'logo'       => $file,
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        $setting = StoreSetting::getSettings();
        $this->assertNotNull($setting->logo);
        Storage::disk('public')->assertExists($setting->logo);

        // Upload logo baru untuk memastikan logo lama terhapus dan digantikan
        $oldLogoPath = $setting->logo;
        $newFile = UploadedFile::fake()->image('logo_baru.jpg', 400, 400);

        $updateResponse = $this->actingAs($this->admin)->put(route('settings.update'), [
            'store_name' => 'Toko Logo Keren',
            'logo'       => $newFile,
        ]);

        $updateResponse->assertRedirect(route('settings.index'));
        $setting->refresh();

        Storage::disk('public')->assertMissing($oldLogoPath);
        Storage::disk('public')->assertExists($setting->logo);
    }

    public function test_admin_can_remove_logo(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo_to_remove.png', 200, 200);
        $path = $file->store('store', 'public');

        $setting = StoreSetting::getSettings();
        $setting->update(['logo' => $path]);

        Storage::disk('public')->assertExists($path);

        $deleteResponse = $this->actingAs($this->admin)->delete(route('settings.remove-logo'));

        $deleteResponse->assertRedirect(route('settings.index'));
        $deleteResponse->assertSessionHas('success');

        $setting->refresh();
        $this->assertNull($setting->logo);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_store_name_is_required(): void
    {
        $response = $this->actingAs($this->admin)->put(route('settings.update'), [
            'store_name' => '',
        ]);

        $response->assertSessionHasErrors(['store_name']);
    }

    public function test_logo_must_be_valid_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin)->put(route('settings.update'), [
            'store_name' => 'Toko Validasi Logo',
            'logo'       => $file,
        ]);

        $response->assertSessionHasErrors(['logo']);
    }
}
