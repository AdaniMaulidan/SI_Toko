<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Arus Kas</title>
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
        .row-grand-total { display: table; width: 100%; font-weight: bold; border-top: 2px solid #333; border-bottom: 2px solid #333; padding: 8px 0; margin-top: 15px; font-size: 13px; color: #0056b3; }
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
                    <h3 style="margin: 0; color: #0056b3;">LAPORAN ARUS KAS (CASH FLOW STATEMENT)</h3>
                    <p>Periode: {{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-box">
        <!-- SALDO AWAL -->
        <div class="row-item" style="font-weight: bold; font-size: 11px; background-color: #f7f7f7; padding: 6px;">
            <span class="col-label" style="padding-left: 5px;">SALDO AWAL KAS (Per {{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }})</span>
            <span class="col-value">Rp {{ number_format($openingBalance, 0, ',', '.') }}</span>
        </div>

        <!-- 1. ARUS MASUK -->
        <div class="section-header" style="color: #28a745; margin-top: 15px;">1. ARUS KAS MASUK (INFLOW)</div>
        @forelse($inflowsByCategory as $in)
        <div class="row-item">
            <span class="col-label">Penerimaan {{ $in->category }}</span>
            <span class="col-value" style="color: #28a745;">+Rp {{ number_format($in->total, 0, ',', '.') }}</span>
        </div>
        @empty
        <div class="row-item" style="color: #666;">
            <span class="col-label">Penerimaan Kas</span>
            <span class="col-value">Rp 0</span>
        </div>
        @endforelse
        <div class="row-total">
            <span class="col-label" style="padding-left: 0; color: #28a745;">TOTAL ARUS KAS MASUK</span>
            <span class="col-value" style="color: #28a745;">+Rp {{ number_format($totalInflow, 0, ',', '.') }}</span>
        </div>

        <!-- 2. ARUS KELUAR -->
        <div class="section-header" style="color: #dc3545;">2. ARUS KAS KELUAR (OUTFLOW)</div>
        @forelse($outflowsByCategory as $out)
        <div class="row-item">
            <span class="col-label">Pengeluaran {{ $out->category }}</span>
            <span class="col-value" style="color: #dc3545;">(Rp {{ number_format($out->total, 0, ',', '.') }})</span>
        </div>
        @empty
        <div class="row-item" style="color: #666;">
            <span class="col-label">Pengeluaran Kas</span>
            <span class="col-value">Rp 0</span>
        </div>
        @endforelse
        <div class="row-total">
            <span class="col-label" style="padding-left: 0; color: #dc3545;">TOTAL ARUS KAS KELUAR</span>
            <span class="col-value" style="color: #dc3545;">(Rp {{ number_format($totalOutflow, 0, ',', '.') }})</span>
        </div>

        <!-- 3. PERUBAHAN BERSIH -->
        <div class="row-item" style="font-weight: bold; border-top: 1px dashed #aaa; padding-top: 6px; margin-top: 6px;">
            <span class="col-label" style="padding-left: 0;">KENAIKAN / (PENURUNAN) BERSIH KAS</span>
            <span class="col-value" style="color: {{ $netCashChange >= 0 ? '#28a745' : '#dc3545' }};">
                {{ $netCashChange >= 0 ? '+' : '' }}Rp {{ number_format($netCashChange, 0, ',', '.') }}
            </span>
        </div>

        <!-- 4. SALDO AKHIR -->
        <div class="row-grand-total">
            <span class="col-label" style="padding-left: 0;">SALDO AKHIR KAS (CASH ON HAND)</span>
            <span class="col-value">Rp {{ number_format($closingBalance, 0, ',', '.') }}</span>
        </div>
    </div>

    <table class="footer-sig">
        <tr>
            <td>
                <p>Dicatat oleh:</p>
                <br><br><br>
                <p><u>( Kasir / Keuangan )</u></p>
            </td>
            <td>
                <p>Mengetahui:</p>
                <br><br><br>
                <p><u>( Pemilik Toko / Pimpinan )</u></p>
            </td>
        </tr>
    </table>
</body>
</html>
