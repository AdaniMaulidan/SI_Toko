@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

{{-- Page Header --}}
<div class="row mb-4">
  <div class="col-12">
    <h4 class="fw-bold mb-1">Dashboard</h4>
    <p class="text-muted mb-0">Selamat datang di SI Kasir 👋</p>
  </div>
</div>

{{-- Stats Cards --}}
<div class="row g-4 mb-4">
  {{-- Total Penjualan Hari Ini --}}
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-muted fw-medium">Penjualan Hari Ini</span>
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2">Rp 2.450.000</h4>
            </div>
            <small class="text-success fw-medium">
              <i class="bx bx-up-arrow-alt"></i> +12% dari kemarin
            </small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-success">
              <i class="icon-base bx bx-trending-up icon-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Jumlah Transaksi --}}
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-muted fw-medium">Transaksi Hari Ini</span>
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2">38</h4>
            </div>
            <small class="text-success fw-medium">
              <i class="bx bx-up-arrow-alt"></i> +5 dari kemarin
            </small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="icon-base bx bx-receipt icon-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Total Keuntungan --}}
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-muted fw-medium">Keuntungan Hari Ini</span>
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2">Rp 485.000</h4>
            </div>
            <small class="text-success fw-medium">
              <i class="bx bx-up-arrow-alt"></i> +8% dari kemarin
            </small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="icon-base bx bx-dollar-circle icon-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Jumlah Produk --}}
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-muted fw-medium">Total Produk</span>
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2">248</h4>
            </div>
            <small class="text-muted fw-medium">
              <span class="text-danger">5 menipis</span> · <span class="text-danger fw-bold">2 habis</span>
            </small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-info">
              <i class="icon-base bx bx-package icon-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Alert Stok --}}
<div class="row g-4 mb-4">
  <div class="col-md-6">
    <div class="card border-warning">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <span class="avatar avatar-sm me-3">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="bx bx-error icon-md"></i>
            </span>
          </span>
          <div class="flex-grow-1">
            <h6 class="mb-0">Stok Menipis</h6>
            <small class="text-muted">5 produk di bawah minimum stok</small>
          </div>
          <a href="{{ route('stock.index') }}" class="btn btn-sm btn-warning">Lihat</a>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card border-danger">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <span class="avatar avatar-sm me-3">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="bx bx-x-circle icon-md"></i>
            </span>
          </span>
          <div class="flex-grow-1">
            <h6 class="mb-0">Stok Habis</h6>
            <small class="text-muted">2 produk kehabisan stok</small>
          </div>
          <a href="{{ route('stock.index') }}" class="btn btn-sm btn-danger">Lihat</a>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Grafik & Transaksi Terbaru --}}
<div class="row g-4 mb-4">
  {{-- Grafik Penjualan --}}
  <div class="col-xl-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Grafik Penjualan</h5>
        <div>
          <select class="form-select form-select-sm">
            <option>7 Hari Terakhir</option>
            <option>30 Hari Terakhir</option>
            <option>Bulan Ini</option>
          </select>
        </div>
      </div>
      <div class="card-body">
        <div id="salesChart"></div>
      </div>
    </div>
  </div>

  {{-- Ringkasan Kas --}}
  <div class="col-xl-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">Ringkasan Kas Hari Ini</h5>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <p class="mb-1 text-muted small">Pemasukan</p>
            <h5 class="text-success mb-0">Rp 2.450.000</h5>
          </div>
          <span class="avatar avatar-sm">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-trending-up"></i>
            </span>
          </span>
        </div>
        <hr />
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <p class="mb-1 text-muted small">Pengeluaran</p>
            <h5 class="text-danger mb-0">Rp 850.000</h5>
          </div>
          <span class="avatar avatar-sm">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="bx bx-trending-down"></i>
            </span>
          </span>
        </div>
        <hr />
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="mb-1 text-muted small">Saldo Bersih</p>
            <h5 class="text-primary mb-0">Rp 1.600.000</h5>
          </div>
          <span class="avatar avatar-sm">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-wallet"></i>
            </span>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Transaksi Terbaru --}}
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Transaksi Terbaru</h5>
        <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>No. Transaksi</th>
                <th>Tanggal</th>
                <th>Kasir</th>
                <th>Total</th>
                <th>Metode</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @php
              $transactions = [
                ['TRX-001', '07 Okt 2026, 08:45', 'Admin', 'Rp 85.000', 'Tunai'],
                ['TRX-002', '07 Okt 2026, 09:10', 'Admin', 'Rp 120.000', 'QRIS'],
                ['TRX-003', '07 Okt 2026, 09:35', 'Admin', 'Rp 45.500', 'Tunai'],
                ['TRX-004', '07 Okt 2026, 10:02', 'Admin', 'Rp 200.000', 'Transfer'],
                ['TRX-005', '07 Okt 2026, 10:30', 'Admin', 'Rp 67.000', 'Tunai'],
              ];
              @endphp
              @foreach ($transactions as $trx)
              <tr>
                <td><span class="fw-medium">{{ $trx[0] }}</span></td>
                <td>{{ $trx[1] }}</td>
                <td>{{ $trx[2] }}</td>
                <td><span class="fw-medium">{{ $trx[3] }}</span></td>
                <td>
                  @if($trx[4] === 'Tunai')
                    <span class="badge bg-label-success">{{ $trx[4] }}</span>
                  @elseif($trx[4] === 'QRIS')
                    <span class="badge bg-label-info">{{ $trx[4] }}</span>
                  @elseif($trx[4] === 'Transfer')
                    <span class="badge bg-label-primary">{{ $trx[4] }}</span>
                  @else
                    <span class="badge bg-label-warning">{{ $trx[4] }}</span>
                  @endif
                </td>
                <td>
                  <a href="{{ route('transactions.show', 1) }}" class="btn btn-sm btn-icon btn-text-secondary">
                    <i class="icon-base bx bx-show icon-md"></i>
                  </a>
                </td>
              </tr>
              @endforeach
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
  // Grafik Penjualan
  const salesChartOptions = {
    series: [{
      name: 'Penjualan',
      data: [850000, 1200000, 980000, 1450000, 1100000, 1800000, 2450000]
    }],
    chart: {
      type: 'area',
      height: 250,
      toolbar: { show: false },
    },
    stroke: { curve: 'smooth', width: 2 },
    fill: {
      type: 'gradient',
      gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 }
    },
    xaxis: {
      categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
    },
    yaxis: {
      labels: {
        formatter: (val) => 'Rp ' + (val / 1000) + 'rb'
      }
    },
    colors: ['#696cff'],
    dataLabels: { enabled: false },
    tooltip: {
      y: {
        formatter: (val) => 'Rp ' + val.toLocaleString('id-ID')
      }
    }
  };

  if (document.getElementById('salesChart')) {
    const chart = new ApexCharts(document.getElementById('salesChart'), salesChartOptions);
    chart.render();
  }
</script>
@endsection
