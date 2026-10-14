<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CashFlow;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected User $cashier;
    protected Supplier $supplier;
    protected Product $product;

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

        $this->supplier = Supplier::create([
            'name'           => 'PT. Indofood Sukses Makmur',
            'contact_person' => 'Pak Bambang',
            'phone'          => '081298765432',
        ]);

        $category = Category::create([
            'name' => 'Sembako & Makanan',
            'slug' => 'sembako-makanan',
        ]);

        $unit = Unit::create([
            'name'   => 'Karton / Dus',
            'symbol' => 'Dus',
        ]);

        $this->product = Product::create([
            'category_id'   => $category->id,
            'unit_id'       => $unit->id,
            'barcode'       => '8999999111222',
            'name'          => 'Indomie Goreng Spesial',
            'cost_price'    => 85000,
            'selling_price' => 110000,
            'stock'         => 10,
            'min_stock'     => 2,
            'is_active'     => true,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('purchases.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_cashier_cannot_access_purchase_create(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('purchases.create'));
        $response->assertForbidden();
    }

    public function test_admin_and_staff_can_view_purchase_create_page(): void
    {
        // Admin
        $responseAdmin = $this->actingAs($this->admin)->get(route('purchases.create'));
        $responseAdmin->assertOk();
        $responseAdmin->assertViewIs('purchases.create');
        $responseAdmin->assertSee('PT. Indofood Sukses Makmur');
        $responseAdmin->assertSee('Indomie Goreng Spesial');

        // Staff
        $responseStaff = $this->actingAs($this->staff)->get(route('purchases.create'));
        $responseStaff->assertOk();
        $responseStaff->assertViewIs('purchases.create');
    }

    public function test_validation_fails_when_storing_empty_purchase(): void
    {
        $response = $this->actingAs($this->admin)->post(route('purchases.store'), []);

        $response->assertSessionHasErrors(['invoice_number', 'purchase_date', 'payment_status', 'items']);
    }

    public function test_successful_purchase_increments_stock_and_updates_cost_price(): void
    {
        $initialStock = $this->product->stock; // 10
        $oldCostPrice = $this->product->cost_price; // 85000
        $newCostPrice = 90000;
        $purchaseQty = 15;

        $payload = [
            'invoice_number' => 'PO-20261014-0001',
            'supplier_id'    => $this->supplier->id,
            'purchase_date'  => '2026-10-14',
            'payment_status' => 'paid',
            'notes'          => 'Kulakan rutin mingguan',
            'items'          => [
                [
                    'product_id' => $this->product->id,
                    'cost_price' => $newCostPrice,
                    'quantity'   => $purchaseQty,
                ]
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('purchases.store'), $payload);

        $response->assertRedirect(route('purchases.index'));
        $response->assertSessionHas('success');

        // 1. Verifikasi Record Purchase tersimpan di database
        $this->assertDatabaseHas('purchases', [
            'invoice_number' => 'PO-20261014-0001',
            'supplier_id'    => $this->supplier->id,
            'user_id'        => $this->admin->id,
            'total_amount'   => 1350000, // 90.000 * 15
            'paid_amount'    => 1350000,
            'payment_status' => 'paid',
        ]);

        // 2. Verifikasi Record PurchaseItem tersimpan
        $this->assertDatabaseHas('purchase_items', [
            'product_id' => $this->product->id,
            'cost_price' => 90000,
            'quantity'   => 15,
            'subtotal'   => 1350000,
        ]);

        // 3. Verifikasi Stok Produk Bertambah secara Otomatis: 10 + 15 = 25
        $this->product->refresh();
        $this->assertEquals($initialStock + $purchaseQty, $this->product->stock);

        // 4. Verifikasi Harga Modal (Cost Price) Produk Berhasil Diperbarui: 85.000 -> 90.000
        $this->assertEquals($newCostPrice, (float) $this->product->cost_price);

        // 5. Verifikasi Arus Kas Keluar (CashFlow) Tercatat
        $this->assertDatabaseHas('cash_flows', [
            'type'           => 'out',
            'category'       => 'Pembelian',
            'amount'         => 1350000,
            'reference_type' => 'purchase',
        ]);
    }

    public function test_purchase_with_unpaid_status_does_not_record_cash_flow(): void
    {
        $payload = [
            'invoice_number' => 'PO-20261014-0002',
            'supplier_id'    => $this->supplier->id,
            'purchase_date'  => '2026-10-14',
            'payment_status' => 'unpaid',
            'notes'          => 'Tempo 30 hari',
            'items'          => [
                [
                    'product_id' => $this->product->id,
                    'cost_price' => 88000,
                    'quantity'   => 5,
                ]
            ],
        ];

        $response = $this->actingAs($this->staff)->post(route('purchases.store'), $payload);

        $response->assertRedirect(route('purchases.index'));

        // Purchase tersimpan dengan status unpaid
        $this->assertDatabaseHas('purchases', [
            'invoice_number' => 'PO-20261014-0002',
            'payment_status' => 'unpaid',
            'paid_amount'    => 0,
        ]);

        // Stok tetap bertambah: 10 + 5 = 15
        $this->product->refresh();
        $this->assertEquals(15, $this->product->stock);

        // CashFlow tidak bertambah untuk PO ini karena belum ada kas keluar
        $purchase = Purchase::where('invoice_number', 'PO-20261014-0002')->first();
        $this->assertDatabaseMissing('cash_flows', [
            'reference_type' => 'purchase',
            'reference_id'   => $purchase->id,
        ]);
    }

    public function test_admin_and_staff_can_view_purchases_index_and_stats(): void
    {
        // Buat data transaksi pembelian
        $purchase = Purchase::create([
            'invoice_number' => 'PO-20261014-9999',
            'supplier_id'    => $this->supplier->id,
            'user_id'        => $this->admin->id,
            'total_amount'   => 500000,
            'paid_amount'    => 500000,
            'payment_status' => 'paid',
            'purchase_date'  => '2026-10-14',
            'notes'          => 'Catatan pengujian index',
        ]);

        $response = $this->actingAs($this->admin)->get(route('purchases.index'));

        $response->assertOk();
        $response->assertViewIs('purchases.index');
        $response->assertSee('PO-20261014-9999');
        $response->assertSee('PT. Indofood Sukses Makmur');
        $response->assertSee('Total Kulakan');
    }

    public function test_filter_purchases_by_supplier(): void
    {
        $otherSupplier = Supplier::create([
            'name'  => 'CV. Sumber Rejeki',
            'phone' => '089988776655',
        ]);

        $po1 = Purchase::create([
            'invoice_number' => 'PO-SUP-1',
            'supplier_id'    => $this->supplier->id,
            'user_id'        => $this->admin->id,
            'total_amount'   => 100000,
            'paid_amount'    => 100000,
            'payment_status' => 'paid',
            'purchase_date'  => '2026-10-14',
        ]);

        $po2 = Purchase::create([
            'invoice_number' => 'PO-SUP-2',
            'supplier_id'    => $otherSupplier->id,
            'user_id'        => $this->admin->id,
            'total_amount'   => 200000,
            'paid_amount'    => 200000,
            'payment_status' => 'paid',
            'purchase_date'  => '2026-10-14',
        ]);

        $response = $this->actingAs($this->staff)->get(route('purchases.index', [
            'supplier_id' => $this->supplier->id,
        ]));

        $response->assertOk();
        $response->assertSee('PO-SUP-1');
        $response->assertDontSee('PO-SUP-2');
    }

    public function test_filter_purchases_by_payment_status(): void
    {
        Purchase::create([
            'invoice_number' => 'PO-PAID-01',
            'supplier_id'    => $this->supplier->id,
            'user_id'        => $this->admin->id,
            'total_amount'   => 300000,
            'paid_amount'    => 300000,
            'payment_status' => 'paid',
            'purchase_date'  => '2026-10-14',
        ]);

        Purchase::create([
            'invoice_number' => 'PO-UNPAID-01',
            'supplier_id'    => $this->supplier->id,
            'user_id'        => $this->admin->id,
            'total_amount'   => 400000,
            'paid_amount'    => 0,
            'payment_status' => 'unpaid',
            'purchase_date'  => '2026-10-14',
        ]);

        // Filter paid
        $responsePaid = $this->actingAs($this->admin)->get(route('purchases.index', [
            'payment_status' => 'paid',
        ]));
        $responsePaid->assertOk();
        $responsePaid->assertSee('PO-PAID-01');
        $responsePaid->assertDontSee('PO-UNPAID-01');

        // Filter unpaid
        $responseUnpaid = $this->actingAs($this->admin)->get(route('purchases.index', [
            'payment_status' => 'unpaid',
        ]));
        $responseUnpaid->assertOk();
        $responseUnpaid->assertSee('PO-UNPAID-01');
        $responseUnpaid->assertDontSee('PO-PAID-01');
    }

    public function test_show_purchase_detail_page(): void
    {
        $purchase = Purchase::create([
            'invoice_number' => 'PO-SHOW-01',
            'supplier_id'    => $this->supplier->id,
            'user_id'        => $this->admin->id,
            'total_amount'   => 500000,
            'paid_amount'    => 500000,
            'payment_status' => 'paid',
            'purchase_date'  => '2026-10-14',
            'notes'          => 'Catatan show faktur',
        ]);

        $purchase->items()->create([
            'product_id' => $this->product->id,
            'cost_price' => 50000,
            'quantity'   => 10,
            'subtotal'   => 500000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('purchases.show', $purchase->id));

        $response->assertOk();
        $response->assertViewIs('purchases.show');
        $response->assertSee('PO-SHOW-01');
        $response->assertSee('Indomie Goreng Spesial');
        $response->assertSee('PT. Indofood Sukses Makmur');
    }

    public function test_ajax_purchase_detail_endpoint_returns_json(): void
    {
        $purchase = Purchase::create([
            'invoice_number' => 'PO-JSON-01',
            'supplier_id'    => $this->supplier->id,
            'user_id'        => $this->admin->id,
            'total_amount'   => 600000,
            'paid_amount'    => 200000,
            'payment_status' => 'partial',
            'purchase_date'  => '2026-10-14',
            'notes'          => 'Catatan modal ajax',
        ]);

        $purchase->items()->create([
            'product_id' => $this->product->id,
            'cost_price' => 60000,
            'quantity'   => 10,
            'subtotal'   => 600000,
        ]);

        $response = $this->actingAs($this->staff)->get(route('purchases.detail', $purchase->id));

        $response->assertOk();
        $response->assertJson([
            'success'  => true,
            'purchase' => [
                'id'             => $purchase->id,
                'invoice_number' => 'PO-JSON-01',
                'supplier_name'  => 'PT. Indofood Sukses Makmur',
                'payment_status' => 'partial',
                'total_amount'   => 600000,
                'paid_amount'    => 200000,
                'remaining_debt' => 400000,
                'items_count'    => 1,
            ],
        ]);
        $response->assertJsonStructure([
            'purchase' => [
                'items' => [
                    '*' => [
                        'product_name',
                        'barcode',
                        'unit',
                        'cost_price',
                        'quantity',
                        'subtotal',
                    ],
                ],
            ],
        ]);
    }
}
