@extends('layouts.app')

@section('title', 'Detail Faktur Pembelian - ' . $purchase->invoice_number)

@section('page-style')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .layout-menu, .layout-navbar, .footer, .btn, .breadcrumb-area, .no-print {
            display: none !important;
        }
        #purchase-invoice-area, #purchase-invoice-area * {
            visibility: visible;
        }
        #purchase-invoice-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 20px;
            margin: 0;
        }
        .card { border: none !important; box-shadow: none !important; }
    }
</style>
@endsection

@section('content')
<div class="breadcrumb-area d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Pembelian / <a href="{{ route('purchases.index') }}" class="text-muted">Riwayat</a> /</span> {{ $purchase->invoice_number }}
    </h4>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali ke Riwayat
        </a>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="bx bx-printer me-1"></i> Cetak Faktur PO
        </button>
    </div>
</div>

<div class="row" id="purchase-invoice-area">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <!-- Header Invoice -->
            <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-sm bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bx bx-store-alt fs-4"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0 fw-bold">FAKTUR PEMBELIAN BARANG (KULAKAN)</h5>
                        <small class="text-muted font-monospace">{{ $purchase->invoice_number }}</small>
                    </div>
                </div>
                <div>
                    @if($purchase->payment_status === 'paid')
                        <span class="badge bg-success fs-6"><i class="bx bx-check me-1"></i>Lunas (Kas Keluar)</span>
                    @elseif($purchase->payment_status === 'partial')
                        <span class="badge bg-info text-white fs-6"><i class="bx bx-time me-1"></i>Sebagian / DP</span>
                    @else
                        <span class="badge bg-warning text-dark fs-6"><i class="bx bx-time-five me-1"></i>Hutang / Tempo</span>
                    @endif
                </div>
            </div>

            <!-- Body Invoice -->
            <div class="card-body p-4">
                <!-- Meta Grid Supplier & Faktur -->
                <div class="row g-4 mb-4">
                    <!-- Informasi Supplier -->
                    <div class="col-md-6">
                        <div class="bg-lighter p-3 rounded border h-100">
                            <small class="text-muted text-uppercase fw-semibold d-block mb-2">Informasi Supplier</small>
                            @if($purchase->supplier)
                                <h5 class="fw-bold text-dark mb-1">{{ $purchase->supplier->name }}</h5>
                                <div class="text-muted small mb-1">
                                    <i class="bx bx-user me-1"></i> Kontak: {{ $purchase->supplier->contact_person ?: '-' }}
                                </div>
                                <div class="text-muted small mb-1">
                                    <i class="bx bx-phone me-1"></i> Telepon: {{ $purchase->supplier->phone ?: '-' }}
                                </div>
                                <div class="text-muted small">
                                    <i class="bx bx-map-pin me-1"></i> Alamat: {{ $purchase->supplier->address ?: '-' }}
                                </div>
                            @else
                                <h6 class="fw-bold text-secondary mb-1">Supplier Umum / Tanpa Nama</h6>
                                <p class="text-muted small mb-0">Pembelian langsung tanpa data master supplier.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Informasi Faktur & Petugas -->
                    <div class="col-md-6">
                        <div class="bg-lighter p-3 rounded border h-100">
                            <small class="text-muted text-uppercase fw-semibold d-block mb-2">Informasi Faktur & Pembuat</small>
                            <div class="d-flex justify-content-between mb-1 small">
                                <span class="text-muted">No. Faktur / PO:</span>
                                <strong class="text-primary font-monospace">{{ $purchase->invoice_number }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1 small">
                                <span class="text-muted">Tanggal Pembelian:</span>
                                <strong class="text-dark">{{ $purchase->purchase_date->format('d F Y') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1 small">
                                <span class="text-muted">Waktu Pencatatan:</span>
                                <span class="text-dark">{{ $purchase->created_at->format('d/m/Y H:i') }} WIB</span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Dicatat Oleh:</span>
                                <strong class="text-dark">{{ $purchase->user?->name ?? 'Staff Gudang' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Rincian Barang yang Dibeli -->
                <h6 class="fw-bold mb-3 d-flex align-items-center gap-1">
                    <i class="bx bx-package text-primary"></i> Rincian Produk yang Dibeli (Barang Masuk)
                </h6>
                <div class="table-responsive text-nowrap border rounded mb-4">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th class="text-end">Harga Beli Satuan</th>
                                <th class="text-center">Kuantitas</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $idx => $item)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td>
                                    <span class="fw-semibold text-heading d-block">{{ $item->product?->name ?? 'Barang' }}</span>
                                    <small class="text-muted font-monospace">{{ $item->product?->barcode ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-label-secondary">{{ $item->product?->category?->name ?? '-' }}</span>
                                </td>
                                <td class="text-end">Rp {{ number_format($item->cost_price, 0, ',', '.') }}</td>
                                <td class="text-center fw-bold">{{ $item->quantity }} {{ $item->product?->unit?->symbol ?? 'Pcs' }}</td>
                                <td class="text-end fw-bold text-primary">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">TOTAL BARANG MASUK:</td>
                                <td class="text-center fw-bold text-primary">{{ $purchase->items->sum('quantity') }} pcs</td>
                                <td class="text-end fw-bolder text-primary fs-6">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Financial Calculation & Notes -->
                <div class="row justify-content-between align-items-start">
                    <div class="col-md-6 mb-3 mb-md-0">
                        @if($purchase->notes)
                        <div class="p-3 bg-light rounded border">
                            <span class="fw-bold text-muted small d-block mb-1">Catatan Faktur:</span>
                            <p class="mb-0 text-dark small">{{ $purchase->notes }}</p>
                        </div>
                        @endif
                    </div>
                    
                    <div class="col-md-5">
                        <div class="bg-lighter p-3 rounded border">
                            <div class="d-flex justify-content-between py-1 border-bottom mb-2 fs-5">
                                <strong class="text-dark">TOTAL TAGIHAN:</strong>
                                <strong class="text-primary fw-bolder">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2 small text-muted">
                                <span>Nominal Dibayarkan:</span>
                                <strong class="text-dark">Rp {{ number_format($purchase->paid_amount, 0, ',', '.') }}</strong>
                            </div>
                            @php
                                $remainingDebt = max(0, $purchase->total_amount - $purchase->paid_amount);
                            @endphp
                            @if($remainingDebt > 0)
                            <div class="d-flex justify-content-between text-danger fw-bold border-top pt-2 fs-6">
                                <span>Sisa Hutang Faktur:</span>
                                <span>Rp {{ number_format($remainingDebt, 0, ',', '.') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
