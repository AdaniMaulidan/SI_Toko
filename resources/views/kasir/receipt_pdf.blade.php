<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk - {{ $sale->invoice_number }}</title>
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
        @if($setting->logo && file_exists(public_path('storage/' . $setting->logo)))
            <div style="margin-bottom: 3px;">
                <img src="{{ public_path('storage/' . $setting->logo) }}" alt="Logo" style="max-height: 38px; max-width: 100px; object-fit: contain;">
            </div>
        @endif
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

    <!-- Meta Transaksi -->
    <table style="font-size: 0.9em;">
        <tr>
            <td>No. Faktur</td>
            <td class="text-right fw-bold">{{ $sale->invoice_number }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td class="text-right">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td class="text-right">{{ $sale->user?->name ?? 'Kasir' }}</td>
        </tr>
        <tr>
            <td>Pelanggan</td>
            <td class="text-right">{{ $sale->customer?->name ?? 'Pelanggan Umum' }}</td>
        </tr>
    </table>

    <div class="dashed-line"></div>

    <!-- Daftar Barang -->
    <table>
        @foreach($sale->items as $item)
            @php
                $itemGross = $item->selling_price * $item->quantity;
                $hasItemDisc = ($itemGross > $item->subtotal);
                $itemDiscAmount = $itemGross - $item->subtotal;
            @endphp
            <tr>
                <td colspan="2" class="fw-bold">{{ $item->product?->name ?? 'Barang' }}</td>
            </tr>
            <tr>
                <td style="font-size: 0.9em;">{{ $item->quantity }} x {{ number_format($item->selling_price, 0, ',', '.') }}</td>
                <td class="text-right fw-bold">{{ number_format($itemGross, 0, ',', '.') }}</td>
            </tr>
            @if($hasItemDisc)
                <tr style="font-size: 0.85em; color: #555;">
                    <td>(Diskon Item)</td>
                    <td class="text-right">-{{ number_format($itemDiscAmount, 0, ',', '.') }}</td>
                </tr>
            @endif
        @endforeach
    </table>

    <div class="dashed-line"></div>

    <!-- Total & Pembayaran -->
    <table>
        <tr>
            <td>Subtotal</td>
            <td class="text-right">{{ number_format($sale->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($sale->discount > 0)
            <tr>
                <td>Diskon Transaksi</td>
                <td class="text-right">-{{ number_format($sale->discount, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr class="fw-bold" style="font-size: 1.05em;">
            <td>TOTAL</td>
            <td class="text-right">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

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

    <table>
        <tr>
            <td>Metode Bayar</td>
            <td class="text-right fw-bold">{{ $methodLabel }}</td>
        </tr>

        @if($sale->payment_method === 'cash')
            <tr>
                <td>Tunai Diterima</td>
                <td class="text-right">{{ number_format($sale->paid_amount, 0, ',', '.') }}</td>
            </tr>
            <tr class="fw-bold">
                <td>Kembalian</td>
                <td class="text-right">{{ number_format($sale->change_amount, 0, ',', '.') }}</td>
            </tr>
        @elseif($sale->payment_method === 'debt')
            @php
                $debt = $sale->debt;
                $dp = (float) $sale->paid_amount;
                $remaining = (float) ($debt?->remaining_debt ?? ($sale->total_amount - $dp));
                $accumulated = (float) ($sale->customer?->total_debt ?? $remaining);
            @endphp
            @if($dp > 0)
                <tr>
                    <td>Uang Muka (DP)</td>
                    <td class="text-right">{{ number_format($dp, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr class="fw-bold">
                <td>Sisa Hutang Faktur</td>
                <td class="text-right">{{ number_format($remaining, 0, ',', '.') }}</td>
            </tr>
            @if($debt && $debt->due_date)
                <tr style="font-size: 0.85em;">
                    <td>Jatuh Tempo</td>
                    <td class="text-right">{{ $debt->due_date->format('d/m/Y') }}</td>
                </tr>
            @endif
            <tr style="font-size: 0.85em; color: #555;">
                <td>Total Saldo Hutang</td>
                <td class="text-right">Rp {{ number_format($accumulated, 0, ',', '.') }}</td>
            </tr>
        @else
            <tr>
                <td>Bayar (Lunas)</td>
                <td class="text-right">{{ number_format($sale->paid_amount, 0, ',', '.') }}</td>
            </tr>
        @endif
    </table>

    @if($sale->notes)
        <div class="dashed-line"></div>
        <div style="font-size: 0.85em;">
            <span class="fw-bold">Catatan:</span> {{ $sale->notes }}
        </div>
    @endif

    <div class="double-line"></div>

    <!-- Footer Struk -->
    <div class="text-center" style="font-size: 0.85em; margin-top: 4px;">
        @if($setting->footer_receipt)
            <div>{{ $setting->footer_receipt }}</div>
        @else
            <div>Terima kasih atas kunjungan Anda!</div>
        @endif
        <div style="margin-top: 2px;">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</div>
        <div style="font-size: 0.8em; color: #555; margin-top: 3px;">*** {{ config('app.name', 'SI Toko') }} ***</div>
    </div>

</body>
</html>
