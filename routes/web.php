<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SI Kasir - Web Routes
|--------------------------------------------------------------------------
*/

// ── Authentication ──────────────────────────────────────────────────────
Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/login',           fn() => view('auth.login'))->name('login');
    Route::post('/login',          fn() => redirect()->route('dashboard'))->name('login.submit');
    Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('forgot-password');
    Route::post('/forgot-password', fn() => back()->with('status', 'Link reset password telah dikirim ke email Anda.'))->name('forgot-password.submit');
    Route::post('/logout',         fn() => redirect()->route('auth.login'))->name('logout');
});

// ── Main App (akan diberi middleware auth nanti) ─────────────────────────
Route::middleware([])->group(function () {

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
        Route::get('/create', fn() => view('suppliers.create'))->name('create');
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

    // Keuangan
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

    // Manajemen Pengguna
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', fn() => view('users.index'))->name('index');
    });

    // Pengaturan
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', fn() => view('settings.index'))->name('index');
    });

    // Backup
    Route::prefix('backup')->name('backup.')->group(function () {
        Route::get('/', fn() => view('backup.index'))->name('index');
    });

    // Audit / Aktivitas
    Route::prefix('activity')->name('activity.')->group(function () {
        Route::get('/', fn() => view('activity.index'))->name('index');
    });
});
