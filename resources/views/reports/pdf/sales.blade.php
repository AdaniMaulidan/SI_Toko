<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        .header { border-bottom: 2px solid #333; padding-bottom: 8px; margin-bottom: 12px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; color: #222; }
        .header p { margin: 2px 0; color: #666; font-size: 10px; }
        .meta-table { width: 100%; margin-bottom: 15px; }
        .meta-table td { padding: 2px 0; font-size: 10px; }
        .summary-box { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .summary-box td { border: 1px solid #ddd; padding: 6px 10px; background-color: #f9f9f9; text-align: center; }
        .summary-box td span { display: block; font-size: 9px; color: #666; text-transform: uppercase; }
        .summary-box td strong { font-size: 13px; color: #111; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #ddd; padding: 5px 6px; font-size: 9px; }
        table.data-table th { background-color: #f2f2f2; text-align: left; font-weight: bold; text-transform: uppercase; }
        table.data-table tr:nth-child(even) td { background-color: #fafafa; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .footer-sig { width: 100%; margin-top: 25px; }
        .footer-sig td { width: 50%; text-align: center; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <h2>{{ $setting->store_name ?? 'SI TOKO' }}</h2>
                    <p>{{ $setting->address ?? 'Alamat Toko' }} | Telp: {{ $setting->phone ?? '-' }}</p>
                </td>
                <td style="text-align: right; vertical-align: bottom;">
                    <h3 style="margin: 0; color: #0056b3;">LAPORAN PENJUALAN</h3>
                    <p>Periode: {{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <table class="summary-box">
        <tr>
            <td>
                <span>Total Transaksi</span>
                <strong>{{ number_format($totalTransactions) }} Struk</strong>
            </td>
            <td>
                <span>Kuantitas Terjual</span>
                <strong>{{ number_format($totalQtySold) }} Pcs</strong>
            </td>
            <td>
                <span>Total Diskon</span>
                <strong>Rp {{ number_format($totalDiscounts, 0, ',', '.') }}</strong>
            </td>
            <td>
                <span>Total Pendapatan Bersih</span>
                <strong style="color: #28a745;">Rp {{ number_format($totalNetSales, 0, ',', '.') }}</strong>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="25">No</th>
                <th>Tanggal & Jam</th>
                <th>No Faktur</th>
                <th>Kasir</th>
                <th>Pelanggan</th>
                <th>Metode</th>
                <th class="text-end">Subtotal</th>
                <th class="text-end">Diskon</th>
                <th class="text-end">Total Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $idx => $s)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>{{ $s->created_at->format('d/m/Y H:i') }}</td>
                <td><strong>{{ $s->invoice_number }}</strong></td>
                <td>{{ $s->user?->name ?? 'Kasir' }}</td>
                <td>{{ $s->customer?->name ?? 'Umum' }}</td>
                <td class="text-center">{{ strtoupper($s->payment_method) }}</td>
                <td class="text-end">Rp {{ number_format($s->subtotal, 0, ',', '.') }}</td>
                <td class="text-end">{{ $s->discount > 0 ? 'Rp ' . number_format($s->discount, 0, ',', '.') : '-' }}</td>
                <td class="text-end"><strong>Rp {{ number_format($s->total_amount, 0, ',', '.') }}</strong></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="6" class="text-end">TOTAL KESELURUHAN:</td>
                <td class="text-end">Rp {{ number_format($totalGrossSales, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($totalDiscounts, 0, ',', '.') }}</td>
                <td class="text-end" style="color: #0056b3;">Rp {{ number_format($totalNetSales, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="footer-sig">
        <tr>
            <td>
                <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
                <br><br><br>
                <p><u>( Staff Kasir / Admin )</u></p>
            </td>
            <td>
                <p>&nbsp;</p>
                <br><br><br>
                <p><u>( Pimpinan / Pemilik Toko )</u></p>
            </td>
        </tr>
    </table>
</body>
</html>
