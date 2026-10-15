<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Valuasi Stok</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        .header { border-bottom: 2px solid #333; padding-bottom: 8px; margin-bottom: 12px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; color: #222; }
        .header p { margin: 2px 0; color: #666; font-size: 10px; }
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
                    <h3 style="margin: 0; color: #0056b3;">LAPORAN STOK & VALUASI ASET</h3>
                    <p>Posisi Per Tanggal: {{ date('d/m/Y H:i') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <table class="summary-box">
        <tr>
            <td>
                <span>Total Item Produk</span>
                <strong>{{ count($products) }} SKU</strong>
            </td>
            <td>
                <span>Item Menipis / Habis</span>
                <strong style="color: #dc3545;">{{ $lowStockCount }} Menipis · {{ $outOfStockCount }} Habis</strong>
            </td>
            <td>
                <span>Total Nilai Aset (HPP)</span>
                <strong style="color: #0056b3;">Rp {{ number_format($totalAssetValue, 0, ',', '.') }}</strong>
            </td>
            <td>
                <span>Proyeksi Nilai Jual</span>
                <strong style="color: #28a745;">Rp {{ number_format($totalRetailValue, 0, ',', '.') }}</strong>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="25">No</th>
                <th>Barcode</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th class="text-end">Harga Beli (HPP)</th>
                <th class="text-end">Harga Jual</th>
                <th class="text-center">Sisa Stok</th>
                <th class="text-end">Total Nilai Aset</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $idx => $prod)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td><code>{{ $prod->barcode }}</code></td>
                <td><strong>{{ $prod->name }}</strong></td>
                <td>{{ $prod->category->name ?? '-' }}</td>
                <td class="text-end">Rp {{ number_format($prod->cost_price, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</td>
                <td class="text-center"><strong>{{ $prod->stock }}</strong> {{ $prod->unit->symbol ?? 'Pcs' }}</td>
                <td class="text-end"><strong>Rp {{ number_format($prod->stock * $prod->cost_price, 0, ',', '.') }}</strong></td>
                <td class="text-center">
                    @if($prod->stock <= 0)
                        <span style="color: red; font-weight: bold;">HABIS</span>
                    @elseif($prod->stock <= $prod->min_stock)
                        <span style="color: orange; font-weight: bold;">MENIPIS</span>
                    @else
                        <span style="color: green;">AMAN</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="6" class="text-end">TOTAL VALUASI ASET:</td>
                <td class="text-center">{{ $products->sum('stock') }} unit</td>
                <td class="text-end" style="color: #0056b3;">Rp {{ number_format($totalAssetValue, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <table class="footer-sig">
        <tr>
            <td>
                <p>Penanggung Jawab Gudang:</p>
                <br><br><br>
                <p><u>( Staff Gudang )</u></p>
            </td>
            <td>
                <p>Mengetahui:</p>
                <br><br><br>
                <p><u>( Pemilik Toko / Manager )</u></p>
            </td>
        </tr>
    </table>
</body>
</html>
