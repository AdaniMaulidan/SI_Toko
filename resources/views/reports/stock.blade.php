@extends('layouts.app')

@section('title', 'Laporan Stok & Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-0">
            <span class="text-muted fw-light">Laporan /</span> Stok & Valuasi Aset
        </h4>
        <small class="text-muted">Analisis valuasi aset gudang berbasis HPP, status stok kritis, dan proyeksi potensi laba</small>
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
        <form method="GET" action="{{ route('reports.stock') }}" class="row gx-3 gy-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label" for="category_id">Kategori Produk</label>
                <select id="category_id" name="category_id" class="form-select">
                    <option value="all">Semua Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label" for="status">Status Stok</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="habis" {{ request('status') == 'habis' ? 'selected' : '' }}>Habis (Stok 0)</option>
                    <option value="menipis" {{ request('status') == 'menipis' ? 'selected' : '' }}>Menipis (Stok <= Min)</option>
                    <option value="aman" {{ request('status') == 'aman' ? 'selected' : '' }}>Aman (Stok > Min)</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label" for="sort">Urutkan Berdasarkan</label>
                <select id="sort" name="sort" class="form-select">
                    <option value="qty_asc" {{ request('sort') == 'qty_asc' ? 'selected' : '' }}>Sisa Stok Terkecil</option>
                    <option value="qty_desc" {{ request('sort') == 'qty_desc' ? 'selected' : '' }}>Sisa Stok Terbesar</option>
                    <option value="value_desc" {{ request('sort') == 'value_desc' ? 'selected' : '' }}>Nilai Aset Terbesar</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                <a href="{{ route('reports.stock') }}" class="btn btn-outline-secondary" title="Reset"><i class="bx bx-reset"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <h6 class="text-white mb-2">Total Nilai Aset Stok (Modal HPP)</h6>
                <h3 class="text-white fw-bold mb-2">Rp {{ number_format($totalAssetValue, 0, ',', '.') }}</h3>
                <div class="d-flex justify-content-between align-items-center text-white-50 border-top pt-2 mt-2">
                    <small>Nilai Jual Retail: Rp {{ number_format($totalRetailValue, 0, ',', '.') }}</small>
                    <small class="text-white fw-bold">Potensi Laba: +Rp {{ number_format($potentialProfit, 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="row g-3 h-100">
            <div class="col-4">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body text-center p-3">
                        <i class="bx bx-error fs-2 mb-1"></i>
                        <h3 class="text-white fw-bold mb-0">{{ $lowStockCount }}</h3>
                        <small class="d-block lh-sm">Item Menipis</small>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body text-center p-3">
                        <i class="bx bx-x-circle fs-2 mb-1"></i>
                        <h3 class="text-white fw-bold mb-0">{{ $outOfStockCount }}</h3>
                        <small class="d-block lh-sm">Item Habis</small>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card bg-success text-white h-100">
                    <div class="card-body text-center p-3">
                        <i class="bx bx-check-circle fs-2 mb-1"></i>
                        <h3 class="text-white fw-bold mb-0">{{ $safeStockCount }}</h3>
                        <small class="d-block lh-sm">Stok Aman</small>
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
        <table class="table table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>Barcode</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th class="text-end">Harga Modal (HPP)</th>
                    <th class="text-end">Harga Jual</th>
                    <th class="text-center">Sisa Qty</th>
                    <th class="text-end">Nilai Aset Modal</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($products as $prod)
                <tr>
                    <td><code>{{ $prod->barcode }}</code></td>
                    <td>
                        <a href="{{ route('products.show', $prod->id) }}" class="fw-semibold text-primary">
                            {{ $prod->name }}
                        </a>
                    </td>
                    <td>{{ $prod->category->name ?? '-' }}</td>
                    <td class="text-end">Rp {{ number_format($prod->cost_price, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</td>
                    <td class="text-center fw-bold">
                        {{ $prod->stock }} <small class="text-muted">{{ $prod->unit->symbol ?? 'Pcs' }}</small>
                    </td>
                    <td class="text-end fw-bold text-primary">
                        Rp {{ number_format($prod->stock * $prod->cost_price, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @if($prod->stock <= 0)
                            <span class="badge bg-danger">Habis</span>
                        @elseif($prod->stock <= $prod->min_stock)
                            <span class="badge bg-warning text-dark">Menipis</span>
                        @else
                            <span class="badge bg-success">Aman</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="bx bx-package fs-4 d-block mb-1"></i>
                        Tidak ada produk yang cocok dengan kriteria filter.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
    <div class="card-footer d-flex justify-content-center d-print-none">
        {{ $products->links() }}
    </div>
    @endif
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
