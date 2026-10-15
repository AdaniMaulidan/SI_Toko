<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected User $staff;
    protected Category $category;
    protected Unit $unit;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'      => 'Admin Laporan',
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

        $this->category = Category::create([
            'name' => 'Kategori Makanan',
            'slug' => 'kategori-makanan',
        ]);

        $this->unit = Unit::create([
            'name'       => 'Pcs',
            'short_name' => 'Pcs',
        ]);

        $this->supplier = Supplier::create([
            'name'  => 'PT Pangan Makmur',
            'phone' => '08123456789',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('reports.sales'));
        $response->assertRedirect(route('login'));
    }

    public function test_cashier_and_staff_cannot_access_reports(): void
    {
        $responseCashier = $this->actingAs($this->cashier)->get(route('reports.sales'));
        $responseCashier->assertForbidden();

        $responseStaff = $this->actingAs($this->staff)->get(route('reports.profit'));
        $responseStaff->assertForbidden();
    }

    public function test_admin_can_view_sales_report(): void
    {
        $product = Product::create([
            'category_id'   => $this->category->id,
            'unit_id'       => $this->unit->id,
            'barcode'       => 'BRG001',
            'name'          => 'Biskuit Enak',
            'cost_price'    => 5000,
            'selling_price' => 8000,
            'stock'         => 100,
            'min_stock'     => 10,
            'is_active'     => true,
        ]);

        $sale = Sale::create([
            'invoice_number' => 'INV-20261015-0001',
            'user_id'        => $this->admin->id,
            'subtotal'       => 24000,
            'discount'       => 4000,
            'total_amount'   => 20000,
            'paid_amount'    => 20000,
            'change_amount'  => 0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        SaleItem::create([
            'sale_id'       => $sale->id,
            'product_id'    => $product->id,
            'cost_price'    => 5000,
            'selling_price' => 8000,
            'quantity'      => 3,
            'subtotal'      => 24000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('reports.sales', [
            'start_date' => Carbon::now()->startOfMonth()->toDateString(),
            'end_date'   => Carbon::now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertViewIs('reports.sales');
        $response->assertViewHas('totalTransactions', 1);
        $response->assertViewHas('totalNetSales', 20000.0);
        $response->assertViewHas('totalQtySold', 3);
        $response->assertSee('INV-20261015-0001');
    }

    public function test_admin_can_view_purchases_report(): void
    {
        $purchase = Purchase::create([
            'invoice_number' => 'PO-202610-001',
            'supplier_id'    => $this->supplier->id,
            'user_id'        => $this->admin->id,
            'total_amount'   => 100000,
            'paid_amount'    => 60000,
            'payment_status' => 'partial',
            'purchase_date'  => Carbon::now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('reports.purchases', [
            'month' => Carbon::now()->format('Y-m'),
        ]));

        $response->assertOk();
        $response->assertViewIs('reports.purchases');
        $response->assertViewHas('totalPurchases', 100000.0);
        $response->assertViewHas('totalPaid', 60000.0);
        $response->assertViewHas('totalDebt', 40000.0);
        $response->assertSee('PO-202610-001');
        $response->assertSee('PT Pangan Makmur');
    }

    public function test_admin_can_view_stock_report(): void
    {
        Product::create([
            'category_id'   => $this->category->id,
            'unit_id'       => $this->unit->id,
            'barcode'       => 'BRG002',
            'name'          => 'Gula Pasir 1kg',
            'cost_price'    => 12000,
            'selling_price' => 15000,
            'stock'         => 10,
            'min_stock'     => 15, // Menipis
            'is_active'     => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('reports.stock'));

        $response->assertOk();
        $response->assertViewIs('reports.stock');
        $response->assertViewHas('totalAssetValue', 120000.0); // 10 * 12000
        $response->assertViewHas('lowStockCount', 1);
        $response->assertSee('Gula Pasir 1kg');
    }

    public function test_admin_can_view_products_performance_report(): void
    {
        $product = Product::create([
            'category_id'   => $this->category->id,
            'unit_id'       => $this->unit->id,
            'barcode'       => 'BRG003',
            'name'          => 'Kecap Manis',
            'cost_price'    => 7000,
            'selling_price' => 10000,
            'stock'         => 50,
            'min_stock'     => 5,
            'is_active'     => true,
        ]);

        $sale = Sale::create([
            'invoice_number' => 'INV-20261015-0002',
            'user_id'        => $this->admin->id,
            'subtotal'       => 50000,
            'discount'       => 0,
            'total_amount'   => 50000,
            'paid_amount'    => 50000,
            'change_amount'  => 0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        SaleItem::create([
            'sale_id'       => $sale->id,
            'product_id'    => $product->id,
            'cost_price'    => 7000,
            'selling_price' => 10000,
            'quantity'      => 5,
            'subtotal'      => 50000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('reports.products'));

        $response->assertOk();
        $response->assertViewIs('reports.products');
        $response->assertSee('Kecap Manis');
        $response->assertSee('5 Pcs');
    }

    public function test_admin_can_view_profit_loss_report(): void
    {
        $product = Product::create([
            'category_id'   => $this->category->id,
            'unit_id'       => $this->unit->id,
            'barcode'       => 'BRG004',
            'name'          => 'Susu Kotak',
            'cost_price'    => 4000,
            'selling_price' => 6000,
            'stock'         => 50,
            'min_stock'     => 5,
            'is_active'     => true,
        ]);

        $sale = Sale::create([
            'invoice_number' => 'INV-20261015-0003',
            'user_id'        => $this->admin->id,
            'subtotal'       => 60000,
            'discount'       => 5000,
            'total_amount'   => 55000,
            'paid_amount'    => 55000,
            'change_amount'  => 0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        SaleItem::create([
            'sale_id'       => $sale->id,
            'product_id'    => $product->id,
            'cost_price'    => 4000,
            'selling_price' => 6000,
            'quantity'      => 10,
            'subtotal'      => 60000,
        ]);

        // Net Sales = 55.000
        // HPP = 10 * 4000 = 40.000
        // Gross Profit = 55.000 - 40.000 = 15.000

        Expense::create([
            'code'             => 'EXP-20261015-0002',
            'user_id'          => $this->admin->id,
            'expense_category' => 'Operasional',
            'amount'           => 5000,
            'date'             => Carbon::now()->toDateString(),
            'description'      => 'Biaya Listrik',
        ]);

        // Net Profit = 15.000 - 5.000 = 10.000

        $response = $this->actingAs($this->admin)->get(route('reports.profit', [
            'month' => Carbon::now()->format('Y-m'),
        ]));

        $response->assertOk();
        $response->assertViewIs('reports.profit');
        $response->assertViewHas('netSales', 55000.0);
        $response->assertViewHas('totalHpp', 40000.0);
        $response->assertViewHas('grossProfit', 15000.0);
        $response->assertViewHas('totalExpenses', 5000.0);
        $response->assertViewHas('netProfit', 10000.0);
    }

    public function test_admin_can_view_cash_flow_report(): void
    {
        // Saldo Awal (sebelum bulan berjalan)
        CashFlow::create([
            'user_id'        => $this->admin->id,
            'type'           => 'in',
            'category'       => 'Modal Awal',
            'amount'         => 1000000,
            'reference_type' => 'manual',
            'date'           => Carbon::now()->subMonth()->toDateString(),
            'notes'          => 'Saldo bulan lalu',
        ]);

        // Mutasi Bulan Berjalan
        CashFlow::create([
            'user_id'        => $this->admin->id,
            'type'           => 'in',
            'category'       => 'Penjualan',
            'amount'         => 500000,
            'reference_type' => 'sale',
            'date'           => Carbon::now()->toDateString(),
            'notes'          => 'Kas Masuk Penjualan',
        ]);

        CashFlow::create([
            'user_id'        => $this->admin->id,
            'type'           => 'out',
            'category'       => 'Pengeluaran',
            'amount'         => 200000,
            'reference_type' => 'expense',
            'date'           => Carbon::now()->toDateString(),
            'notes'          => 'Beli Perlengkapan',
        ]);

        $response = $this->actingAs($this->admin)->get(route('reports.cash', [
            'start_date' => Carbon::now()->startOfMonth()->toDateString(),
            'end_date'   => Carbon::now()->endOfMonth()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertViewIs('reports.cash');
        $response->assertViewHas('openingBalance', 1000000.0);
        $response->assertViewHas('totalInflow', 500000.0);
        $response->assertViewHas('totalOutflow', 200000.0);
        $response->assertViewHas('netCashChange', 300000.0);
        $response->assertViewHas('closingBalance', 1300000.0);
    }
}
