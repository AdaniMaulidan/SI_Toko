@extends('layouts.app')

@section('title', 'Dashboard')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
@endsection

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Dashboard Toko</h4>
    <p class="text-muted mb-0">Ringkasan performa penjualan, kas, dan stok produk secara real-time 👋</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('kasir.index') }}" class="btn btn-primary">
      <i class="bx bx-cart me-1"></i> Buka Kasir / POS
    </a>
  </div>
</div>

{{-- 4 Kartu Metrik Utama --}}
<div class="row g-4 mb-4">
  {{-- 1. Total Penjualan Hari Ini & Bulan Ini --}}
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-muted fw-medium d-block mb-1">Penjualan Hari Ini</span>
            <h4 class="mb-1 text-primary fw-bold">Rp {{ number_format($salesToday, 0, ',', '.') }}</h4>
            <div class="d-flex align-items-center mb-1">
              @if($salesChangePercent >= 0)
                <small class="text-success fw-medium">
                  <i class="bx bx-up-arrow-alt"></i> +{{ $salesChangePercent }}% dari kemarin
                </small>
              @else
                <small class="text-danger fw-medium">
                  <i class="bx bx-down-arrow-alt"></i> {{ $salesChangePercent }}% dari kemarin
                </small>
              @endif
            </div>
            <small class="text-muted d-block border-top pt-1 mt-1">
              Bulan ini: <strong>Rp {{ number_format($salesThisMonth, 0, ',', '.') }}</strong>
            </small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="icon-base bx bx-trending-up icon-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- 2. Jumlah Transaksi Hari Ini & Bulan Ini --}}
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-muted fw-medium d-block mb-1">Transaksi Hari Ini</span>
            <h4 class="mb-1 fw-bold">{{ number_format($transactionsToday) }} <small class="text-muted fs-6">struk</small></h4>
            <div class="d-flex align-items-center mb-1">
              @if($transactionsDiff >= 0)
                <small class="text-success fw-medium">
                  <i class="bx bx-up-arrow-alt"></i> +{{ $transactionsDiff }} dari kemarin
                </small>
              @else
                <small class="text-danger fw-medium">
                  <i class="bx bx-down-arrow-alt"></i> {{ $transactionsDiff }} dari kemarin
                </small>
              @endif
            </div>
            <small class="text-muted d-block border-top pt-1 mt-1">
              Bulan ini: <strong>{{ number_format($transactionsThisMonth) }} transaksi</strong>
            </small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-info">
              <i class="icon-base bx bx-receipt icon-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- 3. Total Keuntungan Bersih (Laba Kotor - Pengeluaran) --}}
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-muted fw-medium d-block mb-1">Keuntungan Bersih (Hari Ini)</span>
            <h4 class="mb-1 {{ $netProfitToday >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
              Rp {{ number_format($netProfitToday, 0, ',', '.') }}
            </h4>
            <div class="d-flex align-items-center mb-1">
              @if($profitChangePercent >= 0)
                <small class="text-success fw-medium">
                  <i class="bx bx-up-arrow-alt"></i> +{{ $profitChangePercent }}% dari kemarin
                </small>
              @else
                <small class="text-danger fw-medium">
                  <i class="bx bx-down-arrow-alt"></i> {{ $profitChangePercent }}% dari kemarin
                </small>
              @endif
            </div>
            <small class="text-muted d-block border-top pt-1 mt-1">
              Bulan ini: <strong class="{{ $netProfitThisMonth >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($netProfitThisMonth, 0, ',', '.') }}</strong>
            </small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-success">
              <i class="icon-base bx bx-dollar-circle icon-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- 4. Jumlah Produk & Low Stock Alert --}}
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-muted fw-medium d-block mb-1">Master Produk</span>
            <h4 class="mb-1 fw-bold">{{ number_format($totalProducts) }} <small class="text-muted fs-6">item</small></h4>
            <div class="d-flex align-items-center mb-1">
              @if($outOfStockCount > 0 || $lowStockCount > 0)
                <span class="badge bg-label-warning me-1">{{ $lowStockCount }} Menipis</span>
                <span class="badge bg-label-danger">{{ $outOfStockCount }} Habis</span>
              @else
                <span class="badge bg-label-success"><i class="bx bx-check me-1"></i>Stok Aman</span>
              @endif
            </div>
            <small class="text-muted d-block border-top pt-1 mt-1">
              <a href="{{ route('stock.index') }}" class="text-primary text-decoration-none">
                Kelola Stok Gudang <i class="bx bx-right-arrow-alt"></i>
              </a>
            </small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="icon-base bx bx-package icon-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Banner Peringatan Stok Menipis & Habis --}}
