@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-0">
            <span class="text-muted fw-light">Laporan /</span> Laba Rugi Komprehensif (Income Statement)
        </h4>
        <small class="text-muted">Perhitungan pendapatan bersih, beban pokok penjualan (HPP), dan biaya operasional</small>
    </div>
    <div class="d-print-none d-flex gap-2">
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="bx bx-printer me-1"></i> Cetak Laporan
        </button>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-4 d-print-none">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.profit') }}" class="row gx-3 gy-2 align-items-end">
            <div class="col-md-4 col-sm-8">
                <label class="form-label" for="month">Bulan Laporan</label>
                <input type="month" id="month" name="month" class="form-control" value="{{ $month }}">
            </div>
            <div class="col-md-3 col-sm-4 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i>Terapkan</button>
                <a href="{{ route('reports.profit') }}" class="btn btn-outline-secondary" title="Reset"><i class="bx bx-reset"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <!-- Kolom Kiri: Rincian Formal Laba Rugi -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Laporan Laba Rugi Toko — Periode: {{ Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</h5>
            </div>
            <div class="card-body py-4">
                
                <!-- 1. PENDAPATAN (REVENUE) -->
                <h6 class="text-primary fw-bold text-uppercase mb-3">
                    <i class="bx bx-trending-up me-1"></i> 1. Pendapatan Penjualan (Revenue)
                </h6>
                <div class="d-flex justify-content-between mb-2 ps-4">
                    <span>Total Penjualan Kotor (Gross Sales)</span>
                    <span class="fw-semibold">Rp {{ number_format($grossSales, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 ps-4 text-danger">
                    <span>Potongan / Diskon Penjualan</span>
                    <span>(Rp {{ number_format($totalDiscounts, 0, ',', '.') }})</span>
                </div>
                <div class="d-flex justify-content-between mb-4 ps-4 fw-bold border-top pt-2">
                    <span>Total Pendapatan Bersih (Net Sales)</span>
                    <span class="text-primary">Rp {{ number_format($netSales, 0, ',', '.') }}</span>
                </div>

                <!-- 2. HARGA POKOK PENJUALAN (HPP) -->
                <h6 class="text-warning fw-bold text-uppercase mb-3">
                    <i class="bx bx-package me-1"></i> 2. Harga Pokok Penjualan (HPP)
                </h6>
                <div class="d-flex justify-content-between mb-2 ps-4 text-muted">
                    <span>Total Modal Barang Terjual (Cost of Goods Sold)</span>
                    <span>(Rp {{ number_format($totalHpp, 0, ',', '.') }})</span>
                </div>
                <div class="d-flex justify-content-between mb-4 ps-4 fw-bold border-top pt-2 text-success fs-6">
                    <span>LABA KOTOR (GROSS PROFIT)</span>
                    <span class="text-success">Rp {{ number_format($grossProfit, 0, ',', '.') }}</span>
                </div>

                <!-- 3. BIAYA OPERASIONAL (EXPENSES) -->
                <h6 class="text-danger fw-bold text-uppercase mb-3">
                    <i class="bx bx-trending-down me-1"></i> 3. Beban & Biaya Operasional (Operating Expenses)
                </h6>
                @forelse($expensesByCategory as $exp)
                <div class="d-flex justify-content-between mb-2 ps-4">
                    <span>Biaya {{ $exp->expense_category }}</span>
                    <span>(Rp {{ number_format($exp->total, 0, ',', '.') }})</span>
                </div>
                @empty
                <div class="d-flex justify-content-between mb-2 ps-4 text-muted">
                    <span>Biaya Operasional Toko</span>
                    <span>Rp 0</span>
                </div>
                @endforelse
                <div class="d-flex justify-content-between mb-4 ps-4 fw-bold border-top pt-2 text-danger">
                    <span>Total Beban Operasional</span>
                    <span>(Rp {{ number_format($totalExpenses, 0, ',', '.') }})</span>
                </div>

            </div>
            <div class="card-footer border-top bg-lighter">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold {{ $netProfit >= 0 ? 'text-primary' : 'text-danger' }}">
                            {{ $netProfit >= 0 ? 'LABA BERSIH (NET PROFIT)' : 'RUGI BERSIH (NET LOSS)' }}
                        </h5>
                        <small class="text-muted d-block mt-1">Laba Bersih = Laba Kotor - Beban Operasional</small>
                    </div>
                    <div class="text-end">
                        <h3 class="mb-0 fw-bold {{ $netProfit >= 0 ? 'text-primary' : 'text-danger' }}">
                            Rp {{ number_format($netProfit, 0, ',', '.') }}
                        </h3>
                        <small class="badge {{ $netProfit >= 0 ? 'bg-label-success' : 'bg-label-danger' }}">
                            Margin: {{ $profitMargin }}%
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Summary Metric Cards -->
    <div class="col-lg-4 mb-4">
        <div class="row g-4">
            <div class="col-12">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="d-block mb-1 text-white-50">Laba Kotor (Gross Profit)</span>
                                <h3 class="card-title text-white mb-0">Rp {{ number_format($grossProfit, 0, ',', '.') }}</h3>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-white text-success">
                                    <i class="bx bx-check-double fs-4"></i>
                                </span>
                            </div>
                        </div>
                        <small class="text-white-50 d-block mt-2">Sebelum dikurangi biaya operasional toko</small>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="d-block mb-1 text-white-50">Total Biaya Operasional</span>
                                <h3 class="card-title text-white mb-0">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</h3>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-white text-danger">
                                    <i class="bx bx-receipt fs-4"></i>
                                </span>
                            </div>
                        </div>
                        <small class="text-white-50 d-block mt-2">{{ $expensesByCategory->count() }} Kategori Pengeluaran</small>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card {{ $netProfit >= 0 ? 'bg-primary' : 'bg-danger' }} text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="d-block mb-1 text-white-50">Hasil Bersih Toko</span>
                                <h3 class="card-title text-white mb-0">Rp {{ number_format($netProfit, 0, ',', '.') }}</h3>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-white {{ $netProfit >= 0 ? 'text-primary' : 'text-danger' }}">
                                    <i class="bx bx-wallet fs-4"></i>
                                </span>
                            </div>
                        </div>
                        <small class="text-white-50 d-block mt-2">Margin Keuntungan Bersih: <strong>{{ $profitMargin }}%</strong></small>
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
        .layout-menu, .layout-navbar, .d-print-none, .btn, .footer { display: none !important; }
        .content-wrapper { padding: 0 !important; margin: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        @page { size: portrait; margin: 10mm; }
    }
</style>
@endsection
