@extends('layouts.app')

@section('title', 'Laporan Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Laporan /</span> Pembelian (Kulakan)
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
                <label class="form-label">Bulan Laporan</label>
                <input type="month" class="form-control" value="{{ date('Y-m') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Supplier</label>
                <select class="form-select">
                    <option value="all">Semua Supplier</option>
                    <option value="1">PT. Indofood</option>
                    <option value="2">PT. Tirta Investama</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status Bayar</label>
                <select class="form-select">
                    <option value="all">Semua</option>
                    <option value="lunas">Lunas</option>
                    <option value="hutang">Hutang (Tempo)</option>
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
    <div class="col-sm-6 col-lg-4 mb-4 mb-lg-0">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <h6 class="text-white mb-2">Total Belanja (Bulan Ini)</h6>
                <h3 class="text-white fw-bold mb-2">Rp 12.850.000</h3>
                <small>Dari 15 Transaksi PO</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4 mb-4 mb-lg-0">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <h6 class="text-white mb-2">Pembelian Lunas</h6>
                <h3 class="text-white fw-bold mb-2">Rp 9.500.000</h3>
                <small>10 Transaksi</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4 mb-4 mb-lg-0">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <h6 class="text-white mb-2">Pembelian Belum Lunas (Hutang Usaha)</h6>
                <h3 class="text-white fw-bold mb-2">Rp 3.350.000</h3>
                <small>5 Transaksi (Jatuh Tempo Mendekat)</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Rincian Pembelian (Okt 2026)</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-striped">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>No PO</th>
                    <th>Supplier</th>
                    <th>Item Barang Masuk</th>
                    <th>Status</th>
                    <th>Total Biaya</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @php
                $purchases = [
                    ['date' => '10 Okt 2026', 'no' => 'PO-202610-008', 'sup' => 'PT. Indofood', 'items' => 250, 'status' => 'Lunas', 'total' => 4500000],
                    ['date' => '08 Okt 2026', 'no' => 'PO-202610-007', 'sup' => 'PT. Tirta Investama', 'items' => 120, 'status' => 'Hutang', 'total' => 2100000],
                    ['date' => '05 Okt 2026', 'no' => 'PO-202610-006', 'sup' => 'Grosir Sembako Jaya', 'items' => 85, 'status' => 'Lunas', 'total' => 1250000],
                    ['date' => '01 Okt 2026', 'no' => 'PO-202610-005', 'sup' => 'PT. Unilever', 'items' => 180, 'status' => 'Hutang', 'total' => 5000000],
                ];
                $total = array_sum(array_column($purchases, 'total'));
                @endphp

                @foreach($purchases as $p)
                <tr>
                    <td>{{ $p['date'] }}</td>
                    <td><span class="fw-medium text-primary">{{ $p['no'] }}</span></td>
                    <td>{{ $p['sup'] }}</td>
                    <td>{{ $p['items'] }} qty</td>
                    <td>
                        <span class="badge bg-label-{{ $p['status'] == 'Lunas' ? 'success' : 'warning' }}">
                            {{ $p['status'] }}
                        </span>
                    </td>
                    <td class="fw-bold text-danger">Rp {{ number_format($p['total'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="5" class="text-end">TOTAL BIAYA KULAKAN:</td>
                    <td class="text-danger fs-5">Rp {{ number_format($total, 0, ',', '.') }}</td>
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
    }
</style>
@endsection