@if($outOfStockCount > 0 || $lowStockCount > 0)
<div class="row g-4 mb-4">
  @if($lowStockCount > 0)
  <div class="col-md-{{ $outOfStockCount > 0 ? '6' : '12' }}">
    <div class="card border-warning bg-label-warning">
      <div class="card-body py-3">
        <div class="d-flex align-items-center">
          <span class="avatar avatar-sm me-3">
            <span class="avatar-initial rounded bg-warning text-white">
              <i class="bx bx-error icon-md"></i>
            </span>
          </span>
          <div class="flex-grow-1">
            <h6 class="mb-0 text-warning fw-bold">Peringatan: Stok Menipis ({{ $lowStockCount }} Produk)</h6>
            <small class="text-muted">Kuantitas produk berada di bawah batas minimum stok aman.</small>
          </div>
          <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-warning">
            <i class="bx bx-plus me-1"></i> Kulakan
          </a>
        </div>
      </div>
    </div>
  </div>
  @endif

  @if($outOfStockCount > 0)
  <div class="col-md-{{ $lowStockCount > 0 ? '6' : '12' }}">
    <div class="card border-danger bg-label-danger">
      <div class="card-body py-3">
        <div class="d-flex align-items-center">
          <span class="avatar avatar-sm me-3">
            <span class="avatar-initial rounded bg-danger text-white">
              <i class="bx bx-x-circle icon-md"></i>
            </span>
          </span>
          <div class="flex-grow-1">
            <h6 class="mb-0 text-danger fw-bold">Perhatian: Stok Habis ({{ $outOfStockCount }} Produk)</h6>
            <small class="text-muted">Produk dengan stok 0 tidak dapat ditransaksikan di kasir.</small>
          </div>
          <a href="{{ route('stock.index') }}" class="btn btn-sm btn-danger">
            <i class="bx bx-search me-1"></i> Cek Stok
          </a>
        </div>
      </div>
    </div>
  </div>
  @endif
</div>
@endif

