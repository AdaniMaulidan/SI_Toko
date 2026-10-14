<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\DebtPayment;
use Carbon\Carbon;
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
     * Tampilkan data rincian piutang / hutang pelanggan, filter status, dan monitoring jatuh tempo (FASE 6.1)
     */
    public function debt(Request $request)
    {
        $query = CustomerDebt::with(['customer', 'sale.items.product', 'payments']);

        // 1. Filter Status (unpaid, partial, paid, overdue, active, all)
        $status = $request->get('status', 'active');
        if ($status === 'unpaid') {
            $query->where('status', 'unpaid');
        } elseif ($status === 'partial') {
            $query->where('status', 'partial');
        } elseif ($status === 'paid') {
            $query->where('status', 'paid');
        } elseif ($status === 'overdue') {
            $query->overdue();
        } elseif ($status === 'all') {
            // Tampilkan semua tanpa filter status
        } else {
            // Default: 'active' (belum lunas & dicicil sebagian)
            $status = 'active';
            $query->unpaid();
        }

        // 2. Filter Status Jatuh Tempo Khusus (due_status)
        if ($request->filled('due_status')) {
            $today = Carbon::today();
            if ($request->due_status === 'overdue') {
                $query->whereNotNull('due_date')->whereDate('due_date', '<', $today);
            } elseif ($request->due_status === 'today') {
                $query->whereNotNull('due_date')->whereDate('due_date', $today);
            } elseif ($request->due_status === 'upcoming_3') {
                $query->whereNotNull('due_date')->whereBetween('due_date', [$today, $today->copy()->addDays(3)]);
            } elseif ($request->due_status === 'upcoming_7') {
                $query->whereNotNull('due_date')->whereBetween('due_date', [$today, $today->copy()->addDays(7)]);
            }
        }

        // 3. Filter Pelanggan Spesifik
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // 4. Filter Rentang Tanggal (Berdasarkan Tgl Jatuh Tempo atau Tgl Transaksi)
        $dateType = $request->get('date_type', 'due_date');
        $dateColumn = $dateType === 'created_at' ? 'created_at' : 'due_date';
        if ($request->filled('start_date')) {
            $query->whereDate($dateColumn, '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate($dateColumn, '<=', $request->end_date);
        }

        // 5. Pencarian Keyword
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('sale', function ($sq) use ($search) {
                    $sq->where('invoice_number', 'like', "%{$search}%");
                })->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // 6. Pengurutan Data (Sorting)
        $sort = $request->get('sort', 'latest');
        if ($sort === 'due_date_asc') {
            $query->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC');
        } elseif ($sort === 'due_date_desc') {
            $query->orderByDesc('due_date');
        } elseif ($sort === 'remaining_desc') {
            $query->orderByDesc('remaining_debt');
        } elseif ($sort === 'remaining_asc') {
            $query->orderBy('remaining_debt');
        } else {
            $query->latest('id');
        }

        $debts = $query->paginate(15)->withQueryString();

        // Metrik Ringkasan (KPI Piutang Toko)
        $stats = [
            'total_receivable' => (float) CustomerDebt::unpaid()->sum('remaining_debt'),
            'count_active'     => CustomerDebt::unpaid()->count(),
            'total_overdue'    => (float) CustomerDebt::overdue()->sum('remaining_debt'),
            'count_overdue'    => CustomerDebt::overdue()->count(),
            'total_partial'    => (float) CustomerDebt::where('status', 'partial')->sum('remaining_debt'),
            'count_partial'    => CustomerDebt::where('status', 'partial')->count(),
            'count_unpaid'     => CustomerDebt::where('status', 'unpaid')->count(),
            'total_paid'       => (float) CustomerDebt::paid()->sum('total_debt'),
            'count_paid'       => CustomerDebt::paid()->count(),
            'count_due_today'  => CustomerDebt::dueToday()->count(),
            'count_due_soon'   => CustomerDebt::dueSoon(3)->count(),
        ];

        // Daftar Pelanggan untuk filter dropdown
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone', 'total_debt']);

        // Kompatibilitas variabel lama $totalReceivable
        $totalReceivable = $stats['total_receivable'];

        return view('customers.debt', compact('debts', 'stats', 'customers', 'status', 'totalReceivable'));
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
