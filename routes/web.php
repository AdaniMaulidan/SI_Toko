<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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

    // Kasir / POS
    Route::get('/kasir', fn() => view('kasir.index'))->name('kasir.index');

    // Riwayat Transaksi
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/',       fn() => view('transactions.index'))->name('index');
        Route::get('/{id}',   fn($id) => view('transactions.show', ['id' => $id]))->name('show');
    });

    // Produk
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/',         fn() => view('products.index'))->name('index');
        Route::get('/create',   fn() => view('products.create'))->name('create');
        Route::get('/{id}/edit',fn($id) => view('products.edit', ['id' => $id]))->name('edit');
        Route::get('/{id}',     fn($id) => view('products.show', ['id' => $id]))->name('show');
    });

    // Kategori
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', fn() => view('categories.index'))->name('index');
    });

    // Satuan
    Route::prefix('units')->name('units.')->group(function () {
        Route::get('/', fn() => view('units.index'))->name('index');
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

    // Supplier
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/',       fn() => view('suppliers.index'))->name('index');
    });

    // Pelanggan & Hutang
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', fn() => view('customers.index'))->name('index');
        Route::get('/debt', fn() => view('customers.debt'))->name('debt');
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
