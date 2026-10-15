<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'      => 'Admin Finance',
            'role'      => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'name'      => 'Kasir Toko',
            'role'      => User::ROLE_CASHIER,
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'name'      => 'Staff Toko',
            'role'      => User::ROLE_STAFF,
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('finance.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_cashier_and_staff_cannot_access_finance(): void
    {
        $responseCashier = $this->actingAs($this->cashier)->get(route('finance.index'));
        $responseCashier->assertForbidden();

        $responseStaff = $this->actingAs($this->staff)->get(route('finance.index'));
        $responseStaff->assertForbidden();
    }

    public function test_admin_can_view_finance_page_with_accurate_balance_and_running_balance(): void
    {
        // 1. Catat modal awal: +1.000.000
        CashFlow::create([
            'user_id'        => $this->admin->id,
            'type'           => 'in',
            'category'       => 'Modal Awal',
            'amount'         => 1000000,
            'reference_type' => 'manual',
            'date'           => '2026-10-01',
            'notes'          => 'Modal kasir',
        ]);

        // 2. Catat penjualan: +500.000
        CashFlow::create([
            'user_id'        => $this->admin->id,
            'type'           => 'in',
            'category'       => 'Penjualan',
            'amount'         => 500000,
            'reference_type' => 'sale',
            'reference_id'   => 1,
            'date'           => '2026-10-02',
            'notes'          => 'Penjualan TRX-1',
        ]);

        // 3. Catat pembelian kulakan: -300.000
        CashFlow::create([
            'user_id'        => $this->admin->id,
            'type'           => 'out',
            'category'       => 'Pembelian',
            'amount'         => 300000,
            'reference_type' => 'purchase',
            'reference_id'   => 1,
            'date'           => '2026-10-03',
            'notes'          => 'Kulakan barang PO-1',
        ]);

        // 4. Catat pengeluaran listrik: -150.000
        CashFlow::create([
            'user_id'        => $this->admin->id,
            'type'           => 'out',
            'category'       => 'Pengeluaran',
            'amount'         => 150000,
            'reference_type' => 'expense',
            'reference_id'   => 1,
            'date'           => '2026-10-04',
            'notes'          => 'Listrik PLN',
        ]);

        // 5. Catat pelunasan piutang pelanggan: +200.000
        CashFlow::create([
            'user_id'        => $this->admin->id,
            'type'           => 'in',
            'category'       => 'Pelunasan Hutang',
            'amount'         => 200000,
            'reference_type' => 'debt_payment',
            'reference_id'   => 1,
            'date'           => '2026-10-05',
            'notes'          => 'Cicilan dari Budi',
        ]);

        // Total Kas Masuk  = 1.000.000 + 500.000 + 200.000 = 1.700.000
        // Total Kas Keluar = 300.000 + 150.000             =   450.000
        // Saldo Riil Akhir = 1.700.000 - 450.000           = 1.250.000

        $response = $this->actingAs($this->admin)->get(route('finance.index'));

        $response->assertOk();
        $response->assertViewIs('finance.index');
        $response->assertSee('Buku Kas');
        $response->assertSee('Rp 1.250.000'); // Saldo Kas Riil
        $response->assertSee('Penjualan');
        $response->assertSee('Pembelian');
        $response->assertSee('Pengeluaran');
        $response->assertSee('Pelunasan Hutang');
        $response->assertSee('Modal Awal');
    }

    public function test_admin_can_record_manual_cash_outflow_for_bank_deposit(): void
    {
        $payload = [
            'date'     => '2026-10-15',
            'type'     => 'out',
            'category' => 'Setor Bank',
            'amount'   => 2000000,
            'notes'    => 'Setoran tunai ke rekening BCA Toko',
        ];

        $response = $this->actingAs($this->admin)->post(route('finance.store'), $payload);

        $response->assertRedirect(route('finance.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cash_flows', [
            'type'           => 'out',
            'category'       => 'Setor Bank',
            'amount'         => 2000000,
            'reference_type' => 'manual',
            'notes'          => 'Setoran tunai ke rekening BCA Toko',
        ]);
    }

    public function test_admin_can_record_manual_cash_inflow_for_initial_capital(): void
    {
        $payload = [
            'date'     => '2026-10-15',
            'type'     => 'in',
            'category' => 'Modal Awal',
            'amount'   => 500000,
            'notes'    => 'Uang kembalian kasir pagi',
        ];

        $response = $this->actingAs($this->admin)->post(route('finance.store'), $payload);

        $response->assertRedirect(route('finance.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cash_flows', [
            'type'           => 'in',
            'category'       => 'Modal Awal',
            'amount'         => 500000,
            'reference_type' => 'manual',
        ]);
    }

    public function test_admin_can_delete_only_manual_cash_flow(): void
    {
        // 1. Mutasi manual
        $manual = CashFlow::create([
            'user_id'        => $this->admin->id,
            'type'           => 'out',
            'category'       => 'Setor Bank',
            'amount'         => 100000,
            'reference_type' => 'manual',
            'date'           => '2026-10-15',
            'notes'          => 'Salah input',
        ]);

        $responseManual = $this->actingAs($this->admin)->delete(route('finance.destroy', $manual));
        $responseManual->assertRedirect(route('finance.index'));
        $responseManual->assertSessionHas('success');
        $this->assertDatabaseMissing('cash_flows', ['id' => $manual->id]);

        // 2. Mutasi otomatis dari penjualan
        $auto = CashFlow::create([
            'user_id'        => $this->admin->id,
            'type'           => 'in',
            'category'       => 'Penjualan',
            'amount'         => 150000,
            'reference_type' => 'sale',
            'reference_id'   => 99,
            'date'           => '2026-10-15',
            'notes'          => 'Penjualan otomatis',
        ]);

        $responseAuto = $this->actingAs($this->admin)->delete(route('finance.destroy', $auto));
        $responseAuto->assertRedirect(route('finance.index'));
        $responseAuto->assertSessionHas('error');
        $this->assertDatabaseHas('cash_flows', ['id' => $auto->id]);
    }

    public function test_filters_work_properly(): void
    {
        CashFlow::create([
            'user_id'        => $this->admin->id,
            'type'           => 'in',
            'category'       => 'Penjualan',
            'amount'         => 100000,
            'reference_type' => 'sale',
            'date'           => '2026-09-01',
            'notes'          => 'Penjualan September',
        ]);

        CashFlow::create([
            'user_id'        => $this->admin->id,
            'type'           => 'out',
            'category'       => 'Pembelian',
            'amount'         => 50000,
            'reference_type' => 'purchase',
            'date'           => '2026-10-10',
            'notes'          => 'Kulakan Oktober',
        ]);

        // Filter type: out
        $responseType = $this->actingAs($this->admin)->get(route('finance.index', ['type' => 'out']));
        $responseType->assertOk();
        $responseType->assertSee('Kulakan Oktober');
        $responseType->assertDontSee('Penjualan September');

        // Filter date range
        $responseDate = $this->actingAs($this->admin)->get(route('finance.index', [
            'start_date' => '2026-10-01',
            'end_date'   => '2026-10-31',
        ]));
        $responseDate->assertOk();
        $responseDate->assertSee('Kulakan Oktober');
        $responseDate->assertDontSee('Penjualan September');
    }
}
