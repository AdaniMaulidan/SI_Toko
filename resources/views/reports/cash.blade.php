@extends('layouts.app')

@section('title', 'Laporan Arus Kas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-0">
            <span class="text-muted fw-light">Laporan /</span> Arus Kas (Cash Flow Statement)
        </h4>
        <small class="text-muted">Ringkasan saldo awal kas, penerimaan kas masuk, pengeluaran kas keluar, dan saldo akhir toko</small>
    </div>
    <div class="d-print-none d-flex gap-2 flex-wrap">
        <a href="{{ route('reports.cash.excel', request()->all()) }}" class="btn btn-outline-success">
            <i class="bx bxs-file-export me-1"></i> Excel (.csv)
        </a>
        <a href="{{ route('reports.cash.pdf', request()->all()) }}" class="btn btn-outline-danger" target="_blank">
            <i class="bx bxs-file-pdf me-1"></i> Unduh PDF
        </a>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="bx bx-printer me-1"></i> Cetak Laporan
        </button>
    </div>
</div>

<!-- Print-Only Formal Header -->
<div class="d-none d-print-block mb-4 pb-2 border-bottom">
    <div class="text-center">
        <h3 class="fw-bold mb-1">{{ \App\Models\StoreSetting::getSettings()['store_name'] ?? 'SI TOKO' }}</h3>
        <p class="mb-1 text-muted">{{ \App\Models\StoreSetting::getSettings()['address'] ?? '' }} | Telp: {{ \App\Models\StoreSetting::getSettings()['phone'] ?? '' }}</p>
        <h4 class="fw-bold mt-3 mb-1 text-uppercase">LAPORAN ARUS KAS (CASH FLOW)</h4>
        <small class="text-muted">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</small>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-4 d-print-none">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.cash') }}" class="row gx-3 gy-2 align-items-end">
            <div class="col-md-4 col-sm-6">
                <label class="form-label" for="start_date">Tanggal Mulai</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-4 col-sm-6">
                <label class="form-label" for="end_date">Tanggal Akhir</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-4 col-sm-12 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i>Filter Laporan</button>
                <a href="{{ route('reports.cash') }}" class="btn btn-outline-secondary" title="Reset"><i class="bx bx-reset"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- 3 Summary Cards -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
        <div class="card bg-secondary text-white h-100">
            <div class="card-body">
                <span class="text-white-50 d-block mb-1">Saldo Awal Kas</span>
                <h4 class="text-white fw-bold mb-0">Rp {{ number_format($openingBalance, 0, ',', '.') }}</h4>
                <small class="text-white-50">Per {{ Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }}</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <span class="text-white-50 d-block mb-1">Total Kas Masuk (In)</span>
                <h4 class="text-white fw-bold mb-0">+ Rp {{ number_format($totalInflow, 0, ',', '.') }}</h4>
                <small class="text-white-50">{{ $inflowsByCategory->count() }} Kategori Sumber</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <span class="text-white-50 d-block mb-1">Total Kas Keluar (Out)</span>
                <h4 class="text-white fw-bold mb-0">- Rp {{ number_format($totalOutflow, 0, ',', '.') }}</h4>
                <small class="text-white-50">{{ $outflowsByCategory->count() }} Kategori Pengeluaran</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <span class="text-white-50 d-block mb-1">Saldo Akhir (Cash on Hand)</span>
                <h4 class="text-white fw-bold mb-0">Rp {{ number_format($closingBalance, 0, ',', '.') }}</h4>
                <small class="text-white-50">Per {{ Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Rekapitulasi Kas Formal -->
    <div class="col-lg-12 mb-4">
        <div class="card h-100">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Laporan Arus Kas Periode {{ Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} s/d {{ Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}</h5>
            </div>
            <div class="card-body py-4">
                
                <div class="d-flex justify-content-between mb-3 ps-3 pe-3 bg-light p-2 rounded">
                    <span class="fw-bold">SALDO AWAL KAS (Per {{ Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }})</span>
                    <span class="fw-bold fs-6">Rp {{ number_format($openingBalance, 0, ',', '.') }}</span>
                </div>

                <!-- 1. ARUS KAS MASUK -->
                <h6 class="text-success fw-bold text-uppercase mt-4 mb-3">
                    <i class="bx bx-down-arrow-alt me-1"></i> Arus Kas Masuk (Inflow)
                </h6>
                @forelse($inflowsByCategory as $in)
                <div class="d-flex justify-content-between mb-2 ps-4">
                    <span>Penerimaan {{ $in->category }}</span>
                    <span class="fw-semibold text-success">+ Rp {{ number_format($in->total, 0, ',', '.') }}</span>
                </div>
                @empty
                <div class="d-flex justify-content-between mb-2 ps-4 text-muted">
                    <span>Tidak ada penerimaan kas masuk</span>
                    <span>Rp 0</span>
                </div>
                @endforelse
                <div class="d-flex justify-content-between mb-4 ps-4 fw-bold border-top pt-2 text-success">
                    <span>TOTAL ARUS KAS MASUK</span>
                    <span>+ Rp {{ number_format($totalInflow, 0, ',', '.') }}</span>
                </div>

                <!-- 2. ARUS KAS KELUAR -->
                <h6 class="text-danger fw-bold text-uppercase mt-4 mb-3">
                    <i class="bx bx-up-arrow-alt me-1"></i> Arus Kas Keluar (Outflow)
                </h6>
                @forelse($outflowsByCategory as $out)
                <div class="d-flex justify-content-between mb-2 ps-4">
                    <span>Pengeluaran {{ $out->category }}</span>
                    <span class="fw-semibold text-danger">- Rp {{ number_format($out->total, 0, ',', '.') }}</span>
                </div>
                @empty
                <div class="d-flex justify-content-between mb-2 ps-4 text-muted">
                    <span>Tidak ada pengeluaran kas keluar</span>
                    <span>Rp 0</span>
                </div>
                @endforelse
                <div class="d-flex justify-content-between mb-4 ps-4 fw-bold border-top pt-2 text-danger">
                    <span>TOTAL ARUS KAS KELUAR</span>
                    <span>- Rp {{ number_format($totalOutflow, 0, ',', '.') }}</span>
                </div>

                <!-- SURPLUS / DEFISIT -->
                <div class="d-flex justify-content-between mb-4 ps-4 pe-3 fw-bold border-top pt-3 {{ $netCashChange >= 0 ? 'text-primary' : 'text-danger' }}">
                    <span>KENAIKAN / (PENURUNAN) BERSIH KAS (NET CHANGE)</span>
                    <span class="fs-6">{{ $netCashChange >= 0 ? '+' : '' }} Rp {{ number_format($netCashChange, 0, ',', '.') }}</span>
                </div>

            </div>
            <div class="card-footer border-top bg-lighter">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold text-primary">SALDO AKHIR KAS (CASH ON HAND)</h5>
                        <small class="text-muted d-block mt-1">Saldo Akhir = Saldo Awal + Total Kas Masuk - Total Kas Keluar</small>
                    </div>
                    <h3 class="mb-0 fw-bold text-primary">Rp {{ number_format($closingBalance, 0, ',', '.') }}</h3>
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
