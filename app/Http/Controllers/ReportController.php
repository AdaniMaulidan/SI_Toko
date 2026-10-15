<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StoreSetting;
use App\Models\Supplier;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * 1. Laporan Penjualan (FASE 8.2)
     * Filter: tanggal, kasir, metode pembayaran
     */
    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->toDateString());

        $query = Sale::with(['user', 'customer', 'items'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        // Filter Kasir
        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }

        // Filter Metode Pembayaran
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        // Summary Cards
        $totalTransactions = (clone $query)->count();
        $totalGrossSales   = (float) (clone $query)->sum('subtotal');
        $totalDiscounts    = (float) (clone $query)->sum('discount');
        $totalNetSales      = (float) (clone $query)->sum('total_amount');

        // Total kuantitas barang terjual
        $totalQtySold = (int) SaleItem::whereHas('sale', function ($q) use ($startDate, $endDate, $request) {
            $q->whereDate('created_at', '>=', $startDate)
              ->whereDate('created_at', '<=', $endDate);
            if ($request->filled('user_id') && $request->user_id !== 'all') {
                $q->where('user_id', $request->user_id);
            }
            if ($request->filled('payment_method') && $request->payment_method !== 'all') {
                $q->where('payment_method', $request->payment_method);
            }
        })->sum('quantity');

        $averagePerTrx = $totalTransactions > 0 ? round($totalNetSales / $totalTransactions) : 0;

        $cashiers = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_CASHIER])->get();
        $sales = $query->latest()->paginate(20)->withQueryString();

        return view('reports.sales', compact(
            'sales',
            'cashiers',
            'totalTransactions',
            'totalGrossSales',
            'totalDiscounts',
            'totalNetSales',
            'totalQtySold',
            'averagePerTrx',
            'startDate',
            'endDate'
        ));
    }

    /**
     * 2. Laporan Pembelian / Kulakan (FASE 8.2)
     * Filter: periode/bulan, supplier, status pembayaran
     */
    public function purchases(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        [$year, $m] = explode('-', $month);

        $query = Purchase::with(['supplier', 'items'])
            ->whereYear('purchase_date', $year)
            ->whereMonth('purchase_date', $m);

        // Filter Supplier
        if ($request->filled('supplier_id') && $request->supplier_id !== 'all') {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter Status Pembayaran
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        // Summary Cards
        $totalPurchases = (float) (clone $query)->sum('total_amount');
        $totalPaid      = (float) (clone $query)->sum('paid_amount');
        $totalDebt      = max(0, $totalPurchases - $totalPaid);
        $totalCount     = (clone $query)->count();
        $paidCount      = (clone $query)->where('payment_status', 'paid')->count();
        $debtCount      = (clone $query)->whereIn('payment_status', ['partial', 'unpaid'])->count();

        $suppliers = Supplier::orderBy('name')->get();
        $purchases = $query->latest('purchase_date')->latest('id')->paginate(20)->withQueryString();

        return view('reports.purchases', compact(
            'purchases',
            'suppliers',
            'totalPurchases',
            'totalPaid',
            'totalDebt',
            'totalCount',
            'paidCount',
            'debtCount',
            'month'
        ));
    }

    /**
     * 3. Laporan Stok & Valuasi Aset Toko (FASE 8.2)
     * Filter: kategori, status stok (semua, habis, menipis, aman), urutan
     */
    public function stock(Request $request)
    {
        $query = Product::with(['category', 'unit'])->active();

        // Filter Kategori
        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }

        // Filter Status Stok
        if ($request->filled('status')) {
            if ($request->status === 'habis') {
                $query->where('stock', '<=', 0);
            } elseif ($request->status === 'menipis') {
                $query->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock');
            } elseif ($request->status === 'berlebih' || $request->status === 'aman') {
                $query->whereColumn('stock', '>', 'min_stock');
            }
        }

        // Pengurutan
        $sort = $request->input('sort', 'qty_asc');
        if ($sort === 'qty_asc') {
            $query->orderBy('stock', 'asc');
        } elseif ($sort === 'qty_desc') {
            $query->orderBy('stock', 'desc');
        } elseif ($sort === 'value_desc') {
            $query->orderByRaw('(stock * cost_price) desc');
        } else {
            $query->orderBy('name', 'asc');
        }

        // Summary Cards (hitung dari seluruh master produk aktif)
        $totalAssetValue = (float) Product::active()->selectRaw('SUM(stock * cost_price) as val')->value('val') ?? 0;
        $totalRetailValue = (float) Product::active()->selectRaw('SUM(stock * selling_price) as val')->value('val') ?? 0;
        $potentialProfit = max(0, $totalRetailValue - $totalAssetValue);

        $lowStockCount = Product::active()->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock')->count();
        $outOfStockCount = Product::active()->where('stock', '<=', 0)->count();
        $safeStockCount = Product::active()->whereColumn('stock', '>', 'min_stock')->count();

        $categories = Category::orderBy('name')->get();
        $products = $query->paginate(25)->withQueryString();

        return view('reports.stock', compact(
            'products',
            'categories',
            'totalAssetValue',
            'totalRetailValue',
            'potentialProfit',
            'lowStockCount',
            'outOfStockCount',
            'safeStockCount'
        ));
    }

    /**
     * 4. Laporan Produk Terlaris & Kinerja Produk (FASE 8.2)
     * Filter: tanggal mulai & akhir, kategori
     */
    public function products(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->toDateString());

        // Query agregasi penjualan item produk
        $query = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('units', 'products.unit_id', '=', 'units.id')
            ->whereDate('sales.created_at', '>=', $startDate)
            ->whereDate('sales.created_at', '<=', $endDate)
            ->select([
                'products.id as product_id',
                'products.barcode',
                'products.name as product_name',
                'categories.name as category_name',
                DB::raw('COALESCE(units.short_name, units.name) as unit_symbol'),
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM((sale_items.selling_price - sale_items.cost_price) * sale_items.quantity) as gross_profit'),
            ])
            ->groupBy('products.id', 'products.barcode', 'products.name', 'categories.name', 'units.short_name', 'units.name');

        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('products.category_id', $request->category_id);
        }

        // Top 10 Terlaris berdasarkan Volume Kuantitas
        $topByQty = (clone $query)->orderByDesc('total_qty')->take(10)->get();

        // Top 10 Terlaris berdasarkan Nilai Omset (Revenue)
        $topByRevenue = (clone $query)->orderByDesc('total_revenue')->take(10)->get();

        $categories = Category::orderBy('name')->get();

        return view('reports.products', compact(
            'topByQty',
            'topByRevenue',
            'categories',
            'startDate',
            'endDate'
        ));
    }

    /**
     * 5. Laporan Laba Rugi Komprehensif (FASE 8.2)
     * Format: Pendapatan Kotor - Diskon - HPP = Laba Kotor - Pengeluaran Operasional = Laba Bersih
     */
    public function profit(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        [$year, $m] = explode('-', $month);

        // 1. PENDAPATAN
        $grossSales = (float) Sale::whereYear('created_at', $year)
            ->whereMonth('created_at', $m)
            ->sum('subtotal');

        $totalDiscounts = (float) Sale::whereYear('created_at', $year)
            ->whereMonth('created_at', $m)
            ->sum('discount');

        $netSales = max(0, $grossSales - $totalDiscounts);

        // 2. HARGA POKOK PENJUALAN (HPP)
        $totalHpp = (float) (SaleItem::whereHas('sale', function ($q) use ($year, $m) {
            $q->whereYear('created_at', $year)->whereMonth('created_at', $m);
        })->selectRaw('SUM(cost_price * quantity) as hpp')->value('hpp') ?? 0);

        // 3. LABA KOTOR (GROSS PROFIT)
        $grossProfit = $netSales - $totalHpp;

        // 4. BIAYA OPERASIONAL (EXPENSES) PER KATEGORI
        $expensesByCategory = Expense::whereYear('date', $year)
            ->whereMonth('date', $m)
            ->select('expense_category', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category')
            ->orderByDesc('total')
            ->get();

        $totalExpenses = (float) $expensesByCategory->sum('total');

        // 5. LABA BERSIH (NET PROFIT)
        $netProfit = $grossProfit - $totalExpenses;

        // Margin Keuntungan Bersih (%)
        $profitMargin = $netSales > 0 ? round(($netProfit / $netSales) * 100, 1) : 0;

        return view('reports.profit', compact(
            'grossSales',
            'totalDiscounts',
            'netSales',
            'totalHpp',
            'grossProfit',
            'expensesByCategory',
            'totalExpenses',
            'netProfit',
            'profitMargin',
            'month'
        ));
    }

    /**
     * 6. Laporan Arus Kas (Cash Flow Report) (FASE 8.2)
     * Format: Saldo Awal, Rincian Kas Masuk, Rincian Kas Keluar, Saldo Akhir
     */
    public function cash(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // 1. Saldo Awal Kas (Semua mutasi sebelum tanggal mulai)
        $priorInflow  = (float) CashFlow::where('type', 'in')->whereDate('date', '<', $startDate)->sum('amount');
        $priorOutflow = (float) CashFlow::where('type', 'out')->whereDate('date', '<', $startDate)->sum('amount');
        $openingBalance = $priorInflow - $priorOutflow;

        // 2. Arus Kas Masuk Periode Ini Berdasarkan Kategori
        $inflowsByCategory = CashFlow::where('type', 'in')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();
        $totalInflow = (float) $inflowsByCategory->sum('total');

        // 3. Arus Kas Keluar Periode Ini Berdasarkan Kategori
        $outflowsByCategory = CashFlow::where('type', 'out')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();
        $totalOutflow = (float) $outflowsByCategory->sum('total');

        // 4. Saldo Bersih Periode & Saldo Akhir
        $netCashChange = $totalInflow - $totalOutflow;
        $closingBalance = $openingBalance + $netCashChange;

        return view('reports.cash', compact(
            'openingBalance',
            'inflowsByCategory',
            'totalInflow',
            'outflowsByCategory',
            'totalOutflow',
            'netCashChange',
            'closingBalance',
            'startDate',
            'endDate'
        ));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // FASE 8.3: METODE EKSPOR LAPORAN (EXCEL / CSV & FORMAL PDF)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Ekspor Laporan Penjualan ke Excel / CSV
     */
    public function exportSalesExcel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->toDateString());

        $query = Sale::with(['user', 'customer', 'items'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->latest()->get();

        $headers = ['No', 'Tanggal', 'Jam', 'No Faktur', 'Kasir', 'Pelanggan', 'Jumlah Item', 'Metode Bayar', 'Subtotal', 'Diskon', 'Total Akhir'];
        $rows = [];
        foreach ($sales as $idx => $s) {
            $rows[] = [
                $idx + 1,
                $s->created_at->format('Y-m-d'),
                $s->created_at->format('H:i'),
                $s->invoice_number,
                $s->user?->name ?? 'Kasir',
                $s->customer?->name ?? 'Umum',
                $s->items->sum('quantity'),
                strtoupper($s->payment_method),
                $s->subtotal,
                $s->discount,
                $s->total_amount,
            ];
        }

        return $this->sendCsvDownload("laporan-penjualan-{$startDate}-sd-{$endDate}.csv", $headers, $rows);
    }

    /**
     * Ekspor Laporan Penjualan ke PDF Formal
     */
    public function exportSalesPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->toDateString());

        $query = Sale::with(['user', 'customer', 'items'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->latest()->get();
        $totalTransactions = $sales->count();
        $totalGrossSales   = (float) $sales->sum('subtotal');
        $totalDiscounts    = (float) $sales->sum('discount');
        $totalNetSales     = (float) $sales->sum('total_amount');
        $totalQtySold      = (int) $sales->sum(fn($s) => $s->items->sum('quantity'));
        $setting           = StoreSetting::getSettings();

        $pdf = Pdf::loadView('reports.pdf.sales', compact(
            'sales',
            'totalTransactions',
            'totalGrossSales',
            'totalDiscounts',
            'totalNetSales',
            'totalQtySold',
            'startDate',
            'endDate',
            'setting'
        ))->setPaper('a4', 'landscape');

        return $pdf->download("laporan-penjualan-{$startDate}-sd-{$endDate}.pdf");
    }

    /**
     * Ekspor Laporan Pembelian ke Excel / CSV
     */
    public function exportPurchasesExcel(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        [$year, $m] = explode('-', $month);

        $query = Purchase::with(['supplier', 'items'])
            ->whereYear('purchase_date', $year)
            ->whereMonth('purchase_date', $m);

        if ($request->filled('supplier_id') && $request->supplier_id !== 'all') {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        $purchases = $query->latest('purchase_date')->get();

        $headers = ['No', 'Tanggal', 'No Faktur PO', 'Supplier', 'Item Masuk', 'Status Bayar', 'Total Biaya', 'Sudah Dibayar', 'Sisa Hutang'];
        $rows = [];
        foreach ($purchases as $idx => $p) {
            $rows[] = [
                $idx + 1,
                $p->purchase_date->format('Y-m-d'),
                $p->invoice_number,
                $p->supplier?->name ?? 'Supplier Umum',
                $p->items->sum('quantity'),
                strtoupper($p->payment_status),
                $p->total_amount,
                $p->paid_amount,
                max(0, $p->total_amount - $p->paid_amount),
            ];
        }

        return $this->sendCsvDownload("laporan-pembelian-{$month}.csv", $headers, $rows);
    }

    /**
     * Ekspor Laporan Pembelian ke PDF Formal
     */
    public function exportPurchasesPdf(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        [$year, $m] = explode('-', $month);

        $query = Purchase::with(['supplier', 'items'])
            ->whereYear('purchase_date', $year)
            ->whereMonth('purchase_date', $m);

        if ($request->filled('supplier_id') && $request->supplier_id !== 'all') {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        $purchases      = $query->latest('purchase_date')->get();
        $totalPurchases = (float) $purchases->sum('total_amount');
        $totalPaid      = (float) $purchases->sum('paid_amount');
        $totalDebt      = max(0, $totalPurchases - $totalPaid);
        $totalCount     = $purchases->count();
        $setting        = StoreSetting::getSettings();

        $pdf = Pdf::loadView('reports.pdf.purchases', compact(
            'purchases',
            'totalPurchases',
            'totalPaid',
            'totalDebt',
            'totalCount',
            'month',
            'setting'
        ))->setPaper('a4', 'landscape');

        return $pdf->download("laporan-pembelian-{$month}.pdf");
    }

    /**
     * Ekspor Laporan Stok ke Excel / CSV
     */
    public function exportStockExcel(Request $request)
    {
        $query = Product::with(['category', 'unit'])->active();

        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('status')) {
            if ($request->status === 'habis') {
                $query->where('stock', '<=', 0);
            } elseif ($request->status === 'menipis') {
                $query->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock');
            } elseif ($request->status === 'berlebih' || $request->status === 'aman') {
                $query->whereColumn('stock', '>', 'min_stock');
            }
        }

        $products = $query->orderBy('name')->get();

        $headers = ['No', 'Barcode', 'Nama Produk', 'Kategori', 'Satuan', 'Harga Beli (HPP)', 'Harga Jual', 'Sisa Stok', 'Min Stok', 'Nilai Aset Modal', 'Status'];
        $rows = [];
        foreach ($products as $idx => $prod) {
            $status = $prod->stock <= 0 ? 'HABIS' : ($prod->stock <= $prod->min_stock ? 'MENIPIS' : 'AMAN');
            $rows[] = [
                $idx + 1,
                $prod->barcode,
                $prod->name,
                $prod->category->name ?? '-',
                $prod->unit->symbol ?? 'Pcs',
                $prod->cost_price,
                $prod->selling_price,
                $prod->stock,
                $prod->min_stock,
                $prod->stock * $prod->cost_price,
                $status,
            ];
        }

        return $this->sendCsvDownload('laporan-stok-valuasi-aset-' . date('Y-m-d') . '.csv', $headers, $rows);
    }

    /**
     * Ekspor Laporan Stok ke PDF Formal
     */
    public function exportStockPdf(Request $request)
    {
        $query = Product::with(['category', 'unit'])->active();

        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('status')) {
            if ($request->status === 'habis') {
                $query->where('stock', '<=', 0);
            } elseif ($request->status === 'menipis') {
                $query->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock');
            } elseif ($request->status === 'berlebih' || $request->status === 'aman') {
                $query->whereColumn('stock', '>', 'min_stock');
            }
        }

        $products         = $query->orderBy('name')->get();
        $totalAssetValue  = (float) $products->sum(fn($p) => $p->stock * $p->cost_price);
        $totalRetailValue = (float) $products->sum(fn($p) => $p->stock * $p->selling_price);
        $lowStockCount    = $products->where('stock', '>', 0)->filter(fn($p) => $p->stock <= $p->min_stock)->count();
        $outOfStockCount  = $products->where('stock', '<=', 0)->count();
        $setting          = StoreSetting::getSettings();

        $pdf = Pdf::loadView('reports.pdf.stock', compact(
            'products',
            'totalAssetValue',
            'totalRetailValue',
            'lowStockCount',
            'outOfStockCount',
            'setting'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('laporan-stok-valuasi-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Ekspor Laporan Produk Terlaris ke Excel / CSV
     */
    public function exportProductsExcel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->toDateString());

        $query = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('units', 'products.unit_id', '=', 'units.id')
            ->whereDate('sales.created_at', '>=', $startDate)
            ->whereDate('sales.created_at', '<=', $endDate)
            ->select([
                'products.barcode',
                'products.name as product_name',
                'categories.name as category_name',
                DB::raw('COALESCE(units.short_name, units.name) as unit_symbol'),
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM((sale_items.selling_price - sale_items.cost_price) * sale_items.quantity) as gross_profit'),
            ])
            ->groupBy('products.id', 'products.barcode', 'products.name', 'categories.name', 'units.short_name', 'units.name')
            ->orderByDesc('total_qty');

        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('products.category_id', $request->category_id);
        }

        $items = $query->get();

        $headers = ['Rank', 'Barcode', 'Nama Produk', 'Kategori', 'Satuan', 'Kuantitas Terjual', 'Total Omset Penjualan', 'Laba Kotor'];
        $rows = [];
        foreach ($items as $idx => $it) {
            $rows[] = [
                $idx + 1,
                $it->barcode,
                $it->product_name,
                $it->category_name ?? '-',
                $it->unit_symbol ?? 'Pcs',
                $it->total_qty,
                $it->total_revenue,
                $it->gross_profit,
            ];
        }

        return $this->sendCsvDownload("laporan-produk-terlaris-{$startDate}-sd-{$endDate}.csv", $headers, $rows);
    }

    /**
     * Ekspor Laporan Produk Terlaris ke PDF Formal
     */
    public function exportProductsPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->toDateString());

        $query = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('units', 'products.unit_id', '=', 'units.id')
            ->whereDate('sales.created_at', '>=', $startDate)
            ->whereDate('sales.created_at', '<=', $endDate)
            ->select([
                'products.barcode',
                'products.name as product_name',
                'categories.name as category_name',
                DB::raw('COALESCE(units.short_name, units.name) as unit_symbol'),
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM((sale_items.selling_price - sale_items.cost_price) * sale_items.quantity) as gross_profit'),
            ])
            ->groupBy('products.id', 'products.barcode', 'products.name', 'categories.name', 'units.short_name', 'units.name');

        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('products.category_id', $request->category_id);
        }

        $topByQty     = (clone $query)->orderByDesc('total_qty')->take(10)->get();
        $topByRevenue = (clone $query)->orderByDesc('total_revenue')->take(10)->get();
        $setting      = StoreSetting::getSettings();

        $pdf = Pdf::loadView('reports.pdf.products', compact(
            'topByQty',
            'topByRevenue',
            'startDate',
            'endDate',
            'setting'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("laporan-produk-terlaris-{$startDate}-sd-{$endDate}.pdf");
    }

    /**
     * Ekspor Laporan Laba Rugi ke Excel / CSV
     */
    public function exportProfitExcel(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        [$year, $m] = explode('-', $month);

        $grossSales = (float) Sale::whereYear('created_at', $year)->whereMonth('created_at', $m)->sum('subtotal');
        $totalDiscounts = (float) Sale::whereYear('created_at', $year)->whereMonth('created_at', $m)->sum('discount');
        $netSales = max(0, $grossSales - $totalDiscounts);

        $totalHpp = (float) (SaleItem::whereHas('sale', function ($q) use ($year, $m) {
            $q->whereYear('created_at', $year)->whereMonth('created_at', $m);
        })->selectRaw('SUM(cost_price * quantity) as hpp')->value('hpp') ?? 0);

        $grossProfit = $netSales - $totalHpp;

        $expensesByCategory = Expense::whereYear('date', $year)
            ->whereMonth('date', $m)
            ->select('expense_category', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category')
            ->orderByDesc('total')
            ->get();
        $totalExpenses = (float) $expensesByCategory->sum('total');

        $netProfit = $grossProfit - $totalExpenses;
        $profitMargin = $netSales > 0 ? round(($netProfit / $netSales) * 100, 1) : 0;

        $headers = ['Komponen Keuangan', 'Nominal (Rp)'];
        $rows = [
            ['1. PENDAPATAN (REVENUE)', ''],
            ['Total Penjualan Kotor (Gross Sales)', $grossSales],
            ['Potongan / Diskon Penjualan', '-' . $totalDiscounts],
            ['Total Pendapatan Bersih (Net Sales)', $netSales],
            ['', ''],
            ['2. BEBAN POKOK PENJUALAN (HPP)', ''],
            ['Total HPP Barang Terjual (COGS)', '-' . $totalHpp],
            ['LABA KOTOR (GROSS PROFIT)', $grossProfit],
            ['', ''],
            ['3. BEBAN OPERASIONAL (EXPENSES)', ''],
        ];

        foreach ($expensesByCategory as $exp) {
            $rows[] = ['Beban ' . $exp->expense_category, '-' . $exp->total];
        }

        $rows[] = ['Total Biaya Operasional', '-' . $totalExpenses];
        $rows[] = ['', ''];
        $rows[] = ['LABA BERSIH (NET PROFIT)', $netProfit];
        $rows[] = ['Margin Laba Bersih (%)', $profitMargin . '%'];

        return $this->sendCsvDownload("laporan-laba-rugi-{$month}.csv", $headers, $rows);
    }

    /**
     * Ekspor Laporan Laba Rugi ke PDF Formal
     */
    public function exportProfitPdf(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        [$year, $m] = explode('-', $month);

        $grossSales = (float) Sale::whereYear('created_at', $year)->whereMonth('created_at', $m)->sum('subtotal');
        $totalDiscounts = (float) Sale::whereYear('created_at', $year)->whereMonth('created_at', $m)->sum('discount');
        $netSales = max(0, $grossSales - $totalDiscounts);

        $totalHpp = (float) (SaleItem::whereHas('sale', function ($q) use ($year, $m) {
            $q->whereYear('created_at', $year)->whereMonth('created_at', $m);
        })->selectRaw('SUM(cost_price * quantity) as hpp')->value('hpp') ?? 0);

        $grossProfit = $netSales - $totalHpp;

        $expensesByCategory = Expense::whereYear('date', $year)
            ->whereMonth('date', $m)
            ->select('expense_category', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category')
            ->orderByDesc('total')
            ->get();
        $totalExpenses = (float) $expensesByCategory->sum('total');

        $netProfit = $grossProfit - $totalExpenses;
        $profitMargin = $netSales > 0 ? round(($netProfit / $netSales) * 100, 1) : 0;
        $setting = StoreSetting::getSettings();

        $pdf = Pdf::loadView('reports.pdf.profit', compact(
            'grossSales',
            'totalDiscounts',
            'netSales',
            'totalHpp',
            'grossProfit',
            'expensesByCategory',
            'totalExpenses',
            'netProfit',
            'profitMargin',
            'month',
            'setting'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("laporan-laba-rugi-{$month}.pdf");
    }

    /**
     * Ekspor Laporan Arus Kas ke Excel / CSV
     */
    public function exportCashExcel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $priorInflow  = (float) CashFlow::where('type', 'in')->whereDate('date', '<', $startDate)->sum('amount');
        $priorOutflow = (float) CashFlow::where('type', 'out')->whereDate('date', '<', $startDate)->sum('amount');
        $openingBalance = $priorInflow - $priorOutflow;

        $inflowsByCategory = CashFlow::where('type', 'in')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();
        $totalInflow = (float) $inflowsByCategory->sum('total');

        $outflowsByCategory = CashFlow::where('type', 'out')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();
        $totalOutflow = (float) $outflowsByCategory->sum('total');

        $netCashChange  = $totalInflow - $totalOutflow;
        $closingBalance = $openingBalance + $netCashChange;

        $headers = ['Komponen Arus Kas', 'Nominal (Rp)'];
        $rows = [
            ['SALDO AWAL KAS', $openingBalance],
            ['', ''],
            ['1. ARUS KAS MASUK (INFLOW)', ''],
        ];

        foreach ($inflowsByCategory as $in) {
            $rows[] = ['Penerimaan ' . $in->category, '+' . $in->total];
        }
        $rows[] = ['TOTAL ARUS KAS MASUK', '+' . $totalInflow];
        $rows[] = ['', ''];
        $rows[] = ['2. ARUS KAS KELUAR (OUTFLOW)', ''];

        foreach ($outflowsByCategory as $out) {
            $rows[] = ['Pengeluaran ' . $out->category, '-' . $out->total];
        }
        $rows[] = ['TOTAL ARUS KAS KELUAR', '-' . $totalOutflow];
        $rows[] = ['', ''];
        $rows[] = ['PERUBAHAN BERSIH KAS (NET CHANGE)', $netCashChange];
        $rows[] = ['SALDO AKHIR KAS (CASH ON HAND)', $closingBalance];

        return $this->sendCsvDownload("laporan-arus-kas-{$startDate}-sd-{$endDate}.csv", $headers, $rows);
    }

    /**
     * Ekspor Laporan Arus Kas ke PDF Formal
     */
    public function exportCashPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $priorInflow  = (float) CashFlow::where('type', 'in')->whereDate('date', '<', $startDate)->sum('amount');
        $priorOutflow = (float) CashFlow::where('type', 'out')->whereDate('date', '<', $startDate)->sum('amount');
        $openingBalance = $priorInflow - $priorOutflow;

        $inflowsByCategory = CashFlow::where('type', 'in')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();
        $totalInflow = (float) $inflowsByCategory->sum('total');

        $outflowsByCategory = CashFlow::where('type', 'out')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();
        $totalOutflow = (float) $outflowsByCategory->sum('total');

        $netCashChange  = $totalInflow - $totalOutflow;
        $closingBalance = $openingBalance + $netCashChange;
        $setting        = StoreSetting::getSettings();

        $pdf = Pdf::loadView('reports.pdf.cash', compact(
            'openingBalance',
            'inflowsByCategory',
            'totalInflow',
            'outflowsByCategory',
            'totalOutflow',
            'netCashChange',
            'closingBalance',
            'startDate',
            'endDate',
            'setting'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("laporan-arus-kas-{$startDate}-sd-{$endDate}.pdf");
    }

    /**
     * Helper untuk streaming download file CSV dengan encoding UTF-8 BOM untuk kompatibilitas Excel
     */
    protected function sendCsvDownload(string $filename, array $headers, array $rows)
    {
        $callback = function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            // Menulis UTF-8 BOM agar Excel menampilkan aksen/karakter dan angka dengan benar
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-store, no-cache',
        ]);
    }
}

