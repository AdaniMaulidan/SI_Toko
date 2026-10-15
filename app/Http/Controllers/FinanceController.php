<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    /**
     * Tampilkan buku kas & mutasi arus kas toko lengkap dengan rekap saldo riil (FASE 7.2)
     */
    public function index(Request $request)
    {
        // 1. Rekap Saldo Kas Riil Toko Saat Ini (Semua Cash In - Semua Cash Out sepanjang waktu)
        $allInflow  = (float) CashFlow::where('type', 'in')->sum('amount');
        $allOutflow = (float) CashFlow::where('type', 'out')->sum('amount');
        $currentRealBalance = $allInflow - $allOutflow;

        // 2. Rekap Saldo Kas Bulan Berjalan
        $thisMonthInflow  = (float) CashFlow::where('type', 'in')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        $thisMonthOutflow = (float) CashFlow::where('type', 'out')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        $thisMonthNet = $thisMonthInflow - $thisMonthOutflow;

        // 3. Query Mutasi Kas dengan Subquery Saldo Berjalan (Running Balance)
        $query = CashFlow::with('user')
            ->select('cash_flows.*')
            ->selectRaw("(
                SELECT COALESCE(SUM(CASE WHEN cf2.type = 'in' THEN cf2.amount ELSE -cf2.amount END), 0)
                FROM cash_flows cf2
                WHERE cf2.date < cash_flows.date 
                   OR (cf2.date = cash_flows.date AND cf2.id <= cash_flows.id)
            ) as running_balance");

        // Filter Rentang Tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Filter Jenis Mutasi (in / out)
        if ($request->filled('type') && in_array($request->type, ['in', 'out'])) {
            $query->where('type', $request->type);
        }

        // Filter Kategori Kas
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter Pencarian Keterangan / Referensi
        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('notes', 'like', "%{$keyword}%")
                  ->orWhere('category', 'like', "%{$keyword}%")
                  ->orWhere('reference_type', 'like', "%{$keyword}%");
            });
        }

        // Hitung total ringkasan filter aktif
        $filteredInflow  = (float) (clone $query)->where('type', 'in')->sum('amount');
        $filteredOutflow = (float) (clone $query)->where('type', 'out')->sum('amount');
        $filteredNet     = $filteredInflow - $filteredOutflow;
        $filteredCount   = (clone $query)->count();

        // Ambil daftar kategori unik untuk filter dropdown
        $categories = CashFlow::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $stats = [
            'current_real_balance' => $currentRealBalance,
            'this_month_inflow'    => $thisMonthInflow,
            'this_month_outflow'   => $thisMonthOutflow,
            'this_month_net'       => $thisMonthNet,
            'filtered_inflow'      => $filteredInflow,
            'filtered_outflow'     => $filteredOutflow,
            'filtered_net'         => $filteredNet,
            'filtered_count'       => $filteredCount,
        ];

        // Urutkan transaksi dari yang terbaru
        $cashflows = $query->latest('date')->latest('id')->paginate(20)->withQueryString();

        return view('finance.index', compact('cashflows', 'stats', 'categories'));
    }

    /**
     * Catat mutasi kas manual (Setor Bank, Tarik Bank, Modal Awal, Prive, Penyesuaian Kas) (FASE 7.2)
     */
    public function store(Request $request)
    {
        $request->validate([
            'date'     => 'required|date',
            'type'     => 'required|in:in,out',
            'category' => 'required|string|max:100',
            'amount'   => 'required|numeric|min:1',
            'notes'    => 'nullable|string|max:1000',
        ], [
            'date.required'     => 'Tanggal mutasi kas wajib diisi.',
            'type.required'     => 'Jenis mutasi kas wajib dipilih.',
            'type.in'           => 'Jenis mutasi harus berupa Uang Masuk atau Uang Keluar.',
            'category.required' => 'Kategori mutasi kas wajib diisi.',
            'amount.required'   => 'Nominal mutasi kas wajib diisi.',
            'amount.min'        => 'Nominal mutasi minimal Rp 1.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                CashFlow::create([
                    'user_id'        => Auth::id(),
                    'type'           => $request->type,
                    'category'       => $request->category,
                    'amount'         => $request->amount,
                    'reference_type' => 'manual',
                    'reference_id'   => null,
                    'date'           => $request->date,
                    'notes'          => $request->notes ?: ($request->type === 'out' ? 'Mutasi kas keluar' : 'Mutasi kas masuk'),
                ]);
            });

            return redirect()->route('finance.index')
                ->with('success', 'Mutasi kas berhasil dicatat ke dalam buku kas.');

        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mencatat mutasi kas: ' . $e->getMessage());
        }
    }

    /**
     * Hapus catatan mutasi kas manual (Hanya diperbolehkan untuk reference_type: manual)
     */
    public function destroy(CashFlow $cashFlow)
    {
        if ($cashFlow->reference_type !== 'manual') {
            return redirect()->route('finance.index')
                ->with('error', 'Mutasi kas otomatis dari transaksi (' . ucfirst($cashFlow->reference_type) . ') tidak dapat dihapus manual dari buku kas. Kelola langsung dari modul sumbernya.');
        }

        try {
            $cashFlow->delete();

            return redirect()->route('finance.index')
                ->with('success', 'Catatan mutasi kas manual berhasil dihapus.');

        } catch (Exception $e) {
            return redirect()->route('finance.index')
                ->with('error', 'Gagal menghapus mutasi kas: ' . $e->getMessage());
        }
    }
}
