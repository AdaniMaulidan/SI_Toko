@extends('layouts.app')

@section('title', 'Manajemen & Status Stok Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Stok /</span> Status Stok Barang
    </h4>
    <a href="{{ route('stock.adjustment') }}" class="btn btn-primary shadow-sm">
        <i class="bx bx-adjust me-1"></i> Penyesuaian Stok (Stock Opname)
    </a>
</div>

{{-- Flash Alert Messages --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- 1. Top Statistics Metric Cards -->
<div class="row g-3 mb-4">
    <!-- Total Item Produk -->
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Total Item Produk</span>
                    <div class="avatar avatar-sm bg-label-primary rounded">
                        <i class="bx bx-package fs-4 text-primary"></i>
                    </div>
                </div>
                <h4 class="fw-bolder mb-1">{{ number_format($stats['total_items'], 0, ',', '.') }}</h4>
                <small class="text-muted">Master data barang aktif</small>
            </div>
        </div>
    </div>

    <!-- Stok Aman -->
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Stok Aman</span>
                    <div class="avatar avatar-sm bg-label-success rounded">
                        <i class="bx bx-check-circle fs-4 text-success"></i>
                    </div>
                </div>
                <h4 class="fw-bolder mb-1 text-success">{{ number_format($stats['safe_stock'], 0, ',', '.') }}</h4>
                <small class="text-muted">Stok di atas batas minimum</small>
            </div>
        </div>
    </div>

    <!-- Stok Menipis -->
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm border-bottom border-warning border-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Stok Menipis</span>
                    <div class="avatar avatar-sm bg-label-warning rounded">
                        <i class="bx bx-error fs-4 text-warning"></i>
                    </div>
                </div>
                <h4 class="fw-bolder mb-1 text-warning">{{ number_format($stats['low_stock'], 0, ',', '.') }}</h4>
                <small class="text-muted">Perlu segera kulakan ulang</small>
            </div>
        </div>
    </div>

    <!-- Stok Habis -->
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm border-bottom border-danger border-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Stok Habis</span>
                    <div class="avatar avatar-sm bg-label-danger rounded">
                        <i class="bx bx-x-circle fs-4 text-danger"></i>
                    </div>
                </div>
                <h4 class="fw-bolder mb-1 text-danger">{{ number_format($stats['out_of_stock'], 0, ',', '.') }}</h4>
                <small class="text-muted">Kuantitas 0 atau kosong</small>
            </div>
        </div>
    </div>
</div>

<!-- 2. Filter Form Card -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 d-flex align-items-center gap-1">
            <i class="bx bx-filter-alt text-primary"></i> Filter Stok Produk
        </h6>
        @if(request()->hasAny(['category_id', 'status', 'q']))
            <a href="{{ route('stock.index') }}" class="btn btn-xs btn-outline-danger">
                <i class="bx bx-refresh me-1"></i>Reset Filter
            </a>
        @endif
    </div>
    <div class="card-body pt-3">
        <form method="GET" action="{{ route('stock.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted" for="category_id">Kategori Produk</label>
                    <select id="category_id" name="category_id" class="form-select form-select-sm">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted" for="status">Status Stok</label>
                    <select id="status" name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="aman" {{ request('status') === 'aman' ? 'selected' : '' }}>Aman</option>
                        <option value="menipis" {{ request('status') === 'menipis' ? 'selected' : '' }}>Menipis (<= Min Stok)</option>
                        <option value="habis" {{ request('status') === 'habis' ? 'selected' : '' }}>Habis (0 Pcs)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted" for="q">Cari Nama atau Barcode</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bx bx-search"></i></span>
                        <input type="text" name="q" id="q" class="form-control" placeholder="Barcode atau nama produk..." value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bx bx-filter-alt me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['category_id', 'status', 'q']))
                        <a href="{{ route('stock.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                            <i class="bx bx-refresh"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 3. Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0">Daftar Stok Barang</h5>
            <small class="text-muted">Total {{ $products->total() }} produk terdaftar</small>
        </div>
    </div>
    
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Barcode</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th class="text-end">Harga Beli</th>
                    <th class="text-end">Harga Jual</th>
                    <th class="text-center">Min. Stok</th>
                    <th class="text-center">Sisa Stok</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                <tr>
                    <td>
                        <span class="font-monospace text-muted small">{{ $p->barcode }}</span>
                    </td>
                    <td>
                        <span class="fw-semibold text-heading d-block">{{ $p->name }}</span>
                    </td>
                    <td>
                        <span class="badge bg-label-secondary">{{ $p->category?->name ?? 'Umum' }}</span>
                    </td>
                    <td class="text-end">
                        <span class="text-muted small">Rp {{ number_format($p->cost_price, 0, ',', '.') }}</span>
                    </td>
                    <td class="text-end">
                        <span class="fw-bold text-dark">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</span>
                    </td>
                    <td class="text-center">
                        <span class="font-monospace">{{ $p->min_stock }}</span>
                    </td>
                    <td class="text-center">
                        <span class="fw-bolder fs-6 {{ $p->stock <= 0 ? 'text-danger' : ($p->stock <= $p->min_stock ? 'text-warning' : 'text-success') }}">
                            {{ $p->stock }}
                        </span>
                        <small class="text-muted">{{ $p->unit?->symbol ?? 'Pcs' }}</small>
                    </td>
                    <td class="text-center">
                        @if($p->stock <= 0)
                            <span class="badge bg-danger"><i class="bx bx-x-circle me-1"></i>Habis</span>
                        @elseif($p->stock <= $p->min_stock)
                            <span class="badge bg-warning text-dark"><i class="bx bx-error me-1"></i>Menipis</span>
                        @else
                            <span class="badge bg-success"><i class="bx bx-check me-1"></i>Aman</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('stock.adjustment', ['product_id' => $p->id]) }}" 
                           class="btn btn-xs btn-outline-primary" 
                           title="Lakukan Stock Opname untuk produk ini">
                            <i class="bx bx-adjust me-1"></i> Opname
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bx bx-package fs-1 mb-2 text-secondary"></i>
                            <h6>Tidak ada data produk ditemukan.</h6>
                            <p class="small text-muted mb-0">Coba sesuaikan filter pencarian atau status stok.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($products->hasPages())
    <div class="card-footer border-top d-flex justify-content-between align-items-center flex-wrap gap-2 py-2">
        <small class="text-muted">
            Menampilkan data ke {{ $products->firstItem() }} sampai {{ $products->lastItem() }} dari {{ $products->total() }} barang
        </small>
        <div>
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>
@endsection
