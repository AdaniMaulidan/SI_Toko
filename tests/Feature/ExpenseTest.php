<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected User $staff;

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

        $this->staff = User::factory()->create([
            'name'      => 'Staff Gudang',
            'role'      => User::ROLE_STAFF,
            'is_active' => true,
        ]);

        Storage::fake('public');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('expenses.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_cashier_and_staff_cannot_access_expenses(): void
    {
        $responseCashier = $this->actingAs($this->cashier)->get(route('expenses.index'));
        $responseCashier->assertForbidden();

        $responseStaff = $this->actingAs($this->staff)->get(route('expenses.index'));
        $responseStaff->assertForbidden();
    }

    public function test_admin_can_view_expenses_index_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('expenses.index'));
        $response->assertOk();
        $response->assertViewIs('expenses.index');
        $response->assertSee('Pengeluaran Toko');
        $response->assertSee('Catat Pengeluaran');
    }

    public function test_admin_can_store_expense_without_receipt_and_records_cashflow(): void
    {
        $payload = [
            'date'             => '2026-10-15',
            'expense_category' => 'Operasional',
            'amount'           => 350000,
            'description'      => 'Pembayaran tagihan listrik PLN bulan ini',
        ];

        $response = $this->actingAs($this->admin)->post(route('expenses.store'), $payload);

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('success');

        // Verifikasi expense tersimpan di DB
        $this->assertDatabaseHas('expenses', [
            'expense_category' => 'Operasional',
            'amount'           => 350000,
            'description'      => 'Pembayaran tagihan listrik PLN bulan ini',
            'user_id'          => $this->admin->id,
        ]);

        $expense = Expense::where('description', 'Pembayaran tagihan listrik PLN bulan ini')->first();
        $this->assertNotNull($expense);
        $this->assertStringStartsWith('EXP-', $expense->code);

        // Verifikasi CashFlow keluar (type: out) otomatis tercatat
        $this->assertDatabaseHas('cash_flows', [
            'user_id'        => $this->admin->id,
            'type'           => 'out',
            'category'       => 'Pengeluaran',
            'amount'         => 350000,
            'reference_type' => 'expense',
            'reference_id'   => $expense->id,
        ]);
    }

    public function test_admin_can_store_expense_with_receipt_upload(): void
    {
        $file = UploadedFile::fake()->image('struk_listrik.jpg', 600, 800);

        $payload = [
            'date'             => '2026-10-15',
            'expense_category' => 'Perlengkapan',
            'amount'           => 75000,
            'description'      => 'Beli lakban cokelat & kantong plastik packing',
            'receipt_image'    => $file,
        ];

        $response = $this->actingAs($this->admin)->post(route('expenses.store'), $payload);

        $response->assertRedirect(route('expenses.index'));

        $expense = Expense::where('description', 'Beli lakban cokelat & kantong plastik packing')->first();
        $this->assertNotNull($expense);
        $this->assertNotNull($expense->receipt_image);

        // Verifikasi file tersimpan di storage public
        Storage::disk('public')->assertExists($expense->receipt_image);
    }

    public function test_admin_can_view_expense_detail_via_json(): void
    {
        $expense = Expense::create([
            'code'             => 'EXP-20261015-0001',
            'user_id'          => $this->admin->id,
            'expense_category' => 'Gaji',
            'amount'           => 2500000,
            'date'             => '2026-10-15',
            'description'      => 'Gaji Bulanan Staff Toko',
        ]);

        $response = $this->actingAs($this->admin)->get(route('expenses.detail', $expense));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'expense' => [
                'id'               => $expense->id,
                'code'             => 'EXP-20261015-0001',
                'expense_category' => 'Gaji',
                'amount'           => 2500000,
                'description'      => 'Gaji Bulanan Staff Toko',
                'user_name'        => 'Admin Toko',
            ],
        ]);
    }

    public function test_admin_can_update_expense_and_sync_cashflow(): void
    {
        $expense = Expense::create([
            'code'             => 'EXP-20261015-0002',
            'user_id'          => $this->admin->id,
            'expense_category' => 'Sewa',
            'amount'           => 1000000,
            'date'             => '2026-10-15',
            'description'      => 'DP Sewa Tempat',
        ]);

        CashFlow::create([
            'user_id'        => $this->admin->id,
            'type'           => 'out',
            'category'       => 'Pengeluaran',
            'amount'         => 1000000,
            'reference_type' => 'expense',
            'reference_id'   => $expense->id,
            'date'           => '2026-10-15',
            'notes'          => 'DP Sewa Tempat',
        ]);

        $updatePayload = [
            'date'             => '2026-10-16',
            'expense_category' => 'Sewa',
            'amount'           => 1500000,
            'description'      => 'Pelunasan Sewa Tempat Toko',
        ];

        $response = $this->actingAs($this->admin)->put(route('expenses.update', $expense), $updatePayload);

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('success');

        // Verifikasi expense terupdate
        $expense->refresh();
        $this->assertEquals(1500000, (float) $expense->amount);
        $this->assertEquals('Pelunasan Sewa Tempat Toko', $expense->description);

        // Verifikasi cashflow terupdate
        $this->assertDatabaseHas('cash_flows', [
            'reference_type' => 'expense',
            'reference_id'   => $expense->id,
            'amount'         => 1500000,
            'date'           => '2026-10-16',
        ]);
    }

    public function test_admin_can_delete_expense_and_remove_related_cashflow_and_image(): void
    {
        $file = UploadedFile::fake()->image('struk_hapus.jpg');
        $storedPath = $file->store('expenses/receipts', 'public');

        $expense = Expense::create([
            'code'             => 'EXP-20261015-0003',
            'user_id'          => $this->admin->id,
            'expense_category' => 'Lainnya',
            'amount'           => 50000,
            'date'             => '2026-10-15',
            'description'      => 'Biaya parkir bongkar muatan',
            'receipt_image'    => $storedPath,
        ]);

        CashFlow::create([
            'user_id'        => $this->admin->id,
            'type'           => 'out',
            'category'       => 'Pengeluaran',
            'amount'         => 50000,
            'reference_type' => 'expense',
            'reference_id'   => $expense->id,
            'date'           => '2026-10-15',
            'notes'          => 'Biaya parkir',
        ]);

        Storage::disk('public')->assertExists($storedPath);

        $response = $this->actingAs($this->admin)->delete(route('expenses.destroy', $expense));

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('success');

        // Expense harus terhapus
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);

        // CashFlow harus terhapus
        $this->assertDatabaseMissing('cash_flows', [
            'reference_type' => 'expense',
            'reference_id'   => $expense->id,
        ]);

        // File bukti harus terhapus dari storage
        Storage::disk('public')->assertMissing($storedPath);
    }
}
