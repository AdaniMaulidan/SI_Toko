<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected User $cashier;
    protected Product $productA;
    protected Product $productB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'role'      => User::ROLE_STAFF,
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'role'      => User::ROLE_CASHIER,
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Minuman Ringan',
            'slug' => 'minuman-ringan',
        ]);

        $unit = Unit::create([
            'name'   => 'Botol',
            'symbol' => 'Btl',
        ]);

        $this->productA = Product::create([
            'category_id'   => $category->id,
            'unit_id'       => $unit->id,
            'barcode'       => '8991234000001',
            'name'          => 'Teh Botol Sosro',
            'cost_price'    => 3000,
            'selling_price' => 4500,
            'stock'         => 50,
            'min_stock'     => 10,
            'is_active'     => true,
        ]);

        $this->productB = Product::create([
            'category_id'   => $category->id,
            'unit_id'       => $unit->id,
            'barcode'       => '8991234000002',
            'name'          => 'Kopi Susu UHT',
            'cost_price'    => 5000,
            'selling_price' => 7000,
            'stock'         => 20,
            'min_stock'     => 5,
            'is_active'     => true,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('stock.adjustment'));
        $response->assertRedirect(route('login'));
    }

    public function test_cashier_cannot_access_stock_adjustment(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('stock.adjustment'));
        $response->assertForbidden();

        $responseIndex = $this->actingAs($this->cashier)->get(route('stock.index'));
        $responseIndex->assertForbidden();
    }

    public function test_admin_and_staff_can_view_stock_index_and_adjustment_pages(): void
    {
        // Stock Index
        $responseIndex = $this->actingAs($this->admin)->get(route('stock.index'));
        $responseIndex->assertOk();
        $responseIndex->assertViewIs('stock.index');
        $responseIndex->assertSee('Teh Botol Sosro');
        $responseIndex->assertSee('Stok Aman');

        // Stock Adjustment Form
        $responseAdj = $this->actingAs($this->staff)->get(route('stock.adjustment'));
        $responseAdj->assertOk();
        $responseAdj->assertViewIs('stock.adjustment');
        $responseAdj->assertSee('Pencocokan Stok Fisik vs Stok Sistem');
    }

    public function test_validation_fails_when_storing_empty_adjustment(): void
    {
        $response = $this->actingAs($this->admin)->post(route('stock.adjustment.store'), []);

        $response->assertSessionHasErrors(['code', 'date', 'type', 'reason_category', 'items']);
    }

    public function test_successful_stock_adjustment_synchronizes_product_stock_and_records_history(): void
    {
        // Stok sistem awal Product A = 50, opname fisik aktual = 42 (selisih -8 karena kemasan rusak/bocor)
        $actualStock = 42;
        $expectedDiff = -8;

        $payload = [
            'code'            => 'ADJ-20261014-0001',
            'date'            => '2026-10-14',
            'type'            => 'out',
            'reason_category' => 'Barang Rusak (Kemasan Robek / Bocor / Cacat)',
            'notes'           => '8 botol pecah saat bongkar muat gudang',
            'items'           => [
                [
                    'product_id'   => $this->productA->id,
                    'actual_stock' => $actualStock,
                ]
            ],
        ];

        $response = $this->actingAs($this->staff)->post(route('stock.adjustment.store'), $payload);

        $response->assertRedirect(route('stock.adjustment'));
        $response->assertSessionHas('success');

        // 1. Verifikasi Header StockAdjustment tersimpan di database
        $this->assertDatabaseHas('stock_adjustments', [
            'code'    => 'ADJ-20261014-0001',
            'user_id' => $this->staff->id,
            'type'    => 'out',
        ]);

        $adjustment = StockAdjustment::where('code', 'ADJ-20261014-0001')->first();

        // 2. Verifikasi Item Penyesuaian tersimpan dengan previous_stock, actual_stock, dan difference
        $this->assertDatabaseHas('stock_adjustment_items', [
            'stock_adjustment_id' => $adjustment->id,
            'product_id'          => $this->productA->id,
            'previous_stock'      => 50,
            'actual_stock'        => 42,
            'difference'          => $expectedDiff,
        ]);

        // 3. Verifikasi Sinkronisasi Otomatis Stok Master Data: Product A stock sekarang 42!
        $this->productA->refresh();
        $this->assertEquals(42, $this->productA->stock);
    }

    public function test_multi_item_stock_opname_adjusts_multiple_products_atomically(): void
    {
        // Product A: 50 -> 55 (+5 kelebihan)
        // Product B: 20 -> 18 (-2 hilang)
        $payload = [
            'code'            => 'ADJ-20261014-0002',
            'date'            => '2026-10-14',
            'type'            => 'opname',
            'reason_category' => 'Koreksi Rutin / Stock Opname Berkala',
            'notes'           => 'Opname stok tengah bulan',
            'items'           => [
                [
                    'product_id'   => $this->productA->id,
                    'actual_stock' => 55,
                ],
                [
                    'product_id'   => $this->productB->id,
                    'actual_stock' => 18,
                ]
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('stock.adjustment.store'), $payload);

        $response->assertRedirect(route('stock.adjustment'));
        $response->assertSessionHas('success');

        // Verifikasi Sinkronisasi Kedua Produk
        $this->productA->refresh();
        $this->productB->refresh();

        $this->assertEquals(55, $this->productA->stock);
        $this->assertEquals(18, $this->productB->stock);

        $this->assertDatabaseHas('stock_adjustment_items', [
            'product_id'     => $this->productA->id,
            'previous_stock' => 50,
            'actual_stock'   => 55,
            'difference'     => 5,
        ]);

        $this->assertDatabaseHas('stock_adjustment_items', [
            'product_id'     => $this->productB->id,
            'previous_stock' => 20,
            'actual_stock'   => 18,
            'difference'     => -2,
        ]);
    }
}
