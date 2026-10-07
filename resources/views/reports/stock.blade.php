@extends('layouts.app')

@section('title', 'Laporan Stok & Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Laporan /</span> Stok & Produk
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
                <label class="form-label">Kategori Produk</label>
                <select class="form-select">
                    <option value="all">Semua Kategori</option>
                    <option value="1">Sembako</option>
                    <option value="2">Minuman</option>
                    <option value="3">Snack</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status Stok</label>
                <select class="form-select">
                    <option value="all">Semua Status</option>
                    <option value="habis">Habis (Out of Stock)</option>
                    <option value="menipis">Menipis (Low Stock)</option>
                    <option value="berlebih">Overstock (Aman)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Urutkan Berdasarkan</label>
                <select class="form-select">
                    <option value="qty_asc">Sisa Stok Terkecil</option>
                    <option value="qty_desc">Sisa Stok Terbesar</option>
                    <option value="value_desc">Nilai Aset Terbesar</option>
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
    <div class="col-md-6 mb-4 mb-md-0">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <h6 class="text-white mb-2">Total Nilai Aset Stok (Modal HPP)</h6>
                <h3 class="text-white fw-bold mb-2">Rp 45.250.000</h3>
                <small>Estimasi nilai uang dari total barang di toko saat ini.</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="row g-4 h-100">
            <div class="col-6">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body text-center px-2">
                        <h3 class="text-white fw-bold mb-1">12</h3>
                        <span class="d-block lh-sm">Item Menipis</span>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body text-center px-2">
                        <h3 class="text-white fw-bold mb-1">3</h3>
                        <span class="d-block lh-sm">Item Habis</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Valuasi & Status Stok Fisik</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Kode Barang</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga Beli (HPP)</th>
                    <th>Sisa Qty</th>
                    <th>Nilai Aset (HPP x Qty)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @php
                $stocks = [
                    ['code' => 'BRG-001', 'name' => 'Indomie Goreng', 'cat' => 'Sembako', 'hpp' => 2500, 'qty' => 120, 'status' => 'Aman'],
                    ['code' => 'BRG-002', 'name' => 'Beras Sania 5kg', 'cat' => 'Sembako', 'hpp' => 68000, 'qty' => 5, 'status' => 'Menipis'],
                    ['code' => 'BRG-003', 'name' => 'Aqua Botol 600ml', 'cat' => 'Minuman', 'hpp' => 2800, 'qty' => 0, 'status' => 'Habis'],
                    ['code' => 'BRG-004', 'name' => 'Minyak Bimoli 2L', 'cat' => 'Sembako', 'hpp' => 35000, 'qty' => 45, 'status' => 'Aman'],
                    ['code' => 'BRG-005', 'name' => 'Teh Pucuk Harum', 'cat' => 'Minuman', 'hpp' => 3000, 'qty' => 12, 'status' => 'Menipis'],
                ];
                
                $total_asset = 0;
                @endphp

                @foreach($stocks as $s)
                @php 
                    $asset_value = $s['hpp'] * $s['qty'];
                    $total_asset += $asset_value;
                @endphp
                <tr>
                    <td><span class="fw-medium">{{ $s['code'] }}</span></td>
                    <td>{{ $s['name'] }}</td>
                    <td>{{ $s['cat'] }}</td>
                    <td>Rp {{ number_format($s['hpp'], 0, ',', '.') }}</td>
                    <td class="fw-bold">{{ $s['qty'] }}</td>
                    <td>Rp {{ number_format($asset_value, 0, ',', '.') }}</td>
                    <td>
                        @if($s['status'] == 'Aman')
                            <span class="badge bg-label-success">Aman</span>
                        @elseif($s['status'] == 'Menipis')
                            <span class="badge bg-label-warning">Menipis</span>
                        @else
                            <span class="badge bg-label-danger">Habis</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="5" class="text-end">TOTAL NILAI ASET (Tampil):</td>
                    <td colspan="2" class="text-primary">Rp {{ number_format($total_asset, 0, ',', '.') }}</td>
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
