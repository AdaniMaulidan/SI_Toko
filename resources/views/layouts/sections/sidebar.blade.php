@php
use Illuminate\Support\Facades\Route;

$currentRoute = Route::currentRouteName();

// Helper: cek apakah route saat ini match dengan prefix
function isActiveMenu($route, $current) {
    if (is_array($route)) {
        foreach ($route as $r) {
            if (str_starts_with($current ?? '', $r)) return true;
        }
        return false;
    }
    return str_starts_with($current ?? '', $route);
}
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <!-- Brand -->
  <div class="app-brand demo">
    <a href="{{ route('dashboard') }}" class="app-brand-link">
      <span class="app-brand-logo demo">
        <span class="text-primary">
          <svg width="25" viewBox="0 0 25 42" xmlns="http://www.w3.org/2000/svg">
            <path fill="currentColor" d="M13.792 0.358L3.398 7.442C0.567 9.694-0.38 12.479 0.558 15.796c0.132 0.435 0.538 1.992 2.566 3.434 0.691 0.491 2.2 1.154 4.527 1.988L7.598 21.253 2.635 24.549C0.445 26.3 0.088 28.508 1.564 31.174 2.838 32.817 5.209 33.264 7.092 32.539c1.255-.483 4.364-2.538 9.326-6.164 1.616-1.875 2.28-3.92 1.99-6.136-.444-2.704-2.23-4.659-5.358-5.864L10.92 13.472 18.619 7.984 13.792 0.358Z"/>
          </svg>
        </span>
      </span>
      <span class="app-brand-text demo menu-text fw-bold ms-2">SI Kasir</span>
    </a>
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="icon-base bx bx-chevron-left icon-sm d-flex align-items-center justify-content-center"></i>
    </a>
  </div>

  <div class="menu-divider mt-0"></div>
  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">

    {{-- ===================== DASHBOARD ===================== --}}
    <li class="menu-item {{ isActiveMenu('dashboard', $currentRoute) ? 'active' : '' }}">
      <a href="{{ route('dashboard') }}" class="menu-link">
        <i class="menu-icon icon-base bx bx-home-smile"></i>
        <div>Dashboard</div>
      </a>
    </li>

    {{-- ===================== KASIR ===================== --}}
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Transaksi</span>
    </li>

    <li class="menu-item {{ isActiveMenu('kasir', $currentRoute) ? 'active' : '' }}">
      <a href="{{ route('kasir.index') }}" class="menu-link">
        <i class="menu-icon icon-base bx bx-cart-alt"></i>
        <div>Kasir / POS</div>
      </a>
    </li>

    <li class="menu-item {{ isActiveMenu('transactions', $currentRoute) ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon icon-base bx bx-receipt"></i>
        <div>Riwayat Transaksi</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ $currentRoute === 'transactions.index' ? 'active' : '' }}">
          <a href="{{ route('transactions.index') }}" class="menu-link">
            <div>Semua Transaksi</div>
          </a>
        </li>
      </ul>
    </li>

    {{-- ===================== PRODUK ===================== --}}
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Produk</span>
    </li>

    <li class="menu-item {{ isActiveMenu('products', $currentRoute) ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon icon-base bx bx-package"></i>
        <div>Manajemen Produk</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ $currentRoute === 'products.index' ? 'active' : '' }}">
          <a href="{{ route('products.index') }}" class="menu-link">
            <div>Daftar Produk</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute === 'products.create' ? 'active' : '' }}">
          <a href="{{ route('products.create') }}" class="menu-link">
            <div>Tambah Produk</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-item {{ isActiveMenu('categories', $currentRoute) ? 'active' : '' }}">
      <a href="{{ route('categories.index') }}" class="menu-link">
        <i class="menu-icon icon-base bx bx-category"></i>
        <div>Kategori</div>
      </a>
    </li>

    <li class="menu-item {{ isActiveMenu('units', $currentRoute) ? 'active' : '' }}">
      <a href="{{ route('units.index') }}" class="menu-link">
        <i class="menu-icon icon-base bx bx-ruler"></i>
        <div>Satuan</div>
      </a>
    </li>

    {{-- ===================== STOK ===================== --}}
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Stok & Pembelian</span>
    </li>

    <li class="menu-item {{ isActiveMenu('stock', $currentRoute) ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon icon-base bx bx-spreadsheet"></i>
        <div>Manajemen Stok</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ $currentRoute === 'stock.index' ? 'active' : '' }}">
          <a href="{{ route('stock.index') }}" class="menu-link">
            <div>Stok Saat Ini</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute === 'stock.adjustment' ? 'active' : '' }}">
          <a href="{{ route('stock.adjustment') }}" class="menu-link">
            <div>Penyesuaian Stok</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-item {{ isActiveMenu('purchases', $currentRoute) ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon icon-base bx bx-store-alt"></i>
        <div>Pembelian Barang</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ $currentRoute === 'purchases.index' ? 'active' : '' }}">
          <a href="{{ route('purchases.index') }}" class="menu-link">
            <div>Riwayat Pembelian</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute === 'purchases.create' ? 'active' : '' }}">
          <a href="{{ route('purchases.create') }}" class="menu-link">
            <div>Tambah Pembelian</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-item {{ isActiveMenu('suppliers', $currentRoute) ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon icon-base bx bx-truck"></i>
        <div>Supplier</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ $currentRoute === 'suppliers.index' ? 'active' : '' }}">
          <a href="{{ route('suppliers.index') }}" class="menu-link">
            <div>Daftar Supplier</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute === 'suppliers.create' ? 'active' : '' }}">
          <a href="{{ route('suppliers.create') }}" class="menu-link">
            <div>Tambah Supplier</div>
          </a>
        </li>
      </ul>
    </li>

    {{-- ===================== PELANGGAN ===================== --}}
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Pelanggan</span>
    </li>

    <li class="menu-item {{ isActiveMenu('customers', $currentRoute) ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon icon-base bx bx-user-circle"></i>
        <div>Pelanggan</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ $currentRoute === 'customers.index' ? 'active' : '' }}">
          <a href="{{ route('customers.index') }}" class="menu-link">
            <div>Daftar Pelanggan</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-item {{ isActiveMenu('debts', $currentRoute) ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon icon-base bx bx-credit-card"></i>
        <div>Hutang Pelanggan</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ $currentRoute === 'debts.index' ? 'active' : '' }}">
          <a href="{{ route('debts.index') }}" class="menu-link">
            <div>Daftar Hutang</div>
          </a>
        </li>
      </ul>
    </li>

    {{-- ===================== KEUANGAN ===================== --}}
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Keuangan</span>
    </li>

    <li class="menu-item {{ isActiveMenu('expenses', $currentRoute) ? 'active' : '' }}">
      <a href="{{ route('expenses.index') }}" class="menu-link">
        <i class="menu-icon icon-base bx bx-wallet-alt"></i>
        <div>Pengeluaran Toko</div>
      </a>
    </li>

    <li class="menu-item {{ isActiveMenu('finance', $currentRoute) ? 'active' : '' }}">
      <a href="{{ route('finance.index') }}" class="menu-link">
        <i class="menu-icon icon-base bx bx-money"></i>
        <div>Keuangan / Kas</div>
      </a>
    </li>

    {{-- ===================== LAPORAN ===================== --}}
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Laporan</span>
    </li>

    <li class="menu-item {{ isActiveMenu('reports', $currentRoute) ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon icon-base bx bx-bar-chart-alt-2"></i>
        <div>Laporan</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ $currentRoute === 'reports.sales' ? 'active' : '' }}">
          <a href="{{ route('reports.sales') }}" class="menu-link">
            <div>Laporan Penjualan</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute === 'reports.purchases' ? 'active' : '' }}">
          <a href="{{ route('reports.purchases') }}" class="menu-link">
            <div>Laporan Pembelian</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute === 'reports.stock' ? 'active' : '' }}">
          <a href="{{ route('reports.stock') }}" class="menu-link">
            <div>Laporan Stok</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute === 'reports.products' ? 'active' : '' }}">
          <a href="{{ route('reports.products') }}" class="menu-link">
            <div>Laporan Produk</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute === 'reports.profit' ? 'active' : '' }}">
          <a href="{{ route('reports.profit') }}" class="menu-link">
            <div>Laporan Keuntungan</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute === 'reports.cash' ? 'active' : '' }}">
          <a href="{{ route('reports.cash') }}" class="menu-link">
            <div>Laporan Kas</div>
          </a>
        </li>
      </ul>
    </li>

    {{-- ===================== ADMIN ===================== --}}
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Administrasi</span>
    </li>

    <li class="menu-item {{ isActiveMenu('users', $currentRoute) ? 'active' : '' }}">
      <a href="{{ route('users.index') }}" class="menu-link">
        <i class="menu-icon icon-base bx bx-group"></i>
        <div>Manajemen Pengguna</div>
      </a>
    </li>

    <li class="menu-item {{ isActiveMenu('settings', $currentRoute) ? 'active' : '' }}">
      <a href="{{ route('settings.index') }}" class="menu-link">
        <i class="menu-icon icon-base bx bx-cog"></i>
        <div>Pengaturan Toko</div>
      </a>
    </li>

    <li class="menu-item {{ isActiveMenu('backup', $currentRoute) ? 'active' : '' }}">
      <a href="{{ route('backup.index') }}" class="menu-link">
        <i class="menu-icon icon-base bx bx-cloud-upload"></i>
        <div>Backup & Restore</div>
      </a>
    </li>

    <li class="menu-item {{ isActiveMenu('activity', $currentRoute) ? 'active' : '' }}">
      <a href="{{ route('activity.index') }}" class="menu-link">
        <i class="menu-icon icon-base bx bx-history"></i>
        <div>Riwayat Aktivitas</div>
      </a>
    </li>

  </ul>
</aside>
