<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected Category $category;
    protected Unit $unit;

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

        $this->category = Category::create([
            'name' => 'Kategori Utama',
            'slug' => 'kategori-utama',
        ]);

        $this->unit = Unit::create([
            'name'   => 'Pcs',
            'symbol' => 'Pcs',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('dashboard'));
        $response->assertOk();
        $response->assertViewIs('dashboard.index');
        $response->assertSee('Dashboard Toko');
    }

    public function test_dashboard_displays_accurate_realtime_metrics(): void
    {
        // 1. Buat 2 Produk: 1 normal, 1 stok kritis
        $productNormal = Product::create([
            'category_id'   => $this->category->id,
            'unit_id'       => $this->unit->id,
            'barcode'       => '8990001',
            'name'          => 'Sabun Mandi',
            'cost_price'    => 3000,
            'selling_price' => 5000,
            'stock'         => 50,
            'min_stock'     => 10,
            'is_active'     => true,
        ]);

        $productCritical = Product::create([
            'category_id'   => $this->category->id,
            'unit_id'       => $this->unit->id,
            'barcode'       => '8990002',
            'name'          => 'Kopi Sachet',
            'cost_price'    => 1000,
            'selling_price' => 2000,
            'stock'         => 3,
            'min_stock'     => 10,
            'is_active'     => true,
        ]);

        $productEmpty = Product::create([
            'category_id'   => $this->category->id,
            'unit_id'       => $this->unit->id,
            'barcode'       => '8990003',
            'name'          => 'Minyak Goreng',
            'cost_price'    => 14000,
            'selling_price' => 17000,
            'stock'         => 0,
            'min_stock'     => 5,
            'is_active'     => true,
        ]);

        // 2. Buat Transaksi Penjualan Hari Ini
        $sale = Sale::create([
            'invoice_number' => 'INV-20261015-0001',
            'user_id'        => $this->cashier->id,
            'subtotal'       => 20000,
            'discount'       => 2000,
            'total_amount'   => 18000,
            'paid_amount'    => 20000,
            'change_amount'  => 2000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'created_at'     => Carbon::now(),
        ]);

        // Jual 4 Sabun: Gross profit = (5000 - 3000) * 4 = 8000
        SaleItem::create([
            'sale_id'       => $sale->id,
            'product_id'    => $productNormal->id,
            'cost_price'    => 3000,
            'selling_price' => 5000,
            'quantity'      => 4,
            'subtotal'      => 20000,
        ]);

        // Gross Profit Bersih Penjualan = 8000 - 2000 (diskon) = 6000

        // 3. Catat Biaya Pengeluaran Hari Ini = 1000
        Expense::create([
            'code'             => 'EXP-20261015-0001',
            'user_id'          => $this->admin->id,
            'expense_category' => 'Operasional',
            'amount'           => 1000,
            'date'             => Carbon::today(),
            'description'      => 'Beli Kresek',
        ]);

        // Keuntungan Bersih Hari Ini = 6000 - 1000 = 5000

        // 4. Catat Mutasi Kas Masuk
        CashFlow::create([
            'user_id'        => $this->admin->id,
            'type'           => 'in',
            'category'       => 'Penjualan',
            'amount'         => 18000,
            'reference_type' => 'sale',
            'reference_id'   => $sale->id,
            'date'           => Carbon::today(),
            'notes'          => 'Kas Masuk Penjualan',
        ]);

        // 5. Hit dan Cek Response Dashboard
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('salesToday', 18000.0);
        $response->assertViewHas('transactionsToday', 1);
        $response->assertViewHas('netProfitToday', 5000.0);
        $response->assertViewHas('totalProducts', 3);
        $response->assertViewHas('lowStockCount', 1);
        $response->assertViewHas('outOfStockCount', 1);
        $response->assertViewHas('currentRealBalance', 18000.0);

        // Verifikasi konten tertera di HTML
        $response->assertSee('Rp 18.000');
        $response->assertSee('Rp 5.000');
        $response->assertSee('Kopi Sachet');
        $response->assertSee('Minyak Goreng');
        $response->assertSee('INV-20261015-0001');
    }

    public function test_dashboard_provides_proper_chart_data_structure(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertOk();
        $chartData = $response->viewData('chartData');

        $this->assertIsArray($chartData);
        $this->assertArrayHasKey('seven_days', $chartData);
        $this->assertArrayHasKey('thirty_days', $chartData);
        $this->assertArrayHasKey('this_month', $chartData);

        $this->assertCount(7, $chartData['seven_days']['labels']);
        $this->assertCount(7, $chartData['seven_days']['data']);
        $this->assertCount(30, $chartData['thirty_days']['labels']);
        $this->assertCount(30, $chartData['thirty_days']['data']);
    }
}