{{-- Grafik Penjualan & Ringkasan Kas --}}
<div class="row g-4 mb-4">
  {{-- Grafik Penjualan Dinamis --}}
  <div class="col-xl-8">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center pb-2">
        <div>
          <h5 class="card-title mb-0">Tren Penjualan Toko</h5>
          <small class="text-muted">Grafik fluktuasi omset penjualan produk</small>
        </div>
        <div>
          <select id="periodSelector" class="form-select form-select-sm" onchange="changeChartPeriod(this.value)">
            <option value="seven_days" selected>7 Hari Terakhir</option>
            <option value="thirty_days">30 Hari Terakhir</option>
            <option value="this_month">Bulan Ini</option>
          </select>
        </div>
      </div>
      <div class="card-body pt-0">
        <div id="salesChart" style="min-height: 260px;"></div>
      </div>
    </div>
  </div>

  {{-- Ringkasan Kas & Buku Kas --}}
  <div class="col-xl-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center pb-2">
        <h5 class="card-title mb-0">Arus Kas Toko</h5>
        <a href="{{ route('finance.index') }}" class="btn btn-sm btn-outline-primary">Buku Kas</a>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <p class="mb-0 text-muted small">Pemasukan Hari Ini</p>
            <h5 class="text-success mb-0 fw-bold">+ Rp {{ number_format($cashInToday, 0, ',', '.') }}</h5>
          </div>
          <span class="avatar avatar-sm">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-down-arrow-alt fs-4"></i>
            </span>
          </span>
        </div>
        <hr class="my-2" />
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <p class="mb-0 text-muted small">Pengeluaran Hari Ini</p>
            <h5 class="text-danger mb-0 fw-bold">- Rp {{ number_format($cashOutToday, 0, ',', '.') }}</h5>
          </div>
          <span class="avatar avatar-sm">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="bx bx-up-arrow-alt fs-4"></i>
            </span>
          </span>
        </div>
        <hr class="my-2" />
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <p class="mb-0 text-muted small">Net Kas Hari Ini</p>
            <h5 class="{{ $netCashToday >= 0 ? 'text-primary' : 'text-danger' }} mb-0 fw-bold">
              {{ $netCashToday >= 0 ? '+' : '' }}Rp {{ number_format($netCashToday, 0, ',', '.') }}
            </h5>
          </div>
          <span class="avatar avatar-sm">
            <span class="avatar-initial rounded {{ $netCashToday >= 0 ? 'bg-label-primary' : 'bg-label-danger' }}">
              <i class="bx {{ $netCashToday >= 0 ? 'bx-line-chart' : 'bx-line-chart-down' }} fs-4"></i>
            </span>
          </span>
        </div>
        <hr class="my-2" />
        <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light">
          <div>
            <span class="text-muted small d-block">Saldo Kas Fisik Riil:</span>
            <h4 class="text-primary mb-0 fw-bold">Rp {{ number_format($currentRealBalance, 0, ',', '.') }}</h4>
          </div>
          <span class="avatar avatar-md">
            <span class="avatar-initial rounded bg-primary text-white">
              <i class="bx bx-wallet fs-3"></i>
            </span>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Baris Bawah: Produk Stok Kritis & Transaksi Terbaru --}}
<div class="row g-4">
  {{-- Daftar Produk Stok Kritis --}}
  <div class="col-xl-5">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="card-title mb-0">Produk Stok Menipis</h5>
          <small class="text-muted">Perlu segera dilakukan pengadaan / kulakan</small>
        </div>
        <a href="{{ route('stock.index') }}" class="btn btn-sm btn-outline-secondary">Semua</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Produk</th>
                <th class="text-center">Sisa</th>
                <th class="text-center">Min</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($criticalStockProducts as $prod)
              <tr>
                <td>
                  <div class="fw-semibold text-truncate" style="max-width: 170px;" title="{{ $prod->name }}">{{ $prod->name }}</div>
                  <small class="text-muted">{{ $prod->category->name ?? '-' }}</small>
                </td>
                <td class="text-center">
                  @if($prod->stock <= 0)
                    <span class="badge bg-danger">Habis</span>
                  @else
                    <span class="badge bg-warning text-dark">{{ $prod->stock }} {{ $prod->unit->symbol ?? 'Pcs' }}</span>
                  @endif
                </td>
                <td class="text-center text-muted">
                  {{ $prod->min_stock }}
                </td>
                <td class="text-end">
                  <a href="{{ route('purchases.create') }}" class="btn btn-xs btn-outline-primary" title="Beli Lagi">
                    <i class="bx bx-plus"></i> Kulakan
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center text-muted py-4">
                  <i class="bx bx-check-circle fs-4 text-success d-block mb-1"></i>
                  Semua stok produk berada di level aman.
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- Transaksi Penjualan Terbaru --}}
  <div class="col-xl-7">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="card-title mb-0">Transaksi Penjualan Terbaru</h5>
          <small class="text-muted">Riwayat penjualan produk dari meja kasir</small>
        </div>
        <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Faktur</th>
                <th>Waktu</th>
                <th>Kasir</th>
                <th class="text-end">Total</th>
                <th class="text-center">Metode</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($recentTransactions as $trx)
              <tr>
                <td>
                  <span class="fw-bold text-primary">{{ $trx->invoice_number }}</span>
                  @if($trx->customer)
                  <small class="text-muted d-block">{{ $trx->customer->name }}</small>
                  @endif
                </td>
                <td>
                  <div>{{ $trx->created_at->translatedFormat('d M') }}</div>
                  <small class="text-muted">{{ $trx->created_at->format('H:i') }}</small>
                </td>
                <td><small class="text-muted">{{ $trx->user?->name ?? 'Kasir' }}</small></td>
                <td class="text-end fw-bold">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                <td class="text-center">
                  @php
                    $methodBadge = match($trx->payment_method) {
                      'cash'     => 'bg-label-success',
                      'qris'     => 'bg-label-info',
                      'transfer' => 'bg-label-primary',
                      'debt'     => 'bg-label-warning',
                      default    => 'bg-label-secondary',
                    };
                    $methodLabel = match($trx->payment_method) {
                      'cash'     => 'Tunai',
                      'qris'     => 'QRIS',
                      'transfer' => 'Transfer',
                      'debt'     => 'Hutang',
                      default    => ucfirst($trx->payment_method),
                    };
                  @endphp
                  <span class="badge {{ $methodBadge }}">{{ $methodLabel }}</span>
                </td>
                <td class="text-center">
                  <a href="{{ route('transactions.show', $trx->id) }}" class="btn btn-sm btn-icon btn-text-secondary" title="Detail Transaksi">
                    <i class="icon-base bx bx-show icon-md"></i>
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-4">
                  <i class="bx bx-receipt fs-4 d-block mb-1"></i>
                  Belum ada transaksi penjualan hari ini.
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection

