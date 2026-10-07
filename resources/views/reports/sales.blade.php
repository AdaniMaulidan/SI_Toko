@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Laporan /</span> Penjualan
    </h4>
    <button type="button" class="btn btn-primary" onclick="window.print()">
        <i class="bx bx-printer me-1"></i> Cetak Laporan
    </button>
</div>

<!-- Filter Card -->
<div class="card mb-4 d-print-none">
    <div class="card-body">
        <form class="row gx-3 gy-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" class="form-control" value="{{ date('Y-m-01') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" value="{{ date('Y-m-t') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Kasir</label>
                <select class="form-select">
                    <option value="all">Semua Kasir</option>
                    <option value="1">Admin Utama</option>
                    <option value="2">Kasir 1</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-primary w-100">Filter Laporan</button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-sm-6 col-md-3 mb-3 mb-md-0">
        <div class="card card-border-shadow-primary h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                    <div class="avatar me-2">
                        <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-cart"></i></span>
                    </div>
                    <h4 class="ms-1 mb-0">125</h4>
                </div>
                <p class="mb-1">Total Transaksi</p>
                <p class="mb-0">
                    <span class="fw-medium me-1">+18%</span>
                    <small class="text-muted">dari bulan lalu</small>
                </p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 mb-3 mb-md-0">
        <div class="card card-border-shadow-success h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                    <div class="avatar me-2">
                        <span class="avatar-initial rounded bg-label-success"><i class="bx bx-dollar"></i></span>
                    </div>
                    <h4 class="ms-1 mb-0">28.450k</h4>
                </div>
                <p class="mb-1">Total Pendapatan</p>
                <p class="mb-0">
                    <span class="fw-medium me-1">+12%</span>
                    <small class="text-muted">dari bulan lalu</small>
                </p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 mb-3 mb-md-0">
        <div class="card card-border-shadow-warning h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                    <div class="avatar me-2">
                        <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-package"></i></span>
                    </div>
                    <h4 class="ms-1 mb-0">842</h4>
                </div>
                <p class="mb-1">Barang Terjual (Qty)</p>
                <p class="mb-0">
                    <span class="fw-medium me-1">+5%</span>
                    <small class="text-muted">dari bulan lalu</small>
                </p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 mb-3 mb-md-0">
        <div class="card card-border-shadow-info h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                    <div class="avatar me-2">
                        <span class="avatar-initial rounded bg-label-info"><i class="bx bx-user"></i></span>
                    </div>
                    <h4 class="ms-1 mb-0">32</h4>
                </div>
                <p class="mb-1">Pelanggan Unik</p>
                <p class="mb-0">
                    <span class="fw-medium me-1">+2%</span>
                    <small class="text-muted">dari bulan lalu</small>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Rincian Laporan Penjualan (Okt 2026)</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>No Transaksi</th>
                    <th>Pelanggan</th>
                    <th>Total Item</th>
                    <th>Metode Pembayaran</th>
                    <th>Subtotal</th>
                    <th>Diskon</th>
                    <th>Total Pendapatan</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @php
                $sales = [
                    ['date' => '07 Okt 2026', 'no' => 'TRX-10029', 'cust' => 'Budi Santoso', 'items' => 5, 'method' => 'Tunai', 'sub' => 150000, 'disc' => 5000, 'total' => 145000],
                    ['date' => '07 Okt 2026', 'no' => 'TRX-10028', 'cust' => 'Umum', 'items' => 2, 'method' => 'QRIS', 'sub' => 35000, 'disc' => 0, 'total' => 35000],
                    ['date' => '06 Okt 2026', 'no' => 'TRX-10027', 'cust' => 'Ahmad Junaidi', 'items' => 12, 'method' => 'Transfer', 'sub' => 320000, 'disc' => 8000, 'total' => 312000],
                    ['date' => '05 Okt 2026', 'no' => 'TRX-10026', 'cust' => 'Siti Aminah', 'items' => 8, 'method' => 'Tunai', 'sub' => 150000, 'disc' => 0, 'total' => 150000],
                ];
                $total_pendapatan = array_sum(array_column($sales, 'total'));
                @endphp

                @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale['date'] }}</td>
                    <td><span class="fw-medium text-primary">{{ $sale['no'] }}</span></td>
                    <td>{{ $sale['cust'] }}</td>
                    <td>{{ $sale['items'] }} pcs</td>
                    <td>
                        <span class="badge bg-label-{{ $sale['method'] == 'Tunai' ? 'success' : ($sale['method'] == 'QRIS' ? 'info' : 'primary') }}">
                            {{ $sale['method'] }}
                        </span>
                    </td>
                    <td>Rp {{ number_format($sale['sub'], 0, ',', '.') }}</td>
                    <td class="text-danger">-Rp {{ number_format($sale['disc'], 0, ',', '.') }}</td>
                    <td class="fw-bold">Rp {{ number_format($sale['total'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="7" class="text-end">TOTAL PENDAPATAN (Filter Aktif):</td>
                    <td class="text-primary fs-5">Rp {{ number_format($total_pendapatan, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@section('page-style')
<style>
    @media print {
        .layout-menu, .layout-navbar, .d-print-none, .btn { display: none !important; }
        .content-wrapper { padding: 0 !important; margin: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        @page { size: landscape; }
    }
</style>
@endsection
