<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
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
    Route::get('/auth/forgot-password',  fn() => view('auth.forgot-password'))->name('auth.forgot-password');
    Route::post('/auth/forgot-password', fn() => back()->with('status', 'Link reset password telah dikirim ke email Anda.'))->name('auth.forgot-password.submit');
});

Route::post('/logout',      [AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth');

// ── Main App (Protected with auth middleware) ─────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', fn() => view('dashboard.index'))->name('dashboard');

    // Kasir / POS (Akses: Administrator & Kasir)
    Route::middleware(['role:admin,cashier'])->group(function () {
        Route::get('/kasir',                 [PosController::class, 'index'])->name('kasir.index');
        Route::get('/kasir/search-products', [PosController::class, 'searchProducts'])->name('kasir.search');
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

    // Stok
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/',           fn() => view('stock.index'))->name('index');
        Route::get('/adjustment', fn() => view('stock.adjustment'))->name('adjustment');
    });

    // Pembelian
    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::get('/',       fn() => view('purchases.index'))->name('index');
        Route::get('/create', fn() => view('purchases.create'))->name('create');
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
        Route::get('/debt',             [CustomerController::class, 'debt'])->name('debt');
        Route::post('/debt/{debt}/pay', [CustomerController::class, 'payDebt'])->name('debt.pay');
    });

    // Pengeluaran
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', fn() => view('expenses.index'))->name('index');
    });

    // Keuangan / Kas
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/', fn() => view('finance.index'))->name('index');
    });

    // Laporan
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales',     fn() => view('reports.sales'))->name('sales');
        Route::get('/purchases', fn() => view('reports.purchases'))->name('purchases');
        Route::get('/stock',     fn() => view('reports.stock'))->name('stock');
        Route::get('/products',  fn() => view('reports.products'))->name('products');
        Route::get('/profit',    fn() => view('reports.profit'))->name('profit');
        Route::get('/cash',      fn() => view('reports.cash'))->name('cash');
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

    // Pengaturan
    Route::prefix('settings')->name('settings.')->middleware(['role:admin'])->group(function () {
        Route::get('/', fn() => view('settings.index'))->name('index');
    });

    // Backup & Restore
    Route::prefix('backup')->name('backup.')->middleware(['role:admin'])->group(function () {
        Route::get('/', fn() => view('backup.index'))->name('index');
    });

    // Import & Export
    Route::prefix('import-export')->name('import_export.')->group(function () {
        Route::get('/', fn() => view('import_export.index'))->name('index');
    });

    // Audit / Aktivitas
    Route::prefix('activity')->name('activity.')->group(function () {
        Route::get('/', fn() => view('activity.index'))->name('index');
    });
});
