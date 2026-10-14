<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\Sale;
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
}

