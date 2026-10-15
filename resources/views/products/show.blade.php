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

        {{-- Riwayat Transaksi & Mutasi Stok Riil --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-history me-1 text-primary"></i> Riwayat Transaksi & Mutasi Stok
                    </h5>
                    <a href="{{ route('stock.adjustment', ['product_id' => $product->id]) }}" class="btn btn-xs btn-outline-primary">
                        <i class="bx bx-adjust me-1"></i> Stock Opname
                    </a>
                </div>

                {{-- Metrik Ringkasan Transaksi Produk --}}
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="p-2 rounded bg-light border">
                            <span class="small text-muted d-block">Total Terjual</span>
                            <span class="fw-bold text-primary">{{ number_format($productStats['total_sold_qty']) }} {{ $product->unit->symbol ?? 'Pcs' }}</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-light border">
                            <span class="small text-muted d-block">Omset Penjualan</span>
                            <span class="fw-bold text-success">Rp {{ number_format($productStats['total_sold_amount'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-light border">
                            <span class="small text-muted d-block">Total Kulakan</span>
                            <span class="fw-bold text-info">{{ number_format($productStats['total_bought_qty']) }} {{ $product->unit->symbol ?? 'Pcs' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Tab Navigasi --}}
                <ul class="nav nav-tabs card-header-tabs mt-3" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#tab-sales" aria-controls="tab-sales" aria-selected="true">
                            <i class="bx bx-cart me-1"></i> Penjualan Kasir ({{ $recentSales->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-purchases" aria-controls="tab-purchases" aria-selected="false">
                            <i class="bx bx-store-alt me-1"></i> Pembelian ({{ $recentPurchases->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-adjustments" aria-controls="tab-adjustments" aria-selected="false">
                            <i class="bx bx-adjust me-1"></i> Stock Opname ({{ $recentAdjustments->count() }})
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0">
                <div class="tab-content p-0">
                    
                    {{-- Tab 1: Penjualan Kasir --}}
                    <div class="tab-pane fade show active" id="tab-sales" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Faktur</th>
                                        <th>Tanggal & Waktu</th>
                                        <th>Kasir</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Harga Jual</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentSales as $item)
                                    <tr>
                                        <td>
                                            @if($item->sale)
                                                <a href="{{ route('transactions.show', $item->sale->id) }}" class="fw-bold text-primary font-monospace">
                                                    {{ $item->sale->invoice_number }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="small">{{ $item->created_at ? $item->created_at->translatedFormat('d M Y, H:i') : '-' }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $item->sale?->user?->name ?? 'Kasir' }}</small>
                                        </td>
                                        <td class="text-center fw-bold text-danger">
                                            -{{ $item->quantity }}
                                        </td>
                                        <td class="text-end small">
                                            Rp {{ number_format($item->selling_price, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end fw-semibold">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bx bx-receipt fs-4 d-block mb-1"></i>
                                            Belum ada transaksi penjualan untuk produk ini.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Tab 2: Pembelian / Kulakan --}}
                    <div class="tab-pane fade" id="tab-purchases" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Faktur PO</th>
                                        <th>Tanggal</th>
                                        <th>Supplier</th>
                                        <th class="text-center">Qty Masuk</th>
                                        <th class="text-end">Harga Beli</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentPurchases as $pItem)
                                    <tr>
                                        <td>
                                            @if($pItem->purchase)
                                                <a href="{{ route('purchases.show', $pItem->purchase->id) }}" class="fw-bold text-primary font-monospace">
                                                    {{ $pItem->purchase->invoice_number }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="small">{{ $pItem->purchase?->purchase_date ? \Carbon\Carbon::parse($pItem->purchase->purchase_date)->translatedFormat('d M Y') : '-' }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $pItem->purchase?->supplier?->name ?? 'Supplier Umum' }}</small>
                                        </td>
                                        <td class="text-center fw-bold text-success">
                                            +{{ $pItem->quantity }}
                                        </td>
                                        <td class="text-end small">
                                            Rp {{ number_format($pItem->cost_price, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end fw-semibold">
                                            Rp {{ number_format($pItem->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bx bx-store fs-4 d-block mb-1"></i>
                                            Belum ada riwayat pembelian (kulakan) untuk produk ini.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Tab 3: Stock Opname --}}
                    <div class="tab-pane fade" id="tab-adjustments" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kode Opname</th>
                                        <th>Tanggal</th>
                                        <th class="text-center">Stok Awal</th>
                                        <th class="text-center">Stok Fisik</th>
                                        <th class="text-center">Selisih</th>
                                        <th>Petugas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentAdjustments as $adjItem)
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-primary font-monospace">
                                                {{ $adjItem->adjustment?->code ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="small">{{ $adjItem->created_at ? $adjItem->created_at->translatedFormat('d M Y, H:i') : '-' }}</span>
                                        </td>
                                        <td class="text-center text-muted">
                                            {{ $adjItem->previous_stock }}
                                        </td>
                                        <td class="text-center fw-semibold text-dark">
                                            {{ $adjItem->actual_stock }}
                                        </td>
                                        <td class="text-center fw-bold {{ $adjItem->difference >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $adjItem->difference >= 0 ? '+' : '' }}{{ $adjItem->difference }}
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $adjItem->adjustment?->user?->name ?? 'Gudang' }}</small>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bx bx-check-circle fs-4 text-success d-block mb-1"></i>
                                            Belum pernah dilakukan penyesuaian stok manual untuk produk ini.
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

    </div>
</div>
@endsection
