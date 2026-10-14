@extends('layouts.app')

@section('title', 'Detail Transaksi - ' . $sale->invoice_number)

@section('page-style')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .layout-menu, .layout-navbar, .footer, .btn, .breadcrumb-area, .no-print {
            display: none !important;
        }
        #receipt-print-area, #receipt-print-area * {
            visibility: visible;
        }
        #receipt-print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            max-width: 300px;
            padding: 0;
            margin: 0;
        }
        .card { border: none !important; box-shadow: none !important; }
    }
    
    .border-dashed {
        border-bottom: 1px dashed #d9dee3;
    }
</style>
@endsection

@section('content')
<div class="breadcrumb-area d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Transaksi / <a href="{{ route('transactions.index') }}" class="text-muted">Riwayat</a> /</span> {{ $sale->invoice_number }}
    </h4>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bx bx-printer me-1"></i> Cetak Struk
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                    <button class="dropdown-item" type="button" onclick="printReceiptPopup({{ $sale->id }}, '58')">
                        <i class="bx bx-printer me-2 text-primary"></i> Cetak Thermal (58mm)
                    </button>
                </li>
                <li>
                    <button class="dropdown-item" type="button" onclick="printReceiptPopup({{ $sale->id }}, '80')">
                        <i class="bx bx-printer me-2 text-primary"></i> Cetak Thermal (80mm)
                    </button>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="{{ route('kasir.receipt.pdf', $sale->id) }}" target="_blank">
                        <i class="bx bxs-file-pdf me-2 text-danger"></i> Download Struk PDF
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="row">
    <!-- Transaction Details (Web View) -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                    <i class="bx bx-receipt text-primary fs-4"></i> Rincian Transaksi
                </h5>
                @if($sale->payment_status === 'paid')
                    <span class="badge bg-success"><i class="bx bx-check me-1"></i>Lunas</span>
                @else
                    <span class="badge bg-warning text-dark"><i class="bx bx-time me-1"></i>Hutang (Bon)</span>
                @endif
            </div>
            <div class="card-body py-4">
                <!-- Meta Info Grid -->
                <div class="row mb-4">
                    <div class="col-sm-6 mb-3 mb-sm-0">
                        <small class="text-muted text-uppercase fw-semibold">No. Faktur</small>
                        <h5 class="mb-0 text-primary fw-bolder font-monospace">{{ $sale->invoice_number }}</h5>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted text-uppercase fw-semibold">Tanggal & Waktu</small>
                        <h6 class="mb-0 text-dark">{{ $sale->created_at->format('d M Y, H:i') }} WIB</h6>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-6 mb-3 mb-sm-0">
                        <small class="text-muted text-uppercase fw-semibold">Kasir</small>
                        <div class="d-flex align-items-center mt-1">
                            <div class="avatar avatar-xs me-2 bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bx bx-user fs-6"></i>
                            </div>
                            <h6 class="mb-0 text-dark">{{ $sale->user?->name ?? 'Kasir' }}</h6>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted text-uppercase fw-semibold">Pelanggan</small>
                        <div class="mt-1">
                            @if($sale->customer)
                                <h6 class="mb-0 text-dark fw-bold">{{ $sale->customer->name }}</h6>
                                <small class="text-muted">{{ $sale->customer->phone ?: 'No Telp: -' }}</small>
                            @else
                                <span class="badge bg-label-secondary font-monospace">Pelanggan Umum (Non-Member)</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-6">
                        <small class="text-muted text-uppercase fw-semibold">Metode Pembayaran</small>
                        <div class="mt-1">
                            @if($sale->payment_method === 'cash')
                                <span class="badge bg-label-success fs-6"><i class="bx bx-money me-1"></i> Tunai</span>
                            @elseif($sale->payment_method === 'qris')
                                <span class="badge bg-label-info fs-6"><i class="bx bx-qr-scan me-1"></i> QRIS</span>
                            @elseif($sale->payment_method === 'transfer')
                                <span class="badge bg-label-primary fs-6"><i class="bx bx-credit-card me-1"></i> Transfer Bank</span>
                            @elseif($sale->payment_method === 'debt')
                                <span class="badge bg-label-warning fs-6"><i class="bx bx-calendar-event me-1"></i> Hutang (Bon)</span>
                            @else
                                <span class="badge bg-label-secondary fs-6">{{ strtoupper($sale->payment_method) }}</span>
                            @endif
                        </div>
                    </div>

                    @if($sale->notes)
                    <div class="col-sm-6">
                        <small class="text-muted text-uppercase fw-semibold">Catatan</small>
                        <p class="mb-0 text-dark small bg-light p-2 rounded">{{ $sale->notes }}</p>
                    </div>
                    @endif
                </div>

                <hr class="mx-n4">
                <h6 class="mb-3 fw-bold d-flex align-items-center gap-1">
                    <i class="bx bx-package text-primary"></i> Produk yang Dibeli
                </h6>
                
                <div class="table-responsive text-nowrap border rounded mb-4">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Produk</th>
                                <th class="text-end">Harga Jual</th>
                                <th class="text-center">Kuantitas</th>
                                <th class="text-end">Diskon Item</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $idx => $item)
                                @php
                                    $itemGross = $item->selling_price * $item->quantity;
                                    $itemDisc = max(0, $itemGross - $item->subtotal);
                                @endphp
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>
                                        <span class="fw-semibold text-heading d-block">{{ $item->product?->name ?? 'Barang' }}</span>
                                        <small class="text-muted font-monospace">{{ $item->product?->barcode ?? '-' }}</small>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($item->selling_price, 0, ',', '.') }}</td>
                                    <td class="text-center fw-bold">{{ $item->quantity }} {{ $item->product?->unit?->symbol ?? 'Pcs' }}</td>
                                    <td class="text-end text-danger">
                                        {{ $itemDisc > 0 ? '- Rp ' . number_format($itemDisc, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="text-end fw-bold text-primary">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Financial Calculation Card -->
                <div class="row justify-content-end">
                    <div class="col-md-6">
                        <div class="bg-lighter rounded p-3 border">
                            <div class="d-flex justify-content-between mb-2 small text-muted">
                                <span>Subtotal Barang:</span>
                                <span class="fw-semibold text-dark">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                            </div>

                            @if($sale->discount > 0)
                            <div class="d-flex justify-content-between mb-2 small text-danger">
                                <span>Total Diskon:</span>
                                <span class="fw-semibold">- Rp {{ number_format($sale->discount, 0, ',', '.') }}</span>
                            </div>
                            @endif

                            <div class="d-flex justify-content-between fw-bold border-top border-bottom py-2 my-2 text-primary fs-5">
                                <span>TOTAL TAGIHAN:</span>
                                <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                            </div>

                            @if($sale->payment_method === 'cash')
                                <div class="d-flex justify-content-between mb-2 small text-muted">
                                    <span>Tunai Diterima:</span>
                                    <span class="fw-semibold text-dark">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold text-success fs-6">
                                    <span>Kembalian:</span>
                                    <span>Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                                </div>
                            @elseif($sale->payment_method === 'debt')
                                @php
                                    $debt = $sale->debt;
                                    $dp = (float) $sale->paid_amount;
                                    $remaining = (float) ($debt?->remaining_debt ?? ($sale->total_amount - $dp));
                                @endphp
                                <div class="d-flex justify-content-between mb-2 small text-muted">
                                    <span>Uang Muka (DP):</span>
                                    <span class="fw-semibold text-dark">Rp {{ number_format($dp, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold text-danger fs-6 border-top pt-2">
                                    <span>Sisa Hutang Faktur:</span>
                                    <span>Rp {{ number_format($remaining, 0, ',', '.') }}</span>
                                </div>
                                @if($debt && $debt->due_date)
                                <div class="d-flex justify-content-between small text-muted mt-1">
                                    <span>Jatuh Tempo:</span>
                                    <span class="fw-semibold">{{ $debt->due_date->format('d M Y') }}</span>
                                </div>
                                @endif
                            @else
                                <div class="d-flex justify-content-between fw-semibold small text-muted">
                                    <span>Bayar (Lunas):</span>
                                    <span class="text-dark">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Right: Thermal Receipt Preview (58mm / 80mm) -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100 bg-lighter border-0 shadow-sm">
            <div class="card-header bg-transparent text-center border-bottom pb-2 pt-3">
                <h6 class="mb-0 text-muted fw-bold d-flex align-items-center justify-content-center gap-1">
                    <i class="bx bx-receipt text-primary"></i> Preview Struk Thermal
                </h6>
            </div>
            <div class="card-body p-3 d-flex flex-column align-items-center justify-content-start" style="background-color: #f0f2f5 !important;">
                
                <!-- Action Controls above Preview -->
                <div class="d-flex gap-1 mb-3 flex-wrap justify-content-center">
                    <button type="button" class="btn btn-xs btn-primary" onclick="printReceiptPopup({{ $sale->id }}, '58')">
                        <i class="bx bx-printer me-1"></i> Cetak 58mm
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-primary" onclick="printReceiptPopup({{ $sale->id }}, '80')">
                        <i class="bx bx-printer me-1"></i> Cetak 80mm
                    </button>
                    <a href="{{ route('kasir.receipt.pdf', $sale->id) }}" class="btn btn-xs btn-outline-danger" target="_blank">
                        <i class="bx bxs-file-pdf me-1"></i> PDF
                    </a>
                </div>

                <!-- Actual Thermal Receipt Container -->
                <div id="receipt-print-area" class="bg-white p-3 shadow-sm rounded" style="width: 270px; min-height: 380px; font-family: 'Courier New', Courier, monospace; font-size: 11px; color: #000; line-height: 1.35;">
                    
                    <!-- Header -->
                    <div class="text-center mb-2">
                        <h6 class="fw-bold mb-1" style="font-size: 14px; color: #000;">{{ config('app.name', 'SI TOKO RETAIL') }}</h6>
                        <div style="font-size: 10px; line-height: 1.2;">
                            Struk Penjualan Kasir<br>
                            Telp Toko POS
                        </div>
                    </div>

                    <!-- Meta -->
                    <div class="border-dashed pb-2 mb-2" style="font-size: 10px;">
                        <div class="d-flex justify-content-between"><span>No:</span> <span class="fw-bold">{{ $sale->invoice_number }}</span></div>
                        <div class="d-flex justify-content-between"><span>Tgl:</span> <span>{{ $sale->created_at->format('d/m/Y H:i') }}</span></div>
                        <div class="d-flex justify-content-between"><span>Ksr:</span> <span>{{ $sale->user?->name ?? 'Kasir' }}</span></div>
                        <div class="d-flex justify-content-between"><span>Plg:</span> <span>{{ $sale->customer?->name ?? 'Pelanggan Umum' }}</span></div>
                    </div>

                    <!-- Items -->
                    <div class="mb-2" style="font-size: 10px;">
                        @foreach($sale->items as $item)
                        <div class="mb-1">
                            <div class="fw-bold">{{ $item->product?->name ?? 'Barang' }}</div>
                            <div class="d-flex justify-content-between">
                                <span>{{ $item->quantity }} x {{ number_format($item->selling_price, 0, ',', '.') }}</span>
                                <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Totals -->
                    <div class="border-dashed pb-2 mb-2 pt-1 border-top" style="border-top: 1px dashed #000; font-size: 10px;">
                        <div class="d-flex justify-content-between"><span>Subtotal</span> <span>{{ number_format($sale->subtotal, 0, ',', '.') }}</span></div>
                        @if($sale->discount > 0)
                        <div class="d-flex justify-content-between"><span>Diskon</span> <span>-{{ number_format($sale->discount, 0, ',', '.') }}</span></div>
                        @endif
                        <div class="d-flex justify-content-between fw-bold" style="font-size: 12px; margin-top: 2px;">
                            <span>TOTAL</span> <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span>Bayar ({{ strtoupper($sale->payment_method) }})</span> 
                            <span>{{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
                        </div>
                        @if($sale->payment_method === 'cash')
                        <div class="d-flex justify-content-between fw-bold"><span>Kembali</span> <span>{{ number_format($sale->change_amount, 0, ',', '.') }}</span></div>
                        @elseif($sale->payment_method === 'debt')
                        <div class="d-flex justify-content-between fw-bold text-danger">
                            <span>Sisa Hutang</span> 
                            <span>{{ number_format(max(0, $sale->total_amount - $sale->paid_amount), 0, ',', '.') }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Footer -->
                    <div class="text-center mt-3" style="font-size: 10px;">
                        <p class="mb-1">Terima kasih atas kunjungan Anda!</p>
                        <p class="mb-0">Barang yang sudah dibeli<br>tidak dapat ditukar/dikembalikan.</p>
                    </div>

                </div>
                <!-- / Thermal Receipt Container -->

            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    function printReceiptPopup(saleId, paper = '58') {
        const url = `/kasir/receipt/${saleId}?paper=${paper}&autoprint=1`;
        const width = paper === '80' ? 440 : 380;
        const height = 650;
        const left = (window.screen.width / 2) - (width / 2);
        const top = (window.screen.height / 2) - (height / 2);

        window.open(url, `Struk_${saleId}`, `width=${width},height=${height},top=${top},left=${left},scrollbars=yes,status=no`);
    }
</script>
@endsection
