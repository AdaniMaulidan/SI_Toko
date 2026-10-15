<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Expense;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * Daftar pengeluaran operasional dengan filter bulan, kategori, dan pencarian (FASE 7.1)
     */
    public function index(Request $request)
    {
        $query = Expense::with('user');

        // 1. Filter Bulan (format: Y-m)
        if ($request->filled('month')) {
            [$year, $month] = explode('-', $request->month);
            $query->whereYear('date', $year)->whereMonth('date', $month);
        }

        // 2. Filter Kategori
        if ($request->filled('category')) {
            $query->where('expense_category', $request->category);
        }

        // 3. Pencarian (keterangan / kode)
        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('description', 'like', "%{$keyword}%")
                  ->orWhere('code', 'like', "%{$keyword}%");
            });
        }

        // Statistik ringkasan berdasarkan filter aktif
        $totalFiltered = (float) (clone $query)->sum('amount');
        $countFiltered = (clone $query)->count();

        // Statistik bulan ini (selalu pakai bulan berjalan)
        $totalThisMonth = (float) Expense::thisMonth()->sum('amount');

        // Kategori tertinggi bulan ini
        $topCategory = Expense::thisMonth()
            ->select('expense_category', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category')
            ->orderByDesc('total')
            ->first();

        $stats = [
            'total_filtered'   => $totalFiltered,
            'count_filtered'   => $countFiltered,
            'total_this_month' => $totalThisMonth,
            'top_category'     => $topCategory?->expense_category ?? '-',
        ];

        // Daftar kategori unik untuk filter dropdown
        $categories = Expense::select('expense_category')
            ->distinct()
            ->orderBy('expense_category')
            ->pluck('expense_category');

        $expenses = $query->latest('date')->latest('id')->paginate(15)->withQueryString();

        return view('expenses.index', compact('expenses', 'stats', 'categories'));
    }

    /**
     * Simpan pengeluaran operasional baru + upload bukti struk (FASE 7.1)
     */
    public function store(Request $request)
    {
        $request->validate([
            'date'             => 'required|date',
            'expense_category' => 'required|string|max:100',
            'amount'           => 'required|numeric|min:1',
            'description'      => 'required|string|max:1000',
            'receipt_image'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'date.required'             => 'Tanggal pengeluaran wajib diisi.',
            'expense_category.required' => 'Kategori pengeluaran wajib dipilih.',
            'amount.required'           => 'Nominal pengeluaran wajib diisi.',
            'amount.min'                => 'Nominal pengeluaran minimal Rp 1.',
            'description.required'      => 'Keterangan pengeluaran wajib diisi.',
            'receipt_image.image'       => 'File harus berupa gambar.',
            'receipt_image.mimes'       => 'Format gambar: JPG, PNG, atau WebP.',
            'receipt_image.max'         => 'Ukuran gambar maksimal 2MB.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Generate kode pengeluaran unik: EXP-YYYYMMDD-XXXX
                $datePrefix = date('Ymd', strtotime($request->date));
                $countToday = Expense::whereDate('date', $request->date)->count() + 1;
                $code = 'EXP-' . $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
                while (Expense::where('code', $code)->exists()) {
                    $countToday++;
                    $code = 'EXP-' . $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
                }

                // Upload bukti struk/nota jika ada
                $receiptPath = null;
                if ($request->hasFile('receipt_image')) {
                    $receiptPath = $request->file('receipt_image')
                        ->store('expenses/receipts', 'public');
                }

                // Simpan data pengeluaran
                $expense = Expense::create([
                    'code'             => $code,
                    'user_id'          => Auth::id(),
                    'expense_category' => $request->expense_category,
                    'amount'           => $request->amount,
                    'date'             => $request->date,
                    'description'      => $request->description,
                    'receipt_image'    => $receiptPath,
                ]);

                // Catat arus kas keluar (Cash Out)
                CashFlow::create([
                    'user_id'        => Auth::id(),
                    'type'           => 'out',
                    'category'       => 'Pengeluaran',
                    'amount'         => $request->amount,
                    'reference_type' => 'expense',
                    'reference_id'   => $expense->id,
                    'date'           => $request->date,
                    'notes'          => "Pengeluaran [{$request->expense_category}] - {$request->description} ({$code})",
                ]);
            });

            return redirect()->route('expenses.index')
                ->with('success', 'Pengeluaran berhasil dicatat dan tercatat di arus kas.');

        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan pengeluaran: ' . $e->getMessage());
        }
    }

    /**
     * Update data pengeluaran operasional (FASE 7.1)
     */
    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'date'             => 'required|date',
            'expense_category' => 'required|string|max:100',
            'amount'           => 'required|numeric|min:1',
            'description'      => 'required|string|max:1000',
            'receipt_image'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'date.required'             => 'Tanggal pengeluaran wajib diisi.',
            'expense_category.required' => 'Kategori pengeluaran wajib dipilih.',
            'amount.required'           => 'Nominal pengeluaran wajib diisi.',
            'amount.min'                => 'Nominal pengeluaran minimal Rp 1.',
            'description.required'      => 'Keterangan pengeluaran wajib diisi.',
            'receipt_image.image'       => 'File harus berupa gambar.',
            'receipt_image.mimes'       => 'Format gambar: JPG, PNG, atau WebP.',
            'receipt_image.max'         => 'Ukuran gambar maksimal 2MB.',
        ]);

        try {
            DB::transaction(function () use ($request, $expense) {
                $oldAmount = (float) $expense->amount;

                // Upload bukti baru jika ada — hapus yang lama
                if ($request->hasFile('receipt_image')) {
                    if ($expense->receipt_image) {
                        Storage::disk('public')->delete($expense->receipt_image);
                    }
                    $expense->receipt_image = $request->file('receipt_image')
                        ->store('expenses/receipts', 'public');
                }

                // Update data pengeluaran
                $expense->update([
                    'expense_category' => $request->expense_category,
                    'amount'           => $request->amount,
                    'date'             => $request->date,
                    'description'      => $request->description,
                    'receipt_image'    => $expense->receipt_image,
                ]);

                // Update arus kas yang terkait
                CashFlow::where('reference_type', 'expense')
                    ->where('reference_id', $expense->id)
                    ->update([
                        'amount'   => $request->amount,
                        'date'     => $request->date,
                        'notes'    => "Pengeluaran [{$request->expense_category}] - {$request->description} ({$expense->code})",
                    ]);
            });

            return redirect()->route('expenses.index')
                ->with('success', 'Data pengeluaran berhasil diperbarui.');

        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui pengeluaran: ' . $e->getMessage());
        }
    }

    /**
     * Hapus data pengeluaran operasional (FASE 7.1)
     */
    public function destroy(Expense $expense)
    {
        try {
            DB::transaction(function () use ($expense) {
                // Hapus gambar bukti struk jika ada
                if ($expense->receipt_image) {
                    Storage::disk('public')->delete($expense->receipt_image);
                }

                // Hapus arus kas terkait
                CashFlow::where('reference_type', 'expense')
                    ->where('reference_id', $expense->id)
                    ->delete();

                // Hapus record pengeluaran
                $expense->delete();
            });

            return redirect()->route('expenses.index')
                ->with('success', 'Data pengeluaran berhasil dihapus.');

        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus data pengeluaran: ' . $e->getMessage());
        }
    }

    /**
     * Endpoint JSON untuk mendapatkan detail pengeluaran via AJAX (untuk modal edit)
     */
    public function detail(Expense $expense)
    {
        return response()->json([
            'success' => true,
            'expense' => [
                'id'               => $expense->id,
                'code'             => $expense->code,
                'date'             => $expense->date->format('Y-m-d'),
                'expense_category' => $expense->expense_category,
                'amount'           => (float) $expense->amount,
                'description'      => $expense->description,
                'receipt_image'    => $expense->receipt_image
                    ? asset('storage/' . $expense->receipt_image)
                    : null,
                'user_name'        => $expense->user?->name ?? '-',
                'created_at'       => $expense->created_at->format('d/m/Y H:i'),
            ],
        ]);
    }
}
