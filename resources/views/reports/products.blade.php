@extends('layouts.app')

@section('title', 'Laporan Kinerja Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-0">
            <span class="text-muted fw-light">Laporan /</span> Kinerja Produk Terlaris (Top Selling)
        </h4>
        <small class="text-muted">Peringkat produk terlaris berdasarkan volume kuantitas penjualan dan total omset</small>
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
        <form method="GET" action="{{ route('reports.products') }}" class="row gx-3 gy-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label" for="start_date">Tanggal Mulai</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label" for="end_date">Tanggal Akhir</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label" for="category_id">Kategori</label>
                <select id="category_id" name="category_id" class="form-select">
                    <option value="all">Semua Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-sm-6 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                <a href="{{ route('reports.products') }}" class="btn btn-outline-secondary" title="Reset"><i class="bx bx-reset"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <!-- Top 10 Terlaris Berdasarkan Kuantitas (Volume) -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0 text-success">
                    <i class="bx bx-trending-up me-1"></i> Top 10 Produk Terlaris (Berdasarkan Qty)
                </h5>
                <small class="text-muted">Volume produk paling banyak dibeli konsumen</small>
            </div>
            <div class="card-body mt-3 p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="60">Rank</th>
                                <th>Produk</th>
                                <th class="text-center">Terjual</th>
                                <th class="text-end">Omset</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topByQty as $idx => $item)
                            <tr>
                                <td>
                                    @if($idx === 0)
                                        <span class="badge rounded-pill bg-warning text-dark"><i class="bx bxs-trophy"></i> #1</span>
                                    @elseif($idx === 1)
                                        <span class="badge rounded-pill bg-secondary">#2</span>
                                    @elseif($idx === 2)
                                        <span class="badge rounded-pill bg-label-secondary">#3</span>
                                    @else
                                        <span class="text-muted ps-2">#{{ $idx + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-truncate" style="max-width: 180px;" title="{{ $item->product_name }}">
                                        {{ $item->product_name }}
                                    </div>
                                    <small class="text-muted">{{ $item->category_name ?? '-' }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-success fw-bold">
                                        {{ number_format($item->total_qty) }} {{ $item->unit_symbol ?? 'Pcs' }}
                                    </span>
                                </td>
                                <td class="text-end fw-semibold">
                                    Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bx bx-info-circle fs-4 d-block mb-1"></i>
                                    Belum ada data penjualan pada periode ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 10 Terlaris Berdasarkan Nilai Omset (Revenue) -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0 text-primary">
                    <i class="bx bx-dollar-circle me-1"></i> Top 10 Produk Kontribusi Omset Tertinggi
                </h5>
                <small class="text-muted">Produk yang menghasilkan perputaran uang terbesar</small>
            </div>
            <div class="card-body mt-3 p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="60">Rank</th>
                                <th>Produk</th>
                                <th class="text-end">Total Omset</th>
                                <th class="text-end">Laba Kotor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topByRevenue as $idx => $item)
                            <tr>
                                <td>
                                    @if($idx === 0)
                                        <span class="badge rounded-pill bg-primary"><i class="bx bxs-crown"></i> #1</span>
                                    @elseif($idx === 1)
                                        <span class="badge rounded-pill bg-info">#2</span>
                                    @elseif($idx === 2)
                                        <span class="badge rounded-pill bg-label-info">#3</span>
                                    @else
                                        <span class="text-muted ps-2">#{{ $idx + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-truncate" style="max-width: 180px;" title="{{ $item->product_name }}">
                                        {{ $item->product_name }}
                                    </div>
                                    <small class="text-muted">{{ number_format($item->total_qty) }} {{ $item->unit_symbol ?? 'Pcs' }}</small>
                                </td>
                                <td class="text-end fw-bold text-primary">
                                    Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                                </td>
                                <td class="text-end fw-semibold text-success">
                                    +Rp {{ number_format($item->gross_profit, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bx bx-info-circle fs-4 d-block mb-1"></i>
                                    Belum ada data penjualan pada periode ini.
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

@section('page-style')
<style>
    @media print {
        .layout-menu, .layout-navbar, .d-print-none, .btn, .footer { display: none !important; }
        .content-wrapper { padding: 0 !important; margin: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        @page { size: landscape; margin: 10mm; }
    }
</style>
@endsection
