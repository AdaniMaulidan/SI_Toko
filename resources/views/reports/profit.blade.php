@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Laporan /</span> Laba Rugi (Profit)
    </h4>
    <button type="button" class="btn btn-primary" onclick="window.print()">
        <i class="bx bx-printer me-1"></i> Cetak Laporan
    </button>
</div>

<!-- Filter Card -->
<div class="card mb-4 d-print-none">
    <div class="card-body">
        <form class="row gx-3 gy-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Bulan</label>
                <input type="month" class="form-control" value="{{ date('Y-m') }}">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-primary w-100">Terapkan</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <!-- Kolom Kiri: Rincian Laba Rugi -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Rincian Laba Rugi - Oktober 2026</h5>
            </div>
            <div class="card-body py-4">
                
                <!-- PENDAPATAN -->
                <h6 class="text-primary fw-bold text-uppercase mb-3"><i class="bx bx-trending-up me-1"></i> Pendapatan (Revenue)</h6>
                <div class="d-flex justify-content-between mb-2 ps-4">
                    <span>Total Penjualan Kotor</span>
                    <span>Rp 28.500.000</span>
                </div>
                <div class="d-flex justify-content-between mb-2 ps-4 text-danger">
                    <span>Diskon Diberikan</span>
                    <span>(Rp 50.000)</span>
                </div>
                <div class="d-flex justify-content-between mb-4 ps-4 fw-bold border-top pt-2">
                    <span>Total Pendapatan Bersih (Net Sales)</span>
                    <span>Rp 28.450.000</span>
                </div>

                <!-- BEBAN HPP -->
                <h6 class="text-warning fw-bold text-uppercase mb-3"><i class="bx bx-package me-1"></i> Harga Pokok Penjualan (HPP)</h6>
                <div class="d-flex justify-content-between mb-2 ps-4 text-muted">
                    <span>Total HPP Barang Terjual</span>
                    <span>(Rp 18.000.000)</span>
                </div>
                <div class="d-flex justify-content-between mb-4 ps-4 fw-bold border-top pt-2 text-success fs-6">
                    <span>Laba Kotor (Gross Profit)</span>
                    <span>Rp 10.450.000</span>
                </div>

                <!-- PENGELUARAN -->
                <h6 class="text-danger fw-bold text-uppercase mb-3"><i class="bx bx-trending-down me-1"></i> Biaya Operasional (Expenses)</h6>
                <div class="d-flex justify-content-between mb-2 ps-4">
                    <span>Gaji Karyawan</span>
                    <span>(Rp 2.500.000)</span>
                </div>
                <div class="d-flex justify-content-between mb-2 ps-4">
                    <span>Listrik, Air & Internet</span>
                    <span>(Rp 850.000)</span>
                </div>
                <div class="d-flex justify-content-between mb-2 ps-4">
                    <span>Perlengkapan Toko</span>
                    <span>(Rp 125.000)</span>
                </div>
                <div class="d-flex justify-content-between mb-4 ps-4 fw-bold border-top pt-2 text-danger">
                    <span>Total Biaya Operasional</span>
                    <span>(Rp 3.475.000)</span>
                </div>

            </div>
            <div class="card-footer border-top bg-lighter">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary">LABA BERSIH (NET PROFIT)</h5>
                    <h4 class="mb-0 fw-bold text-primary">Rp 6.975.000</h4>
                </div>
                <small class="text-muted d-block mt-2">Laba Bersih = Laba Kotor - Total Biaya Operasional</small>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Summary Cards -->
    <div class="col-lg-4 mb-4">
        <div class="row g-4">
            <div class="col-12">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white mb-2">Margin Laba Bersih</h6>
                        <h2 class="text-white fw-bold mb-0">24.5%</h2>
                        <small class="mt-1 d-block">Dari total penjualan bersih</small>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Perbandingan Bulan Lalu</h6>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="badge rounded-pill bg-label-success me-3 p-2">
                                <i class="bx bx-up-arrow-alt fs-5"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <small>Laba Bersih</small>
                                <h6 class="mb-0 fw-bold text-success">+ 15.2%</h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="badge rounded-pill bg-label-danger me-3 p-2">
                                <i class="bx bx-up-arrow-alt fs-5"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <small>Pengeluaran</small>
                                <h6 class="mb-0 fw-bold text-danger">+ 5.0%</h6>
                            </div>
                        </div>
                    </div>
                </div>
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
