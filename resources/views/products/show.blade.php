@extends('layouts.app')

@section('title', 'Detail Produk - ' . $product->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-1">
            <span class="text-muted fw-light">Master Data / Produk /</span> Detail Produk
        </h4>
        <p class="text-muted mb-0 small">Rincian spesifikasi barang, status stok, dan parameter harga.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
            <i class="bx bx-edit-alt me-1"></i> Edit Produk
        </a>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    <!-- Profil Produk -->
    <div class="col-md-5 col-lg-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center p-4">
                
                {{-- Foto / Avatar --}}
                <div class="mx-auto mb-4 position-relative d-inline-block">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" 
                             alt="{{ $product->name }}" 
                             class="rounded shadow-sm object-fit-cover" 
                             style="width: 140px; height: 140px; border: 3px solid #eceef1;" />
                    @else
                        <div class="avatar avatar-xl bg-label-secondary mx-auto d-flex align-items-center justify-content-center rounded" 
                             style="width: 140px; height: 140px;">
                            <i class="bx bx-package" style="font-size: 4.5rem;"></i>
                        </div>
                    @endif
                </div>

                <h5 class="fw-bold mb-1 text-heading">{{ $product->name }}</h5>
                <p class="text-muted mb-2">
                    <span class="badge bg-label-primary">{{ $product->category->name ?? 'Tanpa Kategori' }}</span>
                </p>

                <div class="mb-4">
                    @if($product->is_active)
                        <span class="badge bg-label-success px-3 py-1">Aktif Dijual</span>
                    @else
                        <span class="badge bg-label-secondary px-3 py-1">Nonaktif</span>
                    @endif
                </div>
                
                <div class="d-flex justify-content-around align-items-center mb-4 py-3 bg-light rounded">
                    <div>
                        <h4 class="mb-0 fw-bold {{ $product->stock <= 0 ? 'text-danger' : ($product->stock <= $product->min_stock ? 'text-warning' : 'text-success') }}">
                            {{ $product->stock }}
                        </h4>
                        <small class="text-muted fw-semibold">Stok Saat Ini</small>
                    </div>
                    <div class="border-end h-50"></div>
                    <div>
                        <h5 class="mb-0 text-primary fw-bold">{{ $product->unit->name ?? '-' }}</h5>
                        <small class="text-muted fw-semibold">Satuan ({{ $product->unit->symbol ?? '-' }})</small>
                    </div>
                </div>

                <div class="info-container text-start">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="fw-semibold text-muted">Barcode:</span>
                            <span class="badge bg-label-dark font-monospace">{{ $product->barcode }}</span>
                        </li>
                        <li class="mb-3 d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="fw-semibold text-muted">Batas Stok Minimum:</span>
                            <span class="fw-bold text-heading">{{ $product->min_stock }} {{ $product->unit->symbol ?? '' }}</span>
                        </li>
                        <li class="mb-3 d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="fw-semibold text-muted">Harga Beli (Modal):</span>
                            <span class="fw-bold text-heading">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</span>
                        </li>
                        <li class="mb-3 d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="fw-semibold text-muted">Harga Jual:</span>
                            <span class="fw-bold text-primary fs-6">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                        </li>
                        <li class="mb-3 d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="fw-semibold text-muted">Margin Keuntungan:</span>
                            @php
                                $margin = $product->selling_price - $product->cost_price;
                                $marginPercent = $product->cost_price > 0 ? ($margin / $product->cost_price) * 100 : 100;
                            @endphp
                            <span class="fw-bold {{ $margin >= 0 ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($margin, 0, ',', '.') }} ({{ number_format($marginPercent, 1) }}%)
                            </span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold text-muted">Terdaftar Pada:</span>
                            <span class="small text-muted">{{ $product->created_at ? $product->created_at->format('d M Y, H:i') : '-' }}</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <!-- Statistik & Deskripsi -->
    <div class="col-md-7 col-lg-8 mb-4">
        
        {{-- Status Warning Card if Stock Low --}}
        @if($product->stock <= 0)
        <div class="alert alert-danger d-flex align-items-center mb-4 border-0 shadow-sm" role="alert">
            <i class="bx bx-error-circle fs-3 me-3"></i>
            <div>
                <h6 class="alert-heading fw-bold mb-1">Peringatan: Stok Habis!</h6>
                <span>Stok produk ini bernilai <strong>0</strong>. Produk tidak dapat ditransaksikan di kasir sebelum dilakukan restock (Pembelian/Penyesuaian Stok).</span>
            </div>
        </div>
        @elseif($product->stock <= $product->min_stock)
        <div class="alert alert-warning d-flex align-items-center mb-4 border-0 shadow-sm" role="alert">
            <i class="bx bx-error fs-3 me-3"></i>
            <div>
                <h6 class="alert-heading fw-bold mb-1">Perhatian: Stok Menipis!</h6>
                <span>Sisa stok (<strong>{{ $product->stock }}</strong>) sudah mencapai atau di bawah batas minimum (<strong>{{ $product->min_stock }}</strong>). Harap segera lakukan pemesanan ke supplier.</span>
            </div>
        </div>
        @endif

        {{-- Detail Deskripsi & Spesifikasi --}}
        <div class="card mb-4 border-0 shadow-sm">
            <h5 class="card-header border-bottom py-3">
                <i class="bx bx-file me-1 text-primary"></i> Deskripsi & Keterangan
            </h5>
            <div class="card-body pt-3">
                @if($product->description)
                    <p class="text-body mb-0" style="white-space: pre-line;">{{ $product->description }}</p>
                @else
                    <p class="text-muted fst-italic mb-0">Tidak ada keterangan atau spesifikasi tambahan untuk produk ini.</p>
                @endif
            </div>
        </div>

        {{-- Log Transaksi / Riwayat Stok Placeholder --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="card-title mb-0">
                    <i class="bx bx-history me-1 text-secondary"></i> Riwayat Transaksi & Mutasi Stok
                </h5>
                <span class="badge bg-label-info">Modul Transaksi & Stok</span>
            </div>
            <div class="card-body text-center py-5">
                <div class="avatar avatar-lg bg-label-secondary mx-auto mb-3">
                    <i class="bx bx-receipt fs-2"></i>
                </div>
                <h6 class="fw-bold mb-1">Pencatatan Mutasi Otomatis</h6>
                <p class="text-muted small mb-3" style="max-width: 480px; margin: 0 auto;">
                    Setiap transaksi Kasir (Penjualan), Penerimaan Pembelian, atau Penyesuaian Stok Opname yang melibatkan produk ini akan otomatis tercatat pada riwayat stok sistem.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar
                    </a>
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-primary">
                        <i class="bx bx-edit me-1"></i> Edit Produk
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
