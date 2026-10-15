<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavbarAndProductDetailTest extends TestCase
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
            'name'      => 'Budi Administrator',
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'name'      => 'Siti Kasir Utama',
            'role'      => 'cashier',
            'is_active' => true,
        ]);

        $this->category = Category::create(['name' => 'Sembako', 'slug' => 'sembako']);
        $this->unit     = Unit::create(['name' => 'Pcs', 'symbol' => 'Pcs']);
    }

    public function test_navbar_displays_dynamic_low_stock_and_out_of_stock_alerts(): void
    {
        // 1 produk habis (stok = 0)
        Product::create([
            'barcode'       => 'TEST-BRC-01',
            'name'          => 'Beras Pulen Habis',
            'category_id'   => $this->category->id,
            'unit_id'       => $this->unit->id,
            'cost_price'    => 50000,
            'selling_price' => 60000,
            'stock'         => 0,
            'min_stock'     => 5,
            'is_active'     => true,
        ]);

        // 1 produk menipis (stok = 3, min = 5)
        Product::create([
            'barcode'       => 'TEST-BRC-02',
            'name'          => 'Gula Pasir Menipis',
            'category_id'   => $this->category->id,
            'unit_id'       => $this->unit->id,
            'cost_price'    => 12000,
            'selling_price' => 15000,
            'stock'         => 3,
            'min_stock'     => 5,
            'is_active'     => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        // Cek bahwa view menerima data composer navbar
        $response->assertSee('Peringatan Stok');
        $response->assertSee('2 Perlu Perhatian');
        $response->assertSee('Stok Habis (0 Item)');
        $response->assertSee('Stok Menipis');
        $response->assertSee('Beras Pulen Habis');
        $response->assertSee('Gula Pasir Menipis');
    }

    public function test_navbar_displays_safe_stock_when_no_items_critical(): void
    {
        // Produk stok aman
        Product::create([
            'barcode'       => 'TEST-BRC-SAFE',
            'name'          => 'Minyak Goreng Aman',
            'category_id'   => $this->category->id,
            'unit_id'       => $this->unit->id,
            'cost_price'    => 15000,
            'selling_price' => 18000,
            'stock'         => 50,
            'min_stock'     => 5,
            'is_active'     => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Stok Aman');
        $response->assertSee('Semua stok produk berada di batas aman.');
    }

    public function test_navbar_search_form_renders_with_products_action(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('products.index'));
        $response->assertSee('Cari produk / barcode... (Enter)');
    }

    public function test_pos_layout_displays_current_cashier_name_and_role(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('kasir.index'));

        $response->assertStatus(200);
        $response->assertSee('Siti Kasir Utama');
        $response->assertSee('Cashier');
    }

    public function test_product_detail_page_displays_actual_sale_and_purchase_and_adjustment_history(): void
    {
        $product = Product::create([
            'barcode'       => 'BRC-HISTORY-01',
            'name'          => 'Kecap Manis Bango 550ml',
            'category_id'   => $this->category->id,
            'unit_id'       => $this->unit->id,
            'cost_price'    => 18000,
            'selling_price' => 23000,
            'stock'         => 35,
            'min_stock'     => 5,
            'is_active'     => true,
        ]);

        // Buat record Penjualan
        $sale = Sale::create([
            'invoice_number' => 'TRX-20261015-0001',
            'user_id'        => $this->cashier->id,
            'subtotal'       => 46000,
            'discount'       => 0,
            'total_amount'   => 46000,
            'paid_amount'    => 50000,
            'change_amount'  => 4000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        SaleItem::create([
            'sale_id'       => $sale->id,
            'product_id'    => $product->id,
            'cost_price'    => 18000,
            'selling_price' => 23000,
            'quantity'      => 2,
            'subtotal'      => 46000,
        ]);

        // Buat record Pembelian (PO)
        $supplier = Supplier::create([
            'name'  => 'PT Unilever Distributor',
            'phone' => '0812345000',
        ]);

        $purchase = Purchase::create([
            'invoice_number' => 'PO-20261015-0001',
            'supplier_id'    => $supplier->id,
            'user_id'        => $this->admin->id,
            'total_amount'   => 360000,
            'paid_amount'    => 360000,
            'payment_status' => 'paid',
            'purchase_date'  => '2026-10-15',
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id'  => $product->id,
            'cost_price'  => 18000,
            'quantity'    => 20,
            'subtotal'    => 360000,
        ]);

        // Buat record Penyesuaian Stok (Opname)
        $adjustment = StockAdjustment::create([
            'code'    => 'ADJ-20261015-0001',
            'user_id' => $this->admin->id,
            'date'    => '2026-10-15',
            'type'    => 'opname',
            'notes'   => 'Pencocokan stok opname mingguan',
        ]);

        StockAdjustmentItem::create([
            'stock_adjustment_id' => $adjustment->id,
            'product_id'          => $product->id,
            'previous_stock' => 36,
            'actual_stock'   => 35,
            'difference'     => -1,
        ]);

        $response = $this->actingAs($this->admin)->get(route('products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Transaksi');
        $response->assertSee('Mutasi Stok');
        // Cek Metrik agregat
        $response->assertSee('Total Terjual');
        $response->assertSee('2 Pcs');
        $response->assertSee('Rp 46.000');
        $response->assertSee('20 Pcs'); // Kulakan

        // Cek Riwayat Penjualan
        $response->assertSee('TRX-20261015-0001');
        $response->assertSee('-2');

        // Cek Riwayat Pembelian
        $response->assertSee('PO-20261015-0001');
        $response->assertSee('+20');
        $response->assertSee('PT Unilever Distributor');

        // Cek Riwayat Opname
        $response->assertSee('ADJ-20261015-0001');
        $response->assertSee('-1');
    }
}
