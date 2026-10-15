<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja Produk</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        .header { border-bottom: 2px solid #333; padding-bottom: 8px; margin-bottom: 12px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; color: #222; }
        .header p { margin: 2px 0; color: #666; font-size: 10px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        table.data-table th, table.data-table td { border: 1px solid #ddd; padding: 5px 6px; font-size: 9px; }
        table.data-table th { background-color: #f2f2f2; text-align: left; font-weight: bold; text-transform: uppercase; }
        table.data-table tr:nth-child(even) td { background-color: #fafafa; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .footer-sig { width: 100%; margin-top: 25px; }
        .footer-sig td { width: 50%; text-align: center; font-size: 10px; }
        .section-title { font-size: 12px; font-weight: bold; margin-top: 15px; margin-bottom: 5px; color: #0056b3; }
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
                    <h3 style="margin: 0; color: #0056b3;">LAPORAN PRODUK TERLARIS</h3>
                    <p>Periode: {{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">TOP 10 PRODUK TERLARIS (BERDASARKAN KUANTITAS TERJUAL)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="30">Rank</th>
                <th>Barcode</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th class="text-center">Kuantitas Terjual</th>
                <th class="text-end">Total Omset</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topByQty as $idx => $item)
            <tr>
                <td class="text-center"><strong>#{{ $idx + 1 }}</strong></td>
                <td>{{ $item->barcode }}</td>
                <td><strong>{{ $item->product_name }}</strong></td>
                <td>{{ $item->category_name ?? '-' }}</td>
                <td class="text-center"><strong>{{ number_format($item->total_qty) }}</strong> {{ $item->unit_symbol ?? 'Pcs' }}</td>
                <td class="text-end">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Belum ada data penjualan pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">TOP 10 PRODUK KONTRIBUSI OMSET & LABA TERTINGGI</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="30">Rank</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th class="text-center">Qty Terjual</th>
                <th class="text-end">Total Omset</th>
                <th class="text-end">Laba Kotor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topByRevenue as $idx => $item)
            <tr>
                <td class="text-center"><strong>#{{ $idx + 1 }}</strong></td>
                <td><strong>{{ $item->product_name }}</strong></td>
                <td>{{ $item->category_name ?? '-' }}</td>
                <td class="text-center">{{ number_format($item->total_qty) }} {{ $item->unit_symbol ?? 'Pcs' }}</td>
                <td class="text-end"><strong>Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</strong></td>
                <td class="text-end" style="color: #28a745;"><strong>+Rp {{ number_format($item->gross_profit, 0, ',', '.') }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Belum ada data penjualan pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer-sig">
        <tr>
            <td>
                <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
                <br><br><br>
                <p><u>( Staff Kasir / Admin )</u></p>
            </td>
            <td>
                <p>Mengetahui:</p>
                <br><br><br>
                <p><u>( Pemilik Toko )</u></p>
            </td>
        </tr>
    </table>
</body>
</html>
