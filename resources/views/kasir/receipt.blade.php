<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $sale->invoice_number }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <!-- Sneat Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <!-- Sneat Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />

    <style>
        body {
            background-color: #f5f5f9;
            font-family: 'Courier New', Courier, monospace;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* Navigasi Aksi Cetak di Layar */
        .receipt-action-bar {
            background: #fff;
            border-bottom: 1px solid #d9dee3;
            padding: 12px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 6px rgba(67, 89, 113, 0.08);
            font-family: var(--bs-body-font-family);
        }

        /* Container Kertas Struk */
        .receipt-paper-wrapper {
            display: flex;
            justify-content: center;
            padding: 24px 12px;
        }

        .receipt-container {
            background: #fff;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.1);
            padding: 16px 14px;
            border-radius: 4px;
            box-sizing: border-box;
            line-height: 1.35;
        }

        /* Ukuran 58mm & 80mm */
        .receipt-58mm {
            width: 58mm;
            max-width: 240px;
            font-size: 11px;
        }

        .receipt-80mm {
            width: 80mm;
            max-width: 320px;
            font-size: 12px;
        }

        .dashed-line {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .double-line {
            border-top: 1px double #000;
            border-bottom: 1px double #000;
            height: 3px;
            margin: 8px 0;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .fw-bold { font-weight: bold; }

        .d-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        /* Pengaturan Cetak Thermal Printer Otomatis */
        @media print {
            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .receipt-action-bar {
                display: none !important;
            }

            .receipt-paper-wrapper {
                padding: 0 !important;
                margin: 0 !important;
            }

            .receipt-container {
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 2mm 3mm !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            @page {
                margin: 0;
                size: auto;
            }
        }
    </style>
</head>
<body>

    <!-- Bar Aksi Kontrol (Disembunyikan saat mencetak) -->
    <div class="receipt-action-bar no-print">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-heading fs-6"><i class="bx bx-receipt text-primary me-1"></i> Cetak Struk Belanja</span>
                <span class="badge bg-label-primary font-monospace">{{ $sale->invoice_number }}</span>
            </div>

            <!-- Kontrol Ukuran & Tombol Cetak -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Pilihan Ukuran Kertas -->
                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('kasir.receipt', ['sale' => $sale->id, 'paper' => '58']) }}" 
                       class="btn {{ $paper === '58' ? 'btn-primary' : 'btn-outline-primary' }}">
                        58 mm
                    </a>
                    <a href="{{ route('kasir.receipt', ['sale' => $sale->id, 'paper' => '80']) }}" 
                       class="btn {{ $paper === '80' ? 'btn-primary' : 'btn-outline-primary' }}">
                        80 mm
                    </a>
                </div>

                <!-- Tombol Cetak Thermal -->
                <button type="button" class="btn btn-sm btn-success d-flex align-items-center gap-1 shadow-sm" onclick="window.print()">
                    <i class="bx bx-printer fs-5"></i>
                    <span class="fw-bold">Cetak Struk (Ctrl + P)</span>
                </button>

                <!-- Tombol Download PDF -->
                <a href="{{ route('kasir.receipt.pdf', ['sale' => $sale->id, 'paper' => $paper]) }}" 
                   class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
                    <i class="bx bxs-file-pdf fs-5"></i>
                    <span>Download PDF</span>
                </a>

                <!-- Tombol Tutup / POS -->
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.close();">
                    <i class="bx bx-x me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Area Kertas Thermal Struk -->
    <div class="receipt-paper-wrapper">
        <div class="receipt-container {{ $paper === '80' ? 'receipt-80mm' : 'receipt-58mm' }}">
            
            <!-- Header Toko -->
            <div class="text-center">
                <div class="fw-bold fs-6 text-uppercase" style="letter-spacing: 0.5px;">{{ $setting->store_name ?? 'SI TOKO RETAIL' }}</div>
                @if($setting->address)
                    <div style="font-size: 0.85em;">{{ $setting->address }}</div>
                @endif
                @if($setting->phone)
                    <div style="font-size: 0.85em;">Telp: {{ $setting->phone }}</div>
                @endif
                @if($setting->header_receipt)
                    <div style="font-size: 0.8em; margin-top: 2px;">{{ $setting->header_receipt }}</div>
                @endif
            </div>

            <div class="double-line"></div>

            <!-- Meta Transaksi -->
            <div style="font-size: 0.9em;">
                <div class="d-flex">
                    <span>No. Faktur</span>
                    <span class="fw-bold font-monospace">{{ $sale->invoice_number }}</span>
                </div>
                <div class="d-flex">
                    <span>Tanggal</span>
                    <span>{{ $sale->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="d-flex">
                    <span>Kasir</span>
                    <span>{{ $sale->user?->name ?? 'Kasir' }}</span>
                </div>
                <div class="d-flex">
                    <span>Pelanggan</span>
                    <span>{{ $sale->customer?->name ?? 'Pelanggan Umum' }}</span>
                </div>
            </div>

            <div class="dashed-line"></div>

            <!-- Rincian Barang Belanjaan -->
            <div>
                @foreach($sale->items as $item)
                    @php
                        $itemGross = $item->selling_price * $item->quantity;
                        $hasItemDisc = ($itemGross > $item->subtotal);
                        $itemDiscAmount = $itemGross - $item->subtotal;
                    @endphp
                    <div style="margin-bottom: 5px;">
                        <div class="fw-bold">{{ $item->product?->name ?? 'Barang' }}</div>
                        <div class="d-flex" style="font-size: 0.92em;">
                            <span>{{ $item->quantity }} x {{ number_format($item->selling_price, 0, ',', '.') }}</span>
                            <span class="fw-bold">{{ number_format($itemGross, 0, ',', '.') }}</span>
                        </div>
                        @if($hasItemDisc)
                            <div class="d-flex text-muted" style="font-size: 0.82em;">
                                <span>(Diskon Item)</span>
                                <span>-{{ number_format($itemDiscAmount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="dashed-line"></div>

            <!-- Ringkasan Pembayaran & Total -->
            <div style="font-size: 0.95em;">
                <div class="d-flex">
                    <span>Subtotal</span>
                    <span>{{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                </div>

                @if($sale->discount > 0)
                    <div class="d-flex">
                        <span>Diskon</span>
                        <span>-{{ number_format($sale->discount, 0, ',', '.') }}</span>
                    </div>
                @endif

                <div class="d-flex fw-bold fs-6" style="margin: 4px 0;">
                    <span>TOTAL</span>
                    <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                </div>

                <div class="dashed-line"></div>

                @php
                    $methodLabel = match($sale->payment_method) {
                        'cash'     => 'TUNAI',
                        'qris'     => 'QRIS',
                        'transfer' => 'TRANSFER BANK',
                        'debt'     => 'HUTANG (BON)',
                        default    => strtoupper($sale->payment_method),
                    };
                @endphp

                <div class="d-flex">
                    <span>Metode Bayar</span>
                    <span class="fw-bold">{{ $methodLabel }}</span>
                </div>

                @if($sale->payment_method === 'cash')
                    <div class="d-flex">
                        <span>Tunai Diterima</span>
                        <span>{{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex fw-bold">
                        <span>Kembalian</span>
                        <span>{{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                    </div>
                @elseif($sale->payment_method === 'debt')
                    @php
                        $debt = $sale->debt;
                        $dp = (float) $sale->paid_amount;
                        $remaining = (float) ($debt?->remaining_debt ?? ($sale->total_amount - $dp));
                        $accumulated = (float) ($sale->customer?->total_debt ?? $remaining);
                    @endphp
                    @if($dp > 0)
                        <div class="d-flex">
                            <span>Uang Muka (DP)</span>
                            <span>{{ number_format($dp, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="d-flex fw-bold">
                        <span>Sisa Hutang Faktur</span>
                        <span>{{ number_format($remaining, 0, ',', '.') }}</span>
                    </div>
                    @if($debt && $debt->due_date)
                        <div class="d-flex" style="font-size: 0.88em;">
                            <span>Jatuh Tempo</span>
                            <span>{{ $debt->due_date->format('d/m/Y') }}</span>
                        </div>
                    @endif
                    <div class="d-flex text-muted" style="font-size: 0.85em;">
                        <span>Total Saldo Hutang</span>
                        <span>Rp {{ number_format($accumulated, 0, ',', '.') }}</span>
                    </div>
                @else
                    <div class="d-flex">
                        <span>Bayar (Lunas)</span>
                        <span>{{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>

            @if($sale->notes)
                <div class="dashed-line"></div>
                <div style="font-size: 0.85em;">
                    <span class="fw-bold">Catatan:</span> {{ $sale->notes }}
                </div>
            @endif

            <div class="double-line"></div>

            <!-- Footer Struk -->
            <div class="text-center" style="font-size: 0.85em; margin-top: 6px;">
                @if($setting->footer_receipt)
                    <div>{{ $setting->footer_receipt }}</div>
                @else
                    <div>Terima kasih atas kunjungan Anda!</div>
                @endif
                <div style="margin-top: 2px;">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</div>
                <div style="font-size: 0.8em; color: #555; margin-top: 4px;">*** {{ config('app.name', 'SI Toko') }} ***</div>
            </div>

        </div>
    </div>

    @if(!empty($autoprint))
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 300);
        });
    </script>
    @endif

</body>
</html>
