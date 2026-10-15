<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'role'      => 'cashier',
            'is_active' => true,
        ]);
    }

    public function test_activity_index_is_accessible_by_admin(): void
    {
        ActivityLog::record('login', 'User berhasil login', null, [], $this->admin);

        $response = $this->actingAs($this->admin)->get(route('activity.index'));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Aktivitas &amp; Audit Trail', false);
        $response->assertSee('User berhasil login');
    }

    public function test_activity_index_is_forbidden_for_cashier(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('activity.index'));

        $response->assertStatus(403);
    }

    public function test_activity_log_record_helper_creates_database_record(): void
    {
        $log = ActivityLog::record('settings', 'Pengaturan toko diperbarui', null, ['key' => 'value'], $this->admin);

        $this->assertDatabaseHas('activity_logs', [
            'id'          => $log->id,
            'user_id'     => $this->admin->id,
            'action'      => 'settings',
            'description' => 'Pengaturan toko diperbarui',
        ]);
    }

    public function test_activity_log_recorded_on_login_and_logout(): void
    {
        // Login
        $response = $this->post(route('login.submit'), [
            'email'    => $this->admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action'  => 'login',
        ]);

        // Logout
        $responseLogout = $this->actingAs($this->admin)->post(route('logout'));
        $responseLogout->assertRedirect(route('login'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action'  => 'logout',
        ]);
    }

    public function test_activity_log_recorded_on_settings_update(): void
    {
        $response = $this->actingAs($this->admin)->put(route('settings.update'), [
            'store_name' => 'Toko Baru Makmur',
            'phone'      => '081299998888',
        ]);

        $response->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action'  => 'settings',
        ]);
    }

    public function test_activity_log_recorded_on_stock_adjustment(): void
    {
        $category = Category::create(['name' => 'Umum', 'slug' => 'umum']);
        $unit = Unit::create(['name' => 'Pcs', 'symbol' => 'Pcs']);

        $product = Product::create([
            'barcode'       => 'ADJ-TEST-01',
            'name'          => 'Kopi ABC',
            'category_id'   => $category->id,
            'unit_id'       => $unit->id,
            'cost_price'    => 1000,
            'selling_price' => 1500,
            'stock'         => 10,
            'min_stock'     => 2,
            'is_active'     => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('stock.adjustment.store'), [
            'code'            => 'ADJ-' . time(),
            'date'            => date('Y-m-d'),
            'type'            => 'opname',
            'reason_category' => 'Stock Opname Rutin',
            'notes'           => 'Stok opname bulanan',
            'items'           => [
                [
                    'product_id'   => $product->id,
                    'actual_stock' => 15,
                ],
            ],
        ]);

        $response->assertRedirect(route('stock.adjustment'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action'  => 'adjustment',
        ]);
    }

    public function test_activity_index_filters_by_action_and_user(): void
    {
        ActivityLog::record('login', 'Admin login ke sistem', null, [], $this->admin);
        ActivityLog::record('delete', 'Barang dihapus oleh admin', null, [], $this->admin);
        ActivityLog::record('login', 'Kasir login ke sistem', null, [], $this->cashier);

        // Filter action delete
        $responseAction = $this->actingAs($this->admin)->get(route('activity.index', ['action' => 'delete']));
        $responseAction->assertStatus(200);
        $responseAction->assertSee('Barang dihapus oleh admin');
        $responseAction->assertDontSee('Admin login ke sistem');

        // Filter user cashier
        $responseUser = $this->actingAs($this->admin)->get(route('activity.index', ['user_id' => $this->cashier->id]));
        $responseUser->assertStatus(200);
        $responseUser->assertSee('Kasir login ke sistem');
        $responseUser->assertDontSee('Barang dihapus oleh admin');
    }
}
