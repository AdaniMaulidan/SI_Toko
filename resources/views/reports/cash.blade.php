@extends('layouts.app')

@section('title', 'Laporan Arus Kas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Laporan /</span> Arus Kas (Cash Flow)
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
                <button type="button" class="btn btn-primary w-100">Filter Laporan</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <!-- Rekapitulasi Kas -->
    <div class="col-lg-12 mb-4">
        <div class="card h-100">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Ringkasan Arus Kas - Periode Berjalan</h5>
            </div>
            <div class="card-body py-4">
                
                <div class="d-flex justify-content-between mb-2 ps-4">
                    <span class="fw-bold">Saldo Awal (Per 1 Okt 2026)</span>
                    <span class="fw-bold">Rp 10.000.000</span>
                </div>
                
                <hr>

                <!-- ARUS MASUK -->
                <h6 class="text-success fw-bold text-uppercase mb-3"><i class="bx bx-down-arrow-alt me-1"></i> Kas Masuk (Inflow)</h6>
                <div class="d-flex justify-content-between mb-2 ps-4">
                    <span>Penerimaan Penjualan Tunai</span>
                    <span>Rp 25.000.000</span>
                </div>
                <div class="d-flex justify-content-between mb-2 ps-4">
                    <span>Pembayaran Piutang Pelanggan</span>
                    <span>Rp 1.500.000</span>
                </div>
                <div class="d-flex justify-content-between mb-2 ps-4">
                    <span>Lain-lain / Modal Awal Tambahan</span>
                    <span>Rp 0</span>
                </div>
                <div class="d-flex justify-content-between mb-4 ps-4 fw-bold border-top pt-2 text-success">
                    <span>Total Kas Masuk</span>
                    <span>Rp 26.500.000</span>
                </div>

                <!-- ARUS KELUAR -->
                <h6 class="text-danger fw-bold text-uppercase mb-3"><i class="bx bx-up-arrow-alt me-1"></i> Kas Keluar (Outflow)</h6>
                <div class="d-flex justify-content-between mb-2 ps-4 text-muted">
                    <span>Pembayaran Supplier (Kulakan)</span>
                    <span>(Rp 12.000.000)</span>
                </div>
                <div class="d-flex justify-content-between mb-2 ps-4 text-muted">
                    <span>Biaya Operasional Toko (Listrik, Gaji, dll)</span>
                    <span>(Rp 3.500.000)</span>
                </div>
                <div class="d-flex justify-content-between mb-2 ps-4 text-muted">
                    <span>Setor Bank / Prive (Penarikan Owner)</span>
                    <span>(Rp 8.500.000)</span>
                </div>
                <div class="d-flex justify-content-between mb-4 ps-4 fw-bold border-top pt-2 text-danger">
                    <span>Total Kas Keluar</span>
                    <span>(Rp 24.000.000)</span>
                </div>

            </div>
            <div class="card-footer border-top bg-lighter">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary">SALDO AKHIR (CASH ON HAND)</h5>
                    <h4 class="mb-0 fw-bold text-primary">Rp 12.500.000</h4>
                </div>
                <small class="text-muted d-block mt-2">Saldo Akhir = Saldo Awal + Total Kas Masuk - Total Kas Keluar</small>
            </div>
        </div>
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
