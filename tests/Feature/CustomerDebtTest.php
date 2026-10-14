<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\DebtPayment;
use App\Models\Sale;
use App\Models\StoreSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDebtTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected Customer $customerA;
    protected Customer $customerB;
    protected Sale $saleA;
    protected Sale $saleB;

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

        $this->customerA = Customer::create([
            'name'       => 'Pelanggan Toko A',
            'phone'      => '081234567890',
            'total_debt' => 150000,
        ]);

        $this->customerB = Customer::create([
            'name'       => 'Pelanggan Toko B',
            'phone'      => '089876543210',
            'total_debt' => 50000,
        ]);

        $this->saleA = Sale::create([
            'invoice_number' => 'INV-20261014-001',
            'user_id'        => $this->admin->id,
            'customer_id'    => $this->customerA->id,
            'subtotal'       => 200000,
            'tax'            => 0,
            'discount'       => 0,
            'final_total'    => 200000,
            'paid_amount'    => 50000,
            'change_amount'  => 0,
            'payment_method' => 'debt',
            'payment_status' => 'debt',
        ]);

        $this->saleB = Sale::create([
            'invoice_number' => 'INV-20261014-002',
            'user_id'        => $this->admin->id,
            'customer_id'    => $this->customerB->id,
            'subtotal'       => 50000,
            'tax'            => 0,
            'discount'       => 0,
            'final_total'    => 50000,
            'paid_amount'    => 0,
            'change_amount'  => 0,
            'payment_method' => 'debt',
            'payment_status' => 'debt',
        ]);
    }

    /**
     * Test admin & kasir dapat mengakses halaman daftar hutang
     */
    public function test_authenticated_user_can_access_customer_debt_page(): void
    {
        // Overdue debt (jatuh tempo 5 hari lalu)
        CustomerDebt::create([
            'sale_id'        => $this->saleA->id,
            'customer_id'    => $this->customerA->id,
            'total_debt'     => 150000,
            'remaining_debt' => 150000,
            'due_date'       => Carbon::today()->subDays(5),
            'status'         => 'unpaid',
        ]);

        $response = $this->actingAs($this->admin)->get(route('customers.debt'));

        $response->assertStatus(200);
        $response->assertViewIs('customers.debt');
        $response->assertSee('Daftar Hutang Pelanggan');
        $response->assertSee('Pelanggan Toko A');
        $response->assertSee('INV-20261014-001');
        $response->assertSee('Terlambat 5 hari');
        $response->assertSee('Total Piutang Aktif');
        $response->assertViewHas('stats');
        $response->assertViewHas('debts');
    }

    /**
     * Test guest tidak dapat mengakses halaman hutang pelanggan
     */
    public function test_guest_cannot_access_customer_debt_page(): void
    {
        $response = $this->get(route('customers.debt'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test filter status: unpaid, partial, paid, overdue, active
     */
    public function test_filter_by_status(): void
    {
        // Debt 1: Belum Lunas (Unpaid)
        $debtUnpaid = CustomerDebt::create([
            'sale_id'        => $this->saleA->id,
            'customer_id'    => $this->customerA->id,
            'total_debt'     => 100000,
            'remaining_debt' => 100000,
            'due_date'       => Carbon::today()->addDays(5),
            'status'         => 'unpaid',
        ]);

        // Debt 2: Sebagian (Partial)
        $debtPartial = CustomerDebt::create([
            'sale_id'        => $this->saleB->id,
            'customer_id'    => $this->customerB->id,
            'total_debt'     => 100000,
            'remaining_debt' => 50000,
            'due_date'       => Carbon::today()->addDays(3),
            'status'         => 'partial',
        ]);

        // Debt 3: Lunas (Paid)
        $debtPaid = CustomerDebt::create([
            'sale_id'        => $this->saleA->id,
            'customer_id'    => $this->customerA->id,
            'total_debt'     => 50000,
            'remaining_debt' => 0,
            'due_date'       => Carbon::today()->subDays(2),
            'status'         => 'paid',
        ]);

        // 1. Filter Unpaid
        $resUnpaid = $this->actingAs($this->admin)->get(route('customers.debt', ['status' => 'unpaid']));
        $resUnpaid->assertStatus(200);
        $debtsUnpaid = $resUnpaid->viewData('debts');
        $this->assertTrue($debtsUnpaid->contains('id', $debtUnpaid->id));
        $this->assertFalse($debtsUnpaid->contains('id', $debtPartial->id));
        $this->assertFalse($debtsUnpaid->contains('id', $debtPaid->id));

        // 2. Filter Partial
        $resPartial = $this->actingAs($this->admin)->get(route('customers.debt', ['status' => 'partial']));
        $resPartial->assertStatus(200);
        $debtsPartial = $resPartial->viewData('debts');
        $this->assertTrue($debtsPartial->contains('id', $debtPartial->id));
        $this->assertFalse($debtsPartial->contains('id', $debtUnpaid->id));
        $this->assertFalse($debtsPartial->contains('id', $debtPaid->id));

        // 3. Filter Paid
        $resPaid = $this->actingAs($this->admin)->get(route('customers.debt', ['status' => 'paid']));
        $resPaid->assertStatus(200);
        $debtsPaid = $resPaid->viewData('debts');
        $this->assertTrue($debtsPaid->contains('id', $debtPaid->id));
        $this->assertFalse($debtsPaid->contains('id', $debtUnpaid->id));

        // 4. Filter Active (default: unpaid & partial, excluding paid)
        $resActive = $this->actingAs($this->admin)->get(route('customers.debt', ['status' => 'active']));
        $resActive->assertStatus(200);
        $debtsActive = $resActive->viewData('debts');
        $this->assertTrue($debtsActive->contains('id', $debtUnpaid->id));
        $this->assertTrue($debtsActive->contains('id', $debtPartial->id));
        $this->assertFalse($debtsActive->contains('id', $debtPaid->id));
    }

    /**
     * Test filter jatuh tempo dan peringatan keterlambatan (overdue)
     */
    public function test_overdue_warning_and_indicators(): void
    {
        // 1. Overdue debt (7 hari lalu)
        $overdueDebt = CustomerDebt::create([
            'sale_id'        => $this->saleA->id,
            'customer_id'    => $this->customerA->id,
            'total_debt'     => 120000,
            'remaining_debt' => 120000,
            'due_date'       => Carbon::today()->subDays(7),
            'status'         => 'unpaid',
        ]);

        $this->assertTrue($overdueDebt->is_overdue);
        $this->assertEquals(7, $overdueDebt->days_overdue);

        // 2. Due today
        $dueTodayDebt = CustomerDebt::create([
            'sale_id'        => $this->saleB->id,
            'customer_id'    => $this->customerB->id,
            'total_debt'     => 80000,
            'remaining_debt' => 80000,
            'due_date'       => Carbon::today(),
            'status'         => 'unpaid',
        ]);

        $this->assertTrue($dueTodayDebt->is_due_today);

        // Akses via HTTP dan cek tampilan peringatan
        $response = $this->actingAs($this->cashier)->get(route('customers.debt'));
        $response->assertStatus(200);
        $response->assertSee('Terlambat 7 hari');
        $response->assertSee('Jatuh Tempo Hari Ini!');

        // Filter khusus overdue
        $resFilterOverdue = $this->actingAs($this->admin)->get(route('customers.debt', ['due_status' => 'overdue']));
        $resFilterOverdue->assertStatus(200);
        $debtsOverdue = $resFilterOverdue->viewData('debts');
        $this->assertTrue($debtsOverdue->contains('id', $overdueDebt->id));
        $this->assertFalse($debtsOverdue->contains('id', $dueTodayDebt->id));
    }

    /**
     * Test pencarian hutang pelanggan berdasarkan nama, telepon, dan invoice
     */
    public function test_search_customer_debt(): void
    {
        $debtA = CustomerDebt::create([
            'sale_id'        => $this->saleA->id,
            'customer_id'    => $this->customerA->id,
            'total_debt'     => 150000,
            'remaining_debt' => 150000,
            'due_date'       => Carbon::today()->addDays(10),
            'status'         => 'unpaid',
        ]);

        $debtB = CustomerDebt::create([
            'sale_id'        => $this->saleB->id,
            'customer_id'    => $this->customerB->id,
            'total_debt'     => 50000,
            'remaining_debt' => 50000,
            'due_date'       => Carbon::today()->addDays(10),
            'status'         => 'unpaid',
        ]);

        // Cari berdasarkan nama
        $resName = $this->actingAs($this->admin)->get(route('customers.debt', ['search' => 'Toko A']));
        $resName->assertStatus(200);
        $debtsName = $resName->viewData('debts');
        $this->assertTrue($debtsName->contains('id', $debtA->id));
        $this->assertFalse($debtsName->contains('id', $debtB->id));

        // Cari berdasarkan no invoice
        $resInv = $this->actingAs($this->admin)->get(route('customers.debt', ['search' => 'INV-20261014-002']));
        $resInv->assertStatus(200);
        $debtsInv = $resInv->viewData('debts');
        $this->assertTrue($debtsInv->contains('id', $debtB->id));
        $this->assertFalse($debtsInv->contains('id', $debtA->id));
    }

    /**
     * Test filter berdasarkan pelanggan spesifik
     */
    public function test_filter_by_customer_id(): void
    {
        $debtA = CustomerDebt::create([
            'sale_id'        => $this->saleA->id,
            'customer_id'    => $this->customerA->id,
            'total_debt'     => 150000,
            'remaining_debt' => 150000,
            'due_date'       => Carbon::today()->addDays(5),
            'status'         => 'unpaid',
        ]);

        $debtB = CustomerDebt::create([
            'sale_id'        => $this->saleB->id,
            'customer_id'    => $this->customerB->id,
            'total_debt'     => 50000,
            'remaining_debt' => 50000,
            'due_date'       => Carbon::today()->addDays(5),
            'status'         => 'unpaid',
        ]);

        $response = $this->actingAs($this->admin)->get(route('customers.debt', ['customer_id' => $this->customerB->id]));
        $response->assertStatus(200);
        $debtsCust = $response->viewData('debts');
        $this->assertTrue($debtsCust->contains('id', $debtB->id));
        $this->assertFalse($debtsCust->contains('id', $debtA->id));
    }

    /**
     * Test cicilan pembayaran hutang: kurangi sisa hutang, catat log pembayaran, dan catat uang masuk kas toko (FASE 6.2)
     */
    public function test_can_pay_debt_installment_and_record_cashflow(): void
    {
        $debt = CustomerDebt::create([
            'sale_id'        => $this->saleA->id,
            'customer_id'    => $this->customerA->id,
            'total_debt'     => 150000,
            'remaining_debt' => 150000,
            'due_date'       => Carbon::today()->addDays(7),
            'status'         => 'unpaid',
        ]);

        $this->customerA->update(['total_debt' => 150000]);

        // Bayar cicilan sebesar Rp 50.000
        $response = $this->actingAs($this->cashier)->post(route('customers.debt.pay', $debt->id), [
            'amount'         => 50000,
            'payment_date'   => Carbon::today()->toDateString(),
            'payment_method' => 'cash',
            'note'           => 'Cicilan pertama',
        ]);

        $response->assertRedirect(route('customers.debt'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('last_payment_id');

        // 1. Verifikasi CustomerDebt sisa hutang berkurang & status partial
        $debt->refresh();
        $this->assertEquals(100000, $debt->remaining_debt);
        $this->assertEquals('partial', $debt->status);

        // 2. Verifikasi Customer master total_debt berkurang
        $this->customerA->refresh();
        $this->assertEquals(100000, $this->customerA->total_debt);

        // 3. Verifikasi log DebtPayment tercatat
        $payment = DebtPayment::where('customer_debt_id', $debt->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(50000, $payment->amount);
        $this->assertEquals('cash', $payment->payment_method);
        $this->assertEquals('Cicilan pertama', $payment->note);
        $this->assertEquals($this->cashier->id, $payment->user_id);

        // 4. Verifikasi uang masuk kas toko (CashFlow)
        $cashflow = CashFlow::where('reference_type', 'debt_payment')
                            ->where('reference_id', $payment->id)
                            ->first();
        $this->assertNotNull($cashflow);
        $this->assertEquals('in', $cashflow->type);
        $this->assertEquals('Pelunasan Hutang', $cashflow->category);
        $this->assertEquals(50000, $cashflow->amount);

        // 5. Verifikasi Sale paid_amount bertambah
        $this->saleA->refresh();
        $this->assertEquals(100000, $this->saleA->paid_amount); // 50k saat checkout + 50k cicilan
    }

    /**
     * Test pelunasan penuh hutang: status menjadi paid dan status transaksi sale menjadi paid (FASE 6.2)
     */
    public function test_can_payoff_full_debt(): void
    {
        $debt = CustomerDebt::create([
            'sale_id'        => $this->saleA->id,
            'customer_id'    => $this->customerA->id,
            'total_debt'     => 150000,
            'remaining_debt' => 150000,
            'due_date'       => Carbon::today()->addDays(7),
            'status'         => 'unpaid',
        ]);

        $this->customerA->update(['total_debt' => 150000]);

        // Bayar lunas 150.000
        $response = $this->actingAs($this->admin)->post(route('customers.debt.pay', $debt->id), [
            'amount'         => 150000,
            'payment_date'   => Carbon::today()->toDateString(),
            'payment_method' => 'transfer',
            'note'           => 'Pelunasan lunas via Transfer BCA',
        ]);

        $response->assertRedirect(route('customers.debt'));

        $debt->refresh();
        $this->assertEquals(0, $debt->remaining_debt);
        $this->assertEquals('paid', $debt->status);

        $this->customerA->refresh();
        $this->assertEquals(0, $this->customerA->total_debt);

        $this->saleA->refresh();
        $this->assertEquals('paid', $this->saleA->payment_status);
        $this->assertEquals(200000, $this->saleA->paid_amount);
    }

    /**
     * Test validasi gagal jika nominal bayar melebihi sisa hutang (FASE 6.2)
     */
    public function test_payment_fails_when_amount_exceeds_remaining_debt(): void
    {
        $debt = CustomerDebt::create([
            'sale_id'        => $this->saleA->id,
            'customer_id'    => $this->customerA->id,
            'total_debt'     => 50000,
            'remaining_debt' => 50000,
            'due_date'       => Carbon::today()->addDays(7),
            'status'         => 'unpaid',
        ]);

        $response = $this->actingAs($this->cashier)->post(route('customers.debt.pay', $debt->id), [
            'amount'         => 60000, // melebihi 50.000
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors('amount');

        // Pastikan tidak ada pembayaran dan cash flow yang tercatat
        $this->assertDatabaseMissing('debt_payments', ['customer_debt_id' => $debt->id]);
        $this->assertDatabaseMissing('cash_flows', ['reference_type' => 'debt_payment']);
    }

    /**
     * Test cetak struk kuitansi pembayaran hutang dan download PDF (FASE 6.2)
     */
    public function test_can_print_and_download_payment_receipt(): void
    {
        $debt = CustomerDebt::create([
            'sale_id'        => $this->saleA->id,
            'customer_id'    => $this->customerA->id,
            'total_debt'     => 100000,
            'remaining_debt' => 50000,
            'due_date'       => Carbon::today()->addDays(3),
            'status'         => 'partial',
        ]);

        $payment = DebtPayment::create([
            'customer_debt_id' => $debt->id,
            'user_id'          => $this->cashier->id,
            'payment_date'     => Carbon::today()->toDateString(),
            'amount'           => 50000,
            'payment_method'   => 'qris',
            'note'             => 'Pembayaran cicilan QRIS',
        ]);

        // 1. Cetak Kuitansi Web / Thermal
        $resReceipt = $this->actingAs($this->cashier)->get(route('customers.debt.receipt', $payment->id));
        $resReceipt->assertOk();
        $resReceipt->assertViewIs('customers.debt_receipt');
        $resReceipt->assertSee('BUKTI PEMBAYARAN HUTANG');
        $resReceipt->assertSee('Pelanggan Toko A');
        $resReceipt->assertSee('50.000');
        $resReceipt->assertSee('QRIS');

        // 2. Download Kuitansi PDF
        $resPdf = $this->actingAs($this->admin)->get(route('customers.debt.receipt.pdf', $payment->id));
        $resPdf->assertOk();
        $this->assertEquals('application/pdf', $resPdf->headers->get('content-type'));
    }
}
