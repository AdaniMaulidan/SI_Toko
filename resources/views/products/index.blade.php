@extends('layouts.app')

@section('title', 'Manajemen Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-1">
            <span class="text-muted fw-light">Master Data /</span> Produk
        </h4>
        <p class="text-muted mb-0 small">Kelola katalog barang dagangan, barcode, harga, dan kontrol stok minimum.</p>
    </div>
    <a href="{{ route('products.create') }}" class="btn btn-primary">
        <i class="bx bx-plus me-1"></i> Tambah Produk
    </a>
</div>

{{-- Flash Messages --}}
@if (session('success'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
    <i class="bx bx-check-circle fs-4 me-2"></i>
    <div>{{ session('success') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
    <i class="bx bx-error-circle fs-4 me-2"></i>
    <div>{{ session('error') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-primary rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-package fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Total Produk</span>
                    <h4 class="card-title mb-0 fw-bold">{{ number_format($stats['total']) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-success rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-check-shield fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Produk Aktif</span>
                    <h4 class="card-title mb-0 fw-bold">{{ number_format($stats['active']) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-warning rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-error-circle fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Stok Menipis</span>
                    <h4 class="card-title mb-0 fw-bold text-warning">{{ number_format($stats['low_stock']) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-danger rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-x-circle fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Stok Habis</span>
                    <h4 class="card-title mb-0 fw-bold text-danger">{{ number_format($stats['out_stock']) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('products.index') }}" class="row gx-3 gy-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label" for="category">Kategori</label>
                <select name="category" id="category" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label" for="stock_status">Status Stok</label>
                <select name="stock_status" id="stock_status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="safe" {{ request('stock_status') == 'safe' ? 'selected' : '' }}>Aman (> Min Stok)</option>
                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Menipis (≤ Min Stok)</option>
                    <option value="empty" {{ request('stock_status') == 'empty' ? 'selected' : '' }}>Habis (0)</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label" for="status">Status Produk</label>
                <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-4 col-sm-6">
                <label class="form-label" for="search">Cari Produk</label>
                <div class="input-group">
                    <input type="text" name="search" id="search" class="form-control" placeholder="Nama, barcode..." value="{{ request('search') }}">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bx bx-search"></i>
                    </button>
                    @if(request()->hasAny(['search', 'category', 'stock_status', 'status']))
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bx bx-refresh"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card shadow-sm border-0">
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Produk Info</th>
                    <th>Kategori / Satuan</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 80px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $index => $product)
                <tr>
                    <td>{{ $products->firstItem() + $index }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="rounded me-3 object-fit-cover" width="46" height="46">
                            @else
                                <div class="avatar avatar-md me-3 bg-label-secondary rounded d-flex align-items-center justify-content-center">
                                    <i class="bx bx-package fs-4"></i>
                                </div>
                            @endif
                            <div>
                                <a href="{{ route('products.show', $product) }}" class="fw-bold text-heading d-block text-truncate" style="max-width: 240px;">
                                    {{ $product->name }}
                                </a>
                                <span class="badge bg-label-secondary text-body font-monospace small">
                                    <i class="bx bx-barcode me-1"></i>{{ $product->barcode }}
                                </span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div>
                            <span class="badge bg-label-primary mb-1">{{ $product->category->name ?? '-' }}</span>
                            <div class="small text-muted">{{ $product->unit->name ?? '-' }} ({{ $product->unit->symbol ?? '' }})</div>
                        </div>
                    </td>
                    <td>Rp {{ number_format($product->cost_price, 0, ',', '.') }}</td>
                    <td class="fw-semibold text-primary">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                    <td>
                        @if($product->stock <= 0)
                            <span class="badge bg-label-danger d-inline-flex align-items-center" title="Stok Kosong!">
                                <i class="bx bx-x-circle me-1"></i> Habis (0)
                            </span>
                        @elseif($product->stock <= $product->min_stock)
                            <span class="badge bg-label-warning d-inline-flex align-items-center" title="Stok di bawah batas minimum ({{ $product->min_stock }})">
                                <i class="bx bx-error me-1"></i> {{ $product->stock }} (Menipis)
                            </span>
                        @else
                            <span class="badge bg-label-success d-inline-flex align-items-center">
                                <i class="bx bx-check-circle me-1"></i> {{ $product->stock }}
                            </span>
                        @endif
                        <div class="text-muted small">Min: {{ $product->min_stock }}</div>
                    </td>
                    <td>
                        @if($product->is_active)
                            <span class="badge bg-label-success">Aktif</span>
                        @else
                            <span class="badge bg-label-secondary">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-dots-vertical-rounded fs-5 text-muted"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('products.show', $product) }}">
                                    <i class="bx bx-show me-2 text-info"></i> Detail
                                </a>
                                <a class="dropdown-item" href="{{ route('products.edit', $product) }}">
                                    <i class="bx bx-edit-alt me-2 text-primary"></i> Edit
                                </a>
                                <div class="dropdown-divider"></div>
                                <button type="button" 
                                        class="dropdown-item text-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal{{ $product->id }}">
                                    <i class="bx bx-trash me-2"></i> Hapus
                                </button>
                            </div>
                        </div>

                        <!-- Modal Konfirmasi Hapus -->
                        <div class="modal fade" id="deleteModal{{ $product->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header border-0 pb-0">
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center pt-0">
                                        <div class="avatar avatar-xl bg-label-danger mx-auto mb-3">
                                            <i class="bx bx-trash fs-2"></i>
                                        </div>
                                        <h5 class="modal-title mb-2">Hapus Produk?</h5>
                                        <p class="text-muted small mb-3">
                                            Apakah Anda yakin ingin menghapus <strong>{{ $product->name }}</strong>? Data yang dihapus tidak dapat dipulihkan.
                                        </p>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bx bx-package fs-1 d-block mb-2 text-secondary"></i>
                            <h6 class="fw-bold mb-1">Tidak ada produk ditemukan</h6>
                            <p class="small mb-3">
                                @if(request()->hasAny(['search', 'category', 'stock_status', 'status']))
                                    Coba ubah kata kunci pencarian atau reset filter.
                                @else
                                    Mulai dengan menambahkan produk baru ke katalog Anda.
                                @endif
                            </p>
                            @if(request()->hasAny(['search', 'category', 'stock_status', 'status']))
                                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary me-2">Reset Filter</a>
                            @endif
                            <a href="{{ route('products.create') }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-plus me-1"></i> Tambah Produk
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-3">
        <small class="text-muted">
            Menampilkan {{ $products->firstItem() }} sampai {{ $products->lastItem() }} dari {{ $products->total() }} produk
        </small>
        <div>
            {{ $products->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
