<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected Customer $customer;
    protected Product $product;
    protected Sale $salePaid;
    protected Sale $saleDebt;

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

        $category = Category::create(['name' => 'Makanan Ringan', 'slug' => 'makanan-ringan']);
        $unit = Unit::create(['name' => 'Pieces', 'symbol' => 'Pcs']);

        $this->product = Product::create([
            'category_id' => $category->id,
            'unit_id'     => $unit->id,
            'barcode'     => '8991234567890',
            'name'        => 'Biskuit Cokelat',
            'cost_price'  => 4000,
            'price'       => 5000,
            'stock'       => 100,
            'min_stock'   => 5,
            'is_active'   => true,
        ]);

        $this->customer = Customer::create([
            'name'  => 'Budi Santoso',
            'phone' => '08123456789',
        ]);

        // 1. Paid Transaction
        $this->salePaid = Sale::create([
            'invoice_number' => 'INV-20261014-0001',
            'user_id'        => $this->cashier->id,
            'customer_id'    => $this->customer->id,
            'subtotal'       => 10000,
            'discount'       => 1000,
            'total_amount'   => 9000,
            'paid_amount'    => 10000,
            'change_amount'  => 1000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'notes'          => 'Uang pas kembalian seribu',
        ]);

        SaleItem::create([
            'sale_id'       => $this->salePaid->id,
            'product_id'    => $this->product->id,
            'cost_price'    => 4000,
            'selling_price' => 5000,
            'quantity'      => 2,
            'subtotal'      => 10000,
        ]);

        // 2. Debt Transaction
        $this->saleDebt = Sale::create([
            'invoice_number' => 'INV-20261014-0002',
            'user_id'        => $this->admin->id,
            'customer_id'    => $this->customer->id,
            'subtotal'       => 15000,
            'discount'       => 0,
            'total_amount'   => 15000,
            'paid_amount'    => 5000,
            'change_amount'  => 0,
            'payment_method' => 'debt',
            'payment_status' => 'debt',
            'notes'          => 'Bon tempo 1 minggu',
        ]);

        SaleItem::create([
            'sale_id'       => $this->saleDebt->id,
            'product_id'    => $this->product->id,
            'cost_price'    => 4000,
            'selling_price' => 5000,
            'quantity'      => 3,
            'subtotal'      => 15000,
        ]);
    }

    public function test_guest_cannot_access_transactions(): void
    {
        $response = $this->get(route('transactions.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_transactions_index_and_stats(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('transactions.index'));

        $response->assertOk();
        $response->assertViewIs('transactions.index');
        $response->assertSee('INV-20261014-0001');
        $response->assertSee('INV-20261014-0002');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Total Penjualan');
    }

    public function test_filter_transactions_by_status(): void
    {
        // Filter Lunas (Paid)
        $responsePaid = $this->actingAs($this->cashier)->get(route('transactions.index', [
            'payment_status' => 'paid',
        ]));
        $responsePaid->assertOk();
        $responsePaid->assertSee('INV-20261014-0001');
        $responsePaid->assertDontSee('INV-20261014-0002');

        // Filter Hutang (Debt)
        $responseDebt = $this->actingAs($this->cashier)->get(route('transactions.index', [
            'payment_status' => 'debt',
        ]));
        $responseDebt->assertOk();
        $responseDebt->assertSee('INV-20261014-0002');
        $responseDebt->assertDontSee('INV-20261014-0001');
    }

    public function test_filter_transactions_by_cashier(): void
    {
        $response = $this->actingAs($this->admin)->get(route('transactions.index', [
            'user_id' => $this->cashier->id,
        ]));

        $response->assertOk();
        $response->assertSee('INV-20261014-0001');
        $response->assertDontSee('INV-20261014-0002');
    }

    public function test_filter_transactions_by_search_keyword(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('transactions.index', [
            'q' => '0001',
        ]));

        $response->assertOk();
        $response->assertSee('INV-20261014-0001');
        $response->assertDontSee('INV-20261014-0002');
    }

    public function test_ajax_detail_endpoint_returns_json(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('transactions.detail', $this->salePaid->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'sale'    => [
                'id'             => $this->salePaid->id,
                'invoice_number' => 'INV-20261014-0001',
                'customer_name'  => 'Budi Santoso',
                'subtotal'       => 10000,
                'discount'       => 1000,
                'total_amount'   => 9000,
                'paid_amount'    => 10000,
                'change_amount'  => 1000,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
            ],
        ]);
        $response->assertJsonStructure([
            'sale' => [
                'items' => [
                    '*' => [
                        'product_name',
                        'barcode',
                        'unit',
                        'selling_price',
                        'quantity',
                        'subtotal',
                    ],
                ],
            ],
        ]);
    }

    public function test_show_transaction_page(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('transactions.show', $this->salePaid->id));

        $response->assertOk();
        $response->assertViewIs('transactions.show');
        $response->assertSee('INV-20261014-0001');
        $response->assertSee('Biskuit Cokelat');
    }

    public function test_reprint_thermal_receipt(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('kasir.receipt', [
            'sale'  => $this->salePaid->id,
            'paper' => '58',
        ]));

        $response->assertOk();
        $response->assertViewIs('kasir.receipt');
        $response->assertSee('INV-20261014-0001');
        $response->assertSee('58mm');
    }

    public function test_reprint_receipt_pdf(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('kasir.receipt.pdf', $this->salePaid->id));

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }
}
