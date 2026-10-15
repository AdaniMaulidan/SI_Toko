<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Real-Time dengan metrik bisnis toko (FASE 8.1)
     */
    public function index(Request $request)
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // ── 1. Metrik Omset Penjualan ──────────────────────────────────────
        $salesToday = (float) Sale::today()->sum('total_amount');
        $salesYesterday = (float) Sale::whereDate('created_at', $yesterday)->sum('total_amount');
        $salesThisMonth = (float) Sale::thisMonth()->sum('total_amount');

        // Persentase perubahan penjualan hari ini vs kemarin
        $salesChangePercent = 0;
        if ($salesYesterday > 0) {
            $salesChangePercent = round((($salesToday - $salesYesterday) / $salesYesterday) * 100, 1);
        }

        // ── 2. Metrik Jumlah Transaksi ─────────────────────────────────────
        $transactionsToday = Sale::today()->count();
        $transactionsYesterday = Sale::whereDate('created_at', $yesterday)->count();
        $transactionsThisMonth = Sale::thisMonth()->count();
        $transactionsDiff = $transactionsToday - $transactionsYesterday;

        // ── 3. Metrik Keuntungan Bersih (Laba Kotor - Pengeluaran) ─────────
        // A. Hari Ini
        $grossProfitToday = (float) (SaleItem::whereHas('sale', fn($q) => $q->today())
            ->selectRaw('SUM((selling_price - cost_price) * quantity) as gross_profit')
            ->value('gross_profit') ?? 0);
        $discountToday = (float) Sale::today()->sum('discount');
        $grossProfitToday = max(0, $grossProfitToday - $discountToday);
        $expenseToday = (float) Expense::whereDate('date', $today)->sum('amount');
        $netProfitToday = $grossProfitToday - $expenseToday;

        // Kemarin untuk perbandingan keuntungan
        $grossProfitYesterday = (float) (SaleItem::whereHas('sale', fn($q) => $q->whereDate('created_at', $yesterday))
            ->selectRaw('SUM((selling_price - cost_price) * quantity) as gross_profit')
            ->value('gross_profit') ?? 0);
        $discountYesterday = (float) Sale::whereDate('created_at', $yesterday)->sum('discount');
        $grossProfitYesterday = max(0, $grossProfitYesterday - $discountYesterday);
        $expenseYesterday = (float) Expense::whereDate('date', $yesterday)->sum('amount');
        $netProfitYesterday = $grossProfitYesterday - $expenseYesterday;

        $profitChangePercent = 0;
        if ($netProfitYesterday > 0) {
            $profitChangePercent = round((($netProfitToday - $netProfitYesterday) / $netProfitYesterday) * 100, 1);
        }

        // B. Bulan Ini
        $grossProfitThisMonth = (float) (SaleItem::whereHas('sale', fn($q) => $q->thisMonth())
            ->selectRaw('SUM((selling_price - cost_price) * quantity) as gross_profit')
            ->value('gross_profit') ?? 0);
        $discountThisMonth = (float) Sale::thisMonth()->sum('discount');
        $grossProfitThisMonth = max(0, $grossProfitThisMonth - $discountThisMonth);
        $expenseThisMonth = (float) Expense::thisMonth()->sum('amount');
        $netProfitThisMonth = $grossProfitThisMonth - $expenseThisMonth;

        // ── 4. Metrik Produk & Peringatan Stok (Low Stock Alert) ───────────
        $totalProducts = Product::active()->count();
        $outOfStockCount = Product::active()->where('stock', '<=', 0)->count();
        $lowStockCount = Product::active()
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        // Daftar produk stok kritis (habis & menipis) untuk panel notifikasi
        $criticalStockProducts = Product::active()
            ->with(['unit', 'category'])
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        // ── 5. Ringkasan Kas Toko Hari Ini & Riil ──────────────────────────
        $cashInToday = (float) CashFlow::whereDate('date', $today)->where('type', 'in')->sum('amount');
        $cashOutToday = (float) CashFlow::whereDate('date', $today)->where('type', 'out')->sum('amount');
        $netCashToday = $cashInToday - $cashOutToday;

        $totalCashIn = (float) CashFlow::where('type', 'in')->sum('amount');
        $totalCashOut = (float) CashFlow::where('type', 'out')->sum('amount');
        $currentRealBalance = $totalCashIn - $totalCashOut;

        // ── 6. Transaksi Terbaru (Recent Transactions) ────────────────────
        $recentTransactions = Sale::with(['user', 'customer'])
            ->latest()
            ->take(6)
            ->get();

        // ── 7. Data Grafik Penjualan Mingguan & Bulanan ────────────────────
        // A. 7 Hari Terakhir
        $chart7DaysLabels = [];
        $chart7DaysData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chart7DaysLabels[] = $date->translatedFormat('D, d M');
            $chart7DaysData[] = (float) Sale::whereDate('created_at', $date)->sum('total_amount');
        }

        // B. 30 Hari Terakhir
        $chart30DaysLabels = [];
        $chart30DaysData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chart30DaysLabels[] = $date->translatedFormat('d M');
            $chart30DaysData[] = (float) Sale::whereDate('created_at', $date)->sum('total_amount');
        }

        // C. Bulan Berjalan (per minggu atau per hari di bulan ini)
        $startOfMonth = Carbon::now()->startOfMonth();
        $daysInMonth = Carbon::now()->day;
        $chartMonthLabels = [];
        $chartMonthData = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Carbon::create(Carbon::now()->year, Carbon::now()->month, $d);
            $chartMonthLabels[] = $date->translatedFormat('d M');
            $chartMonthData[] = (float) Sale::whereDate('created_at', $date)->sum('total_amount');
        }

        $chartData = [
            'seven_days' => [
                'labels' => $chart7DaysLabels,
                'data'   => $chart7DaysData,
            ],
            'thirty_days' => [
                'labels' => $chart30DaysLabels,
                'data'   => $chart30DaysData,
            ],
            'this_month' => [
                'labels' => $chartMonthLabels,
                'data'   => $chartMonthData,
            ],
        ];

        return view('dashboard.index', compact(
            'salesToday',
            'salesYesterday',
            'salesThisMonth',
            'salesChangePercent',
            'transactionsToday',
            'transactionsYesterday',
            'transactionsThisMonth',
            'transactionsDiff',
            'netProfitToday',
            'netProfitThisMonth',
            'profitChangePercent',
            'totalProducts',
            'outOfStockCount',
            'lowStockCount',
            'criticalStockProducts',
            'cashInToday',
            'cashOutToday',
            'netCashToday',
            'currentRealBalance',
            'recentTransactions',
            'chartData'
        ));
    }
}
