@extends('layouts.app')

@section('title', 'Detail Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Produk /</span> Detail Produk
    </h4>
    <div>
        <a href="{{ route('products.edit', 1) }}" class="btn btn-primary me-2">
            <i class="bx bx-edit-alt me-1"></i> Edit
        </a>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    <!-- Profil Produk -->
    <div class="col-md-5 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="avatar avatar-xl bg-label-secondary mx-auto mb-4" style="width: 120px; height: 120px;">
                    <span class="avatar-initial rounded"><i class="bx bx-package" style="font-size: 4rem;"></i></span>
                </div>
                <h5 class="mb-1">Indomie Goreng Spesial</h5>
                <p class="text-muted mb-3">Sembako</p>
                <span class="badge bg-label-success px-3 py-1 mb-4">Aktif</span>
                
                <div class="d-flex justify-content-around align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h6 class="mb-0">150</h6>
                        <small class="text-muted">Stok Saat Ini</small>
                    </div>
                    <div>
                        <h6 class="mb-0 text-primary">Bungkus</h6>
                        <small class="text-muted">Satuan</small>
                    </div>
                </div>

                <div class="info-container text-start">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <span class="fw-bold me-2">Kode Produk:</span>
                            <span>BRG-001</span>
                        </li>
                        <li class="mb-3">
                            <span class="fw-bold me-2">Barcode:</span>
                            <span>89686010001</span>
                        </li>
                        <li class="mb-3">
                            <span class="fw-bold me-2">Minimum Stok:</span>
                            <span>10</span>
                        </li>
                        <li class="mb-3">
                            <span class="fw-bold me-2">Harga Beli:</span>
                            <span>Rp 2.500</span>
                        </li>
                        <li class="mb-3">
                            <span class="fw-bold me-2">Harga Jual:</span>
                            <span>Rp 3.000</span>
                        </li>
                        <li>
                            <span class="fw-bold me-2">Margin Keuntungan:</span>
                            <span class="text-success">Rp 500 (20%)</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik & Riwayat -->
    <div class="col-md-7 col-lg-8 mb-4">
        <div class="row g-4 mb-4">
            <div class="col-sm-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2 pb-1">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-trending-up"></i></span>
                            </div>
                            <h4 class="mb-0">342</h4>
                        </div>
                        <h6 class="mb-0">Total Terjual</h6>
                        <small class="text-muted">Bulan ini</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2 pb-1">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-success"><i class="bx bx-wallet"></i></span>
                            </div>
                            <h4 class="mb-0">Rp 171.000</h4>
                        </div>
                        <h6 class="mb-0">Total Keuntungan</h6>
                        <small class="text-muted">Bulan ini</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <h5 class="card-header border-bottom">Riwayat Stok Terakhir</h5>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Tipe</th>
                            <th>Keterangan</th>
                            <th>Qty</th>
                            <th>Sisa Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>07 Okt 2026, 10:30</td>
                            <td><span class="badge bg-label-danger">Keluar</span></td>
                            <td>Penjualan (TRX-005)</td>
                            <td class="text-danger">-5</td>
                            <td>150</td>
                        </tr>
                        <tr>
                            <td>06 Okt 2026, 14:15</td>
                            <td><span class="badge bg-label-success">Masuk</span></td>
                            <td>Pembelian (Supplier A)</td>
                            <td class="text-success">+100</td>
                            <td>155</td>
                        </tr>
                        <tr>
                            <td>05 Okt 2026, 09:20</td>
                            <td><span class="badge bg-label-danger">Keluar</span></td>
                            <td>Penjualan (TRX-042)</td>
                            <td class="text-danger">-12</td>
                            <td>55</td>
                        </tr>
                        <tr>
                            <td>01 Okt 2026, 08:00</td>
                            <td><span class="badge bg-label-warning">Penyesuaian</span></td>
                            <td>Barang Rusak</td>
                            <td class="text-danger">-3</td>
                            <td>67</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-center pt-3">
                <a href="{{ route('stock.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua Riwayat Stok</a>
            </div>
        </div>
    </div>
</div>
@endsection
