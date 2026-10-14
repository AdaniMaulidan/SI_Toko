<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kuitansi - KW{{ $payment->id }}</title>
    <style>
        @page {
            margin: 2mm 3mm;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: {{ $paper === '80' ? '9pt' : '8pt' }};
            color: #000;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }
        
        .dashed-line {
            border-top: 1px dashed #000;
            margin: 4px 0;
        }
        .double-line {
            border-top: 1px double #000;
            border-bottom: 1px double #000;
            height: 2px;
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 1px 0;
            vertical-align: top;
        }
    </style>
</head>
<body>

    <!-- Header Toko -->
    <div class="text-center">
        <div class="fw-bold text-uppercase" style="font-size: 1.1em;">{{ $setting->store_name ?? 'SI TOKO RETAIL' }}</div>
        @if($setting->address)
            <div style="font-size: 0.85em;">{{ $setting->address }}</div>
        @endif
        @if($setting->phone)
            <div style="font-size: 0.85em;">Telp: {{ $setting->phone }}</div>
        @endif
        @if($setting->header_receipt)
            <div style="font-size: 0.8em;">{{ $setting->header_receipt }}</div>
        @endif
    </div>

    <div class="double-line"></div>

    <!-- Judul Dokumen -->
    <div class="text-center fw-bold text-uppercase" style="font-size: 0.95em;">
        BUKTI PEMBAYARAN HUTANG
    </div>
    <div class="text-center" style="font-size: 0.8em;">
        (KUITANSI RESMI)
    </div>

    <div class="dashed-line"></div>

    <!-- Metadata Kuitansi -->
    <table>
        <tr>
            <td style="width: 44%;">No. Kuitansi</td>
            <td style="width: 4%;">:</td>
            <td class="text-right fw-bold">KW-{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td>No. Faktur</td>
            <td>:</td>
            <td class="text-right font-monospace">{{ $debt->sale ? $debt->sale->invoice_number : ('TRX-' . $debt->id) }}</td>
        </tr>
        <tr>
            <td>Tgl Bayar</td>
            <td>:</td>
            <td class="text-right">{{ $payment->payment_date->format('d/m/Y') }} {{ $payment->created_at ? $payment->created_at->format('H:i') : '' }}</td>
        </tr>
        <tr>
            <td>Pelanggan</td>
            <td>:</td>
            <td class="text-right fw-bold">{{ $customer->name ?? 'Pelanggan Umum' }}</td>
        </tr>
        @if($customer && $customer->phone)
        <tr>
            <td>No. Telepon</td>
            <td>:</td>
            <td class="text-right">{{ $customer->phone }}</td>
        </tr>
        @endif
        <tr>
            <td>Kasir</td>
            <td>:</td>
            <td class="text-right">{{ $payment->user?->name ?? 'Kasir' }}</td>
        </tr>
        <tr>
            <td>Metode Bayar</td>
            <td>:</td>
            <td class="text-right">
                {{ match($payment->payment_method) {
                    'cash'     => 'Tunai',
                    'transfer' => 'Transfer Bank',
                    'qris'     => 'QRIS',
                    default    => strtoupper($payment->payment_method)
                } }}
            </td>
        </tr>
    </table>

    <div class="dashed-line"></div>

    <!-- Rincian Finansial -->
    <table>
        <tr>
            <td>Total Hutang Awal</td>
            <td class="text-right">Rp {{ number_format($debt->total_debt, 0, ',', '.') }}</td>
        </tr>
        @php
            $prevPaid = max(0, (float) ($debt->total_debt - $debt->remaining_debt - $payment->amount));
        @endphp
        @if($prevPaid > 0)
        <tr>
            <td>Dibayar Sebelumnya</td>
            <td class="text-right">Rp {{ number_format($prevPaid, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="fw-bold">
            <td style="padding-top: 3px;">DIBAYAR SEKARANG</td>
            <td class="text-right" style="padding-top: 3px;">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
        </tr>
        <tr class="fw-bold">
            <td style="padding-top: 2px;">SISA HUTANG</td>
            <td class="text-right" style="padding-top: 2px;">Rp {{ number_format($debt->remaining_debt, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Status Tagihan</td>
            <td class="text-right fw-bold">{{ $debt->remaining_debt <= 0 ? '*** LUNAS ***' : 'BELUM LUNAS' }}</td>
        </tr>
    </table>

    @if($payment->note)
    <div class="dashed-line"></div>
    <div style="font-size: 0.85em;">
        <strong>Catatan:</strong> {{ $payment->note }}
    </div>
    @endif

    <div class="double-line"></div>

    <!-- Footer -->
    <div class="text-center" style="font-size: 0.8em; margin-top: 4px;">
        @if($setting->footer_receipt)
            <div>{{ $setting->footer_receipt }}</div>
        @else
            <div>Terima kasih atas pembayaran Anda.</div>
            <div>Simpan kuitansi ini sebagai bukti bayar sah.</div>
        @endif
        <div style="margin-top: 4px; font-size: 0.75em; color: #444;">
            Dicetak: {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

</body>
</html>
