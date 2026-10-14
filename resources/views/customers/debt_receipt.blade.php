<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Pembayaran - KW-{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</title>
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
            max-width: 310px;
            font-size: 13px;
        }

        /* Garis Pemisah Thermal */
        .dashed-line {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .double-line {
            border-top: 1px double #000;
            border-bottom: 1px double #000;
            height: 3px;
            margin: 6px 0;
        }

        .table-receipt {
            width: 100%;
            border-collapse: collapse;
        }

        .table-receipt td {
            padding: 2px 0;
            vertical-align: top;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: none;
                margin: 0;
                padding: 0;
            }

            .receipt-action-bar {
                display: none !important;
            }

            .receipt-paper-wrapper {
                padding: 0;
                margin: 0;
            }

            .receipt-container {
                box-shadow: none;
                border-radius: 0;
                padding: 4px;
                width: 100% !important;
                max-width: 100% !important;
            }

            @page {
                margin: 2mm 3mm;
            }
        }
    </style>
</head>
<body>

    <!-- Bar Aksi (Hanya tampil di layar monitor) -->
    <div class="receipt-action-bar d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('customers.debt') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar Hutang
            </a>
            <span class="fw-bold font-monospace text-dark ms-2">
                KW-{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}
            </span>
            <span class="badge {{ $debt->remaining_debt <= 0 ? 'bg-success' : 'bg-warning' }} ms-1">
                {{ $debt->remaining_debt <= 0 ? 'LUNAS' : 'CICILAN' }}
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Pilihan Ukuran Kertas Thermal -->
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-primary active" id="btn-paper-58" onclick="setPaperSize('58')">
                    58mm (Thermal Standar)
                </button>
                <button type="button" class="btn btn-outline-primary" id="btn-paper-80" onclick="setPaperSize('80')">
                    80mm (Lebar)
                </button>
            </div>

            <!-- Tombol Cetak Cepat -->
            <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                <i class="bx bx-printer me-1"></i> Cetak Struk
            </button>

            <!-- Tombol Download PDF -->
            <a href="{{ route('customers.debt.receipt.pdf', $payment->id) }}?paper=58" id="btn-download-pdf" class="btn btn-sm btn-outline-danger">
                <i class="bx bxs-file-pdf me-1"></i> Download PDF
            </a>
        </div>
    </div>

    <!-- Area Kertas Struk Thermal -->
    <div class="receipt-paper-wrapper">
        <div id="receipt-paper" class="receipt-container receipt-58mm">

            <!-- 1. Header Informasi Toko -->
            <div class="text-center">
                <div class="fw-bold text-uppercase" style="font-size: 1.15em;">
                    {{ $setting->store_name ?? 'SI TOKO RETAIL' }}
                </div>
                @if($setting->address)
                    <div style="font-size: 0.9em;">{{ $setting->address }}</div>
                @endif
                @if($setting->phone)
                    <div style="font-size: 0.9em;">Telp: {{ $setting->phone }}</div>
                @endif
                @if($setting->header_receipt)
                    <div style="font-size: 0.85em; margin-top: 2px;">{{ $setting->header_receipt }}</div>
                @endif
            </div>

            <div class="double-line"></div>

            <!-- 2. Judul Dokumen & Info Kuitansi -->
            <div class="text-center fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                BUKTI PEMBAYARAN HUTANG
            </div>
            <div class="text-center" style="font-size: 0.85em;">
                (KUITANSI RESMI)
            </div>

            <div class="dashed-line"></div>

            <table class="table-receipt">
                <tr>
                    <td style="width: 42%;">No. Kuitansi</td>
                    <td style="width: 4%;">:</td>
                    <td class="fw-bold text-end">KW-{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</td>
                </tr>
                <tr>
                    <td>Faktur Asal</td>
                    <td>:</td>
                    <td class="text-end font-monospace">
                        {{ $debt->sale ? $debt->sale->invoice_number : ('TRX-' . $debt->id) }}
                    </td>
                </tr>
                <tr>
                    <td>Tanggal Bayar</td>
                    <td>:</td>
                    <td class="text-end">
                        {{ $payment->payment_date->format('d/m/Y') }} {{ $payment->created_at ? $payment->created_at->format('H:i') : '' }}
                    </td>
                </tr>
                <tr>
                    <td>Pelanggan</td>
                    <td>:</td>
                    <td class="fw-bold text-end">{{ $customer->name ?? 'Pelanggan Umum' }}</td>
                </tr>
                @if($customer && $customer->phone)
                <tr>
                    <td>No. Telp</td>
                    <td>:</td>
                    <td class="text-end">{{ $customer->phone }}</td>
                </tr>
                @endif
                <tr>
                    <td>Kasir/Penerima</td>
                    <td>:</td>
                    <td class="text-end">{{ $payment->user?->name ?? 'Kasir Toko' }}</td>
                </tr>
                <tr>
                    <td>Metode Bayar</td>
                    <td>:</td>
                    <td class="text-end fw-semibold">
                        {{ match($payment->payment_method) {
                            'cash'     => 'Tunai (Cash)',
                            'transfer' => 'Transfer Bank',
                            'qris'     => 'QRIS',
                            default    => strtoupper($payment->payment_method)
                        } }}
                    </td>
                </tr>
            </table>

            <div class="dashed-line"></div>

            <!-- 3. Rincian Finansial Hutang & Cicilan -->
            <table class="table-receipt">
                <tr>
                    <td>Total Tagihan Awal</td>
                    <td class="text-end">Rp {{ number_format($debt->total_debt, 0, ',', '.') }}</td>
                </tr>
                @php
                    $prevPaid = max(0, (float) ($debt->total_debt - $debt->remaining_debt - $payment->amount));
                @endphp
                @if($prevPaid > 0)
                <tr>
                    <td>Dibayar Sebelumnya</td>
                    <td class="text-end">Rp {{ number_format($prevPaid, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="fw-bold" style="font-size: 1.05em;">
                    <td style="padding-top: 4px;">DIBAYAR SEKARANG</td>
                    <td class="text-end" style="padding-top: 4px;">
                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                    </td>
                </tr>
                <tr class="fw-bold">
                    <td style="padding-top: 2px;">SISA HUTANG</td>
                    <td class="text-end {{ $debt->remaining_debt <= 0 ? 'text-success' : '' }}" style="padding-top: 2px;">
                        Rp {{ number_format($debt->remaining_debt, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td>Status Tagihan</td>
                    <td class="text-end fw-bold">
                        {{ $debt->remaining_debt <= 0 ? '*** LUNAS ***' : 'BELUM LUNAS' }}
                    </td>
                </tr>
                @if($debt->remaining_debt > 0 && $debt->due_date)
                <tr>
                    <td>Tenggat Jatuh Tempo</td>
                    <td class="text-end">{{ $debt->due_date->format('d/m/Y') }}</td>
                </tr>
                @endif
            </table>

            @if($payment->note)
            <div class="dashed-line"></div>
            <div style="font-size: 0.85em;">
                <span class="fw-bold">Catatan:</span> {{ $payment->note }}
            </div>
            @endif

            <div class="double-line"></div>

            <!-- 4. Footer Struk -->
            <div class="text-center" style="font-size: 0.85em; margin-top: 4px;">
                @if($setting->footer_receipt)
                    <div>{{ $setting->footer_receipt }}</div>
                @else
                    <div>Terima kasih atas pembayaran Anda.</div>
                    <div>Simpan struk ini sebagai bukti pembayaran yang sah.</div>
                @endif
                <div style="margin-top: 6px; font-size: 0.8em; color: #555;">
                    Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
                </div>
            </div>

        </div>
    </div>

    <!-- Script Pengendali Ukuran dan Auto Print -->
    <script>
        function setPaperSize(size) {
            const paper = document.getElementById('receipt-paper');
            const btn58 = document.getElementById('btn-paper-58');
            const btn80 = document.getElementById('btn-paper-80');
            const btnPdf = document.getElementById('btn-download-pdf');

            if (size === '80') {
                paper.classList.remove('receipt-58mm');
                paper.classList.add('receipt-80mm');
                btn80.classList.add('active');
                btn58.classList.remove('active');
                btnPdf.href = "{{ route('customers.debt.receipt.pdf', $payment->id) }}?paper=80";
            } else {
                paper.classList.remove('receipt-80mm');
                paper.classList.add('receipt-58mm');
                btn58.classList.add('active');
                btn80.classList.remove('active');
                btnPdf.href = "{{ route('customers.debt.receipt.pdf', $payment->id) }}?paper=58";
            }
        }

        // Auto print jika ada parameter ?autoprint=1 di URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('autoprint') === '1') {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    window.print();
                }, 300);
            });
        }
    </script>
</body>
</html>
