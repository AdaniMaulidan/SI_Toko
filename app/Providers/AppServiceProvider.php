<?php

namespace App\Providers;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // View Composer untuk Notifikasi Stok Dinamis di Navbar
        View::composer('layouts.sections.navbar', function ($view) {
            if (Schema::hasTable('products')) {
                $lowStockCount   = Product::active()->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock')->count();
                $outOfStockCount = Product::active()->where('stock', '<=', 0)->count();
                $criticalItems   = Product::active()
                    ->where(function ($q) {
                        $q->where('stock', '<=', 0)
                          ->orWhereColumn('stock', '<=', 'min_stock');
                    })
                    ->with('unit')
                    ->orderBy('stock', 'asc')
                    ->limit(5)
                    ->get();

                $view->with([
                    'navbarLowStockCount'    => $lowStockCount,
                    'navbarOutOfStockCount'  => $outOfStockCount,
                    'navbarTotalAlerts'      => $lowStockCount + $outOfStockCount,
                    'navbarCriticalProducts' => $criticalItems,
                ]);
            }
        });
    }
}
