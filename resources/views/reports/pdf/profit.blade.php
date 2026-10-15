<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .header { border-bottom: 2px solid #333; padding-bottom: 8px; margin-bottom: 15px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; color: #222; }
        .header p { margin: 2px 0; color: #666; font-size: 10px; }
        .report-box { border: 1px solid #ddd; padding: 15px; background-color: #fff; margin-bottom: 20px; }
        .section-header { font-weight: bold; text-transform: uppercase; margin-top: 10px; margin-bottom: 6px; font-size: 11px; }
        .row-item { display: table; width: 100%; margin-bottom: 4px; }
        .col-label { display: table-cell; padding-left: 20px; }
        .col-value { display: table-cell; text-align: right; width: 140px; }
        .row-total { display: table; width: 100%; font-weight: bold; border-top: 1px solid #ccc; padding-top: 4px; margin-top: 4px; margin-bottom: 12px; }
        .row-grand-total { display: table; width: 100%; font-weight: bold; border-top: 2px solid #333; border-bottom: 2px solid #333; padding: 8px 0; margin-top: 15px; font-size: 13px; }
        .footer-sig { width: 100%; margin-top: 30px; }
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
                    <h3 style="margin: 0; color: #0056b3;">LAPORAN LABA RUGI (INCOME STATEMENT)</h3>
                    <p>Periode: {{ Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-box">
        <!-- 1. PENDAPATAN -->
        <div class="section-header" style="color: #0056b3;">1. PENDAPATAN PENJUALAN (REVENUE)</div>
        <div class="row-item">
            <span class="col-label">Total Penjualan Kotor (Gross Sales)</span>
            <span class="col-value">Rp {{ number_format($grossSales, 0, ',', '.') }}</span>
        </div>
        <div class="row-item" style="color: #dc3545;">
            <span class="col-label">Potongan & Diskon Penjualan</span>
            <span class="col-value">(Rp {{ number_format($totalDiscounts, 0, ',', '.') }})</span>
        </div>
        <div class="row-total">
            <span class="col-label" style="padding-left: 0;">Total Pendapatan Bersih (Net Sales)</span>
            <span class="col-value" style="color: #0056b3;">Rp {{ number_format($netSales, 0, ',', '.') }}</span>
        </div>

        <!-- 2. HPP -->
        <div class="section-header" style="color: #d39e00;">2. HARGA POKOK PENJUALAN (HPP)</div>
        <div class="row-item" style="color: #666;">
            <span class="col-label">Total Beban Pokok Barang Terjual (COGS)</span>
            <span class="col-value">(Rp {{ number_format($totalHpp, 0, ',', '.') }})</span>
        </div>
        <div class="row-total">
            <span class="col-label" style="padding-left: 0; color: #28a745;">LABA KOTOR (GROSS PROFIT)</span>
            <span class="col-value" style="color: #28a745;">Rp {{ number_format($grossProfit, 0, ',', '.') }}</span>
        </div>

        <!-- 3. BEBAN OPERASIONAL -->
        <div class="section-header" style="color: #dc3545;">3. BIAYA & BEBAN OPERASIONAL (OPERATING EXPENSES)</div>
        @forelse($expensesByCategory as $exp)
        <div class="row-item">
            <span class="col-label">Beban {{ $exp->expense_category }}</span>
            <span class="col-value">(Rp {{ number_format($exp->total, 0, ',', '.') }})</span>
        </div>
        @empty
        <div class="row-item" style="color: #666;">
            <span class="col-label">Biaya Operasional Toko</span>
            <span class="col-value">Rp 0</span>
        </div>
        @endforelse
        <div class="row-total">
            <span class="col-label" style="padding-left: 0; color: #dc3545;">Total Biaya Operasional</span>
            <span class="col-value" style="color: #dc3545;">(Rp {{ number_format($totalExpenses, 0, ',', '.') }})</span>
        </div>

        <!-- 4. HASIL BERSIH -->
        <div class="row-grand-total" style="color: {{ $netProfit >= 0 ? '#0056b3' : '#dc3545' }};">
            <span class="col-label" style="padding-left: 0;">{{ $netProfit >= 0 ? 'LABA BERSIH (NET PROFIT)' : 'RUGI BERSIH (NET LOSS)' }}</span>
            <span class="col-value">Rp {{ number_format($netProfit, 0, ',', '.') }}</span>
        </div>
        <div style="text-align: right; font-size: 10px; color: #666; margin-top: 4px;">
            Margin Laba Bersih: <strong>{{ $profitMargin }}%</strong>
        </div>
    </div>

    <table class="footer-sig">
        <tr>
            <td>
                <p>Dibuat oleh bagian Keuangan:</p>
                <br><br><br>
                <p><u>( Staff Keuangan / Admin )</u></p>
            </td>
            <td>
                <p>Disetujui oleh:</p>
                <br><br><br>
                <p><u>( Pemilik Toko / Direksi )</u></p>
            </td>
        </tr>
    </table>
</body>
</html>
