<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\DebtPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Tampilkan daftar seluruh pelanggan & ringkasan saldo hutang
     */
    public function index(Request $request)
    {
        $query = Customer::withCount('sales');

        // Pencarian nama, no telepon, email, alamat
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Filter status hutang
        if ($request->filled('debt_status')) {
            if ($request->debt_status === 'has_debt') {
                $query->where('total_debt', '>', 0);
            } elseif ($request->debt_status === 'no_debt') {
                $query->where('total_debt', '<=', 0);
            }
        }

        $customers = $query->latest()->paginate(15)->withQueryString();

        // Ringkasan metrik statistik
        $stats = [
            'total'          => Customer::count(),
            'with_debt'      => Customer::where('total_debt', '>', 0)->count(),
            'total_debt_sum' => Customer::sum('total_debt'),
            'total_sales'    => Customer::withCount('sales')->get()->sum('sales_count'),
        ];

        return view('customers.index', compact('customers', 'stats'));
    }

    /**
     * Simpan data pelanggan baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:150'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'email'   => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'email.email'   => 'Format alamat email tidak valid.',
        ]);

        $validated['total_debt'] = 0;

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Pelanggan baru berhasil ditambahkan.');
    }

    /**
     * Perbarui data pelanggan
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:150'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'email'   => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'email.email'   => 'Format alamat email tidak valid.',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    /**
     * Hapus data pelanggan (dengan proteksi integritas data)
     */
    public function destroy(Customer $customer)
    {
        // Proteksi 1: Masih memiliki saldo hutang aktif
        if ($customer->total_debt > 0 || $customer->debts()->unpaid()->exists()) {
            $formattedDebt = number_format($customer->total_debt, 0, ',', '.');
            return redirect()->route('customers.index')
                ->with('error', "Pelanggan \"{$customer->name}\" tidak dapat dihapus karena masih memiliki tanggungan saldo hutang sebesar Rp {$formattedDebt}.");
        }

        // Proteksi 2: Memiliki riwayat transaksi penjualan
        if ($customer->sales()->exists()) {
            return redirect()->route('customers.index')
                ->with('error', "Pelanggan \"{$customer->name}\" tidak dapat dihapus karena memiliki riwayat transaksi penjualan. Anda dapat memperbarui datanya.");
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Data pelanggan berhasil dihapus.');
    }

    /**
     * Tampilkan data rincian piutang / hutang pelanggan yang belum lunas
     */
    public function debt(Request $request)
    {
        $query = CustomerDebt::with(['customer', 'sale'])->unpaid();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('sale', function ($sq) use ($search) {
                    $sq->where('invoice_number', 'like', "%{$search}%");
                });
            });
        }

        $debts = $query->latest()->paginate(15)->withQueryString();
        $totalReceivable = CustomerDebt::unpaid()->sum('remaining_debt');

        return view('customers.debt', compact('debts', 'totalReceivable'));
    }

    /**
     * Proses pembayaran cicilan / pelunasan hutang pelanggan
     */
    public function payDebt(Request $request, CustomerDebt $debt)
    {
        $validated = $request->validate([
            'amount'         => ['required', 'numeric', 'min:1', 'max:' . $debt->remaining_debt],
            'payment_method' => ['required', 'string', 'in:cash,transfer,qris'],
            'note'           => ['nullable', 'string', 'max:255'],
        ], [
            'amount.required' => 'Jumlah pembayaran wajib diisi.',
            'amount.min'      => 'Jumlah pembayaran minimal Rp 1.',
            'amount.max'      => 'Jumlah pembayaran tidak boleh melebihi sisa hutang (Rp ' . number_format($debt->remaining_debt, 0, ',', '.') . ').',
        ]);

        DB::transaction(function () use ($validated, $debt) {
            $amount = $validated['amount'];

            // 1. Catat log pembayaran hutang
            DebtPayment::create([
                'customer_debt_id' => $debt->id,
                'user_id'          => auth()->id(),
                'payment_date'     => now()->toDateString(),
                'amount'           => $amount,
                'payment_method'   => $validated['payment_method'],
                'note'             => $validated['note'] ?? 'Pembayaran hutang',
            ]);

            // 2. Kurangi sisa hutang pada invoice ini
            $debt->remaining_debt = max(0, $debt->remaining_debt - $amount);
            $debt->status = $debt->remaining_debt <= 0 ? 'paid' : 'partial';
            $debt->save();

            // 3. Kurangi total saldo hutang master pelanggan
            $customer = $debt->customer;
            if ($customer) {
                $customer->total_debt = max(0, $customer->total_debt - $amount);
                $customer->save();
            }
        });

        return redirect()->route('customers.debt')->with('success', 'Pembayaran hutang berhasil dicatat.');
    }
}
