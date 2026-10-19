<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| SI Toko - Web Routes
|--------------------------------------------------------------------------
*/

// ── Authentication ──────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',                 [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login',                [AuthController::class, 'login'])->name('login.submit');
    Route::get('/auth/login',            fn() => redirect()->route('login'))->name('auth.login');
    Route::post('/auth/login',           [AuthController::class, 'login'])->name('auth.login.submit');
});

Route::post('/logout',      [AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth');

// ── Main App (Protected with auth middleware) ─────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard (FASE 8.1)
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil Akun & Foto Profil (Akses: Semua Pengguna Terautentikasi)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',             [ProfileController::class, 'index'])->name('index');
        Route::put('/',             [ProfileController::class, 'update'])->name('update');
        Route::delete('/image',     [ProfileController::class, 'removeImage'])->name('remove-image');
        Route::put('/password',     [ProfileController::class, 'updatePassword'])->name('update-password');
    });

    // Kasir / POS (Akses: Administrator & Kasir)
    Route::middleware(['role:admin,cashier'])->group(function () {
        Route::get('/kasir',                 [PosController::class, 'index'])->name('kasir.index');
        Route::get('/kasir/search-products', [PosController::class, 'searchProducts'])->name('kasir.search');
        Route::post('/kasir/checkout',       [PosController::class, 'checkout'])->name('kasir.checkout');
        Route::get('/kasir/receipt/{sale}',      [PosController::class, 'printReceipt'])->name('kasir.receipt');
        Route::get('/kasir/receipt/{sale}/pdf',  [PosController::class, 'downloadReceiptPdf'])->name('kasir.receipt.pdf');
    });

    // Riwayat Transaksi (FASE 4.5)
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/',             [TransactionController::class, 'index'])->name('index');
        Route::get('/{id}',         [TransactionController::class, 'show'])->name('show');
        Route::get('/{id}/detail',  [TransactionController::class, 'detail'])->name('detail');
    });

    // Produk (Akses: Administrator & Gudang/Staf)
    Route::prefix('products')->name('products.')->middleware(['role:admin,staff'])->group(function () {
        Route::get('/',                   [ProductController::class, 'index'])->name('index');
        Route::get('/create',             [ProductController::class, 'create'])->name('create');
        Route::post('/',                  [ProductController::class, 'store'])->name('store');
        Route::get('/{product}',          [ProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit',     [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}',          [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}',       [ProductController::class, 'destroy'])->name('destroy');
    });

    // Kategori (Akses: Administrator & Gudang/Staf)
    Route::prefix('categories')->name('categories.')->middleware(['role:admin,staff'])->group(function () {
        Route::get('/',                  [CategoryController::class, 'index'])->name('index');
        Route::post('/',                 [CategoryController::class, 'store'])->name('store');
        Route::put('/{category}',        [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}',     [CategoryController::class, 'destroy'])->name('destroy');
    });

    // Satuan (Akses: Administrator & Gudang/Staf)
    Route::prefix('units')->name('units.')->middleware(['role:admin,staff'])->group(function () {
        Route::get('/',              [UnitController::class, 'index'])->name('index');
        Route::post('/',             [UnitController::class, 'store'])->name('store');
        Route::put('/{unit}',        [UnitController::class, 'update'])->name('update');
        Route::delete('/{unit}',     [UnitController::class, 'destroy'])->name('destroy');
    });

    // Manajemen Stok & Penyesuaian (Akses: Administrator & Gudang/Staf - FASE 5.3)
    Route::prefix('stock')->name('stock.')->middleware(['role:admin,staff'])->group(function () {
        Route::get('/',            [StockController::class, 'index'])->name('index');
        Route::get('/adjustment',  [StockController::class, 'adjustment'])->name('adjustment');
        Route::post('/adjustment', [StockController::class, 'storeAdjustment'])->name('adjustment.store');
    });

    // Pembelian / Kulakan (Akses: Administrator & Gudang/Staf - FASE 5)
    Route::prefix('purchases')->name('purchases.')->middleware(['role:admin,staff'])->group(function () {
        Route::get('/',                 [PurchaseController::class, 'index'])->name('index');
        Route::get('/create',           [PurchaseController::class, 'create'])->name('create');
        Route::post('/',                [PurchaseController::class, 'store'])->name('store');
        Route::get('/{purchase}',        [PurchaseController::class, 'show'])->name('show');
        Route::get('/{purchase}/detail', [PurchaseController::class, 'detail'])->name('detail');
    });

    // Supplier (Akses: Administrator & Gudang/Staf)
    Route::prefix('suppliers')->name('suppliers.')->middleware(['role:admin,staff'])->group(function () {
        Route::get('/',                 [SupplierController::class, 'index'])->name('index');
        Route::post('/',                [SupplierController::class, 'store'])->name('store');
        Route::put('/{supplier}',       [SupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}',    [SupplierController::class, 'destroy'])->name('destroy');
    });

    // Pelanggan & Hutang (Akses: Administrator, Kasir, Staf)
    Route::prefix('customers')->name('customers.')->middleware(['role:admin,cashier,staff'])->group(function () {
        Route::get('/',                 [CustomerController::class, 'index'])->name('index');
        Route::post('/',                [CustomerController::class, 'store'])->name('store');
        Route::put('/{customer}',       [CustomerController::class, 'update'])->name('update');
        Route::delete('/{customer}',    [CustomerController::class, 'destroy'])->name('destroy');
        Route::get('/debt',                                [CustomerController::class, 'debt'])->name('debt');
        Route::post('/debt/{debt}/pay',                    [CustomerController::class, 'payDebt'])->name('debt.pay');
        Route::get('/debt/payments/{payment}/receipt',     [CustomerController::class, 'printPaymentReceipt'])->name('debt.receipt');
        Route::get('/debt/payments/{payment}/receipt/pdf', [CustomerController::class, 'downloadPaymentReceiptPdf'])->name('debt.receipt.pdf');
    });

    // Pengeluaran Operasional (Akses: Administrator - FASE 7.1)
    Route::prefix('expenses')->name('expenses.')->middleware(['role:admin'])->group(function () {
        Route::get('/',                  [ExpenseController::class, 'index'])->name('index');
        Route::post('/',                 [ExpenseController::class, 'store'])->name('store');
        Route::get('/{expense}/detail',  [ExpenseController::class, 'detail'])->name('detail');
        Route::put('/{expense}',         [ExpenseController::class, 'update'])->name('update');
        Route::delete('/{expense}',      [ExpenseController::class, 'destroy'])->name('destroy');
    });

    // Keuangan & Kas (Akses: Administrator - FASE 7.2)
    Route::prefix('finance')->name('finance.')->middleware(['role:admin'])->group(function () {
        Route::get('/',              [FinanceController::class, 'index'])->name('index');
        Route::post('/',             [FinanceController::class, 'store'])->name('store');
        Route::delete('/{cashFlow}', [FinanceController::class, 'destroy'])->name('destroy');
    });

    // Modul Laporan Komprehensif & Ekspor (Akses: Administrator - FASE 8.2 & 8.3)
    Route::prefix('reports')->name('reports.')->middleware(['role:admin'])->group(function () {
        Route::get('/sales',                [ReportController::class, 'sales'])->name('sales');
        Route::get('/sales/export-excel',    [ReportController::class, 'exportSalesExcel'])->name('sales.excel');
        Route::get('/sales/export-pdf',      [ReportController::class, 'exportSalesPdf'])->name('sales.pdf');

        Route::get('/purchases',             [ReportController::class, 'purchases'])->name('purchases');
        Route::get('/purchases/export-excel', [ReportController::class, 'exportPurchasesExcel'])->name('purchases.excel');
        Route::get('/purchases/export-pdf',   [ReportController::class, 'exportPurchasesPdf'])->name('purchases.pdf');

        Route::get('/stock',                 [ReportController::class, 'stock'])->name('stock');
        Route::get('/stock/export-excel',    [ReportController::class, 'exportStockExcel'])->name('stock.excel');
        Route::get('/stock/export-pdf',      [ReportController::class, 'exportStockPdf'])->name('stock.pdf');

        Route::get('/products',              [ReportController::class, 'products'])->name('products');
        Route::get('/products/export-excel', [ReportController::class, 'exportProductsExcel'])->name('products.excel');
        Route::get('/products/export-pdf',   [ReportController::class, 'exportProductsPdf'])->name('products.pdf');

        Route::get('/profit',                [ReportController::class, 'profit'])->name('profit');
        Route::get('/profit/export-excel',   [ReportController::class, 'exportProfitExcel'])->name('profit.excel');
        Route::get('/profit/export-pdf',     [ReportController::class, 'exportProfitPdf'])->name('profit.pdf');

        Route::get('/cash',                  [ReportController::class, 'cash'])->name('cash');
        Route::get('/cash/export-excel',     [ReportController::class, 'exportCashExcel'])->name('cash.excel');
        Route::get('/cash/export-pdf',       [ReportController::class, 'exportCashPdf'])->name('cash.pdf');
    });

    // Manajemen Pengguna (Akses: Administrator / Owner)
    Route::prefix('users')->name('users.')->middleware(['role:admin'])->group(function () {
        Route::get('/',                  [UserController::class, 'index'])->name('index');
        Route::post('/',                 [UserController::class, 'store'])->name('store');
        Route::put('/{user}',            [UserController::class, 'update'])->name('update');
        Route::put('/{user}/password',   [UserController::class, 'updatePassword'])->name('update-password');
        Route::patch('/{user}/toggle',   [UserController::class, 'toggleStatus'])->name('toggle');
        Route::delete('/{user}',         [UserController::class, 'destroy'])->name('destroy');
    });

    // Pengaturan Toko (Akses: Administrator - FASE 9.1)
    Route::prefix('settings')->name('settings.')->middleware(['role:admin'])->group(function () {
        Route::get('/',             [SettingController::class, 'index'])->name('index');
        Route::put('/',             [SettingController::class, 'update'])->name('update');
        Route::delete('/logo',      [SettingController::class, 'removeLogo'])->name('remove-logo');
    });

    // Backup & Restore Database (Akses: Administrator - FASE 9.2)
    Route::prefix('backup')->name('backup.')->middleware(['role:admin'])->group(function () {
        Route::get('/',                      [BackupController::class, 'index'])->name('index');
        Route::get('/download',              [BackupController::class, 'download'])->name('download');
        Route::post('/',                     [BackupController::class, 'store'])->name('store');
        Route::get('/download/{filename}',   [BackupController::class, 'downloadFile'])->name('download-file');
        Route::delete('/{filename}',         [BackupController::class, 'destroy'])->name('destroy');
        Route::post('/restore',              [BackupController::class, 'restore'])->name('restore');
    });

    // Import & Export Data Master (FASE 10.1 - Akses: Administrator & Staf)
    Route::prefix('import-export')->name('import_export.')->middleware(['role:admin,staff'])->group(function () {
        Route::get('/',         [ImportExportController::class, 'index'])->name('index');
        Route::post('/export',  [ImportExportController::class, 'export'])->name('export');
        Route::get('/template', [ImportExportController::class, 'downloadTemplate'])->name('template');
        Route::post('/import',  [ImportExportController::class, 'import'])->name('import');
    });

    // Riwayat Aktivitas & Audit Trail (FASE 10.2 - Akses: Administrator)
    Route::prefix('activity')->name('activity.')->middleware(['role:admin'])->group(function () {
        Route::get('/', [ActivityController::class, 'index'])->name('index');
    });
});