@section('page-script')
<script>
  // Dataset grafik penjualan dari backend
  const chartDatasets = @json($chartData);

  let salesChart = null;

  function initSalesChart() {
    const el = document.getElementById('salesChart');
    if (!el) return;

    if (typeof ApexCharts === 'undefined') {
      console.warn('ApexCharts library belum termuat.');
      return;
    }

    const currentData = chartDatasets.seven_days;

    const options = {
      series: [{
        name: 'Omset Penjualan',
        data: currentData.data
      }],
      chart: {
        type: 'area',
        height: 270,
        parentHeightOffset: 0,
        toolbar: { show: false },
        animations: {
          enabled: true,
          easing: 'easeinout',
          speed: 600
        }
      },
      dataLabels: { enabled: false },
      stroke: { curve: 'smooth', width: 2.5 },
      colors: ['#696cff'],
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.45,
          opacityTo: 0.05,
          stops: [0, 90, 100]
        }
      },
      xaxis: {
        categories: currentData.labels,
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: {
          style: {
            fontSize: '12px',
            colors: '#a1acb8'
          }
        }
      },
      yaxis: {
        labels: {
          formatter: function(val) {
            if (val >= 1000000) {
              return 'Rp ' + (val / 1000000).toFixed(1) + 'jt';
            } else if (val >= 1000) {
              return 'Rp ' + (val / 1000).toFixed(0) + 'rb';
            }
            return 'Rp ' + val;
          },
          style: {
            fontSize: '12px',
            colors: '#a1acb8'
          }
        }
      },
      grid: {
        borderColor: '#e7e7e7',
        strokeDashArray: 4,
        padding: { top: 0, right: 10, bottom: 0, left: 10 }
      },
      tooltip: {
        y: {
          formatter: function(val) {
            return 'Rp ' + Number(val).toLocaleString('id-ID');
          }
        }
      }
    };

    salesChart = new ApexCharts(el, options);
    salesChart.render();
  }

  function changeChartPeriod(period) {
    if (!salesChart || !chartDatasets[period]) return;
    const target = chartDatasets[period];

    salesChart.updateOptions({
      xaxis: {
        categories: target.labels
      },
      series: [{
        name: 'Omset Penjualan',
        data: target.data
      }]
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    // Jalankan inisialisasi grafik, jika script tertunda beri jeda singkat
    if (typeof ApexCharts !== 'undefined') {
      initSalesChart();
    } else {
      setTimeout(initSalesChart, 300);
    }
  });
</script>
@endsection
