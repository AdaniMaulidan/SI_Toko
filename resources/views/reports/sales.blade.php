@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-0">
            <span class="text-muted fw-light">Laporan /</span> Penjualan
        </h4>
        <small class="text-muted">Rekap transaksi penjualan kasir, omset, diskon, dan metode pembayaran</small>
    </div>
    <div class="d-print-none d-flex gap-2 flex-wrap">
        <a href="{{ route('reports.sales.excel', request()->all()) }}" class="btn btn-outline-success">
            <i class="bx bxs-file-export me-1"></i> Excel (.csv)
        </a>
        <a href="{{ route('reports.sales.pdf', request()->all()) }}" class="btn btn-outline-danger" target="_blank">
            <i class="bx bxs-file-pdf me-1"></i> Unduh PDF
        </a>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="bx bx-printer me-1"></i> Cetak Laporan
        </button>
    </div>
</div>

<!-- Print-Only Formal Header -->
<div class="d-none d-print-block mb-4 pb-2 border-bottom">
    <div class="text-center">
        <h3 class="fw-bold mb-1">{{ \App\Models\StoreSetting::getSettings()['store_name'] ?? 'SI TOKO' }}</h3>
        <p class="mb-1 text-muted">{{ \App\Models\StoreSetting::getSettings()['address'] ?? '' }} | Telp: {{ \App\Models\StoreSetting::getSettings()['phone'] ?? '' }}</p>
        <h4 class="fw-bold mt-3 mb-1 text-uppercase">LAPORAN TRANSAKSI PENJUALAN</h4>
        <small class="text-muted">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</small>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-4 d-print-none">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.sales') }}" class="row gx-3 gy-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label" for="start_date">Tanggal Mulai</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label" for="end_date">Tanggal Akhir</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label" for="user_id">Kasir</label>
                <select id="user_id" name="user_id" class="form-select">
                    <option value="all">Semua Kasir</option>
                    @foreach($cashiers as $c)
                    <option value="{{ $c->id }}" {{ request('user_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label" for="payment_method">Metode Bayar</label>
                <select id="payment_method" name="payment_method" class="form-select">
                    <option value="all">Semua Metode</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                    <option value="qris" {{ request('payment_method') == 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                    <option value="debt" {{ request('payment_method') == 'debt' ? 'selected' : '' }}>Hutang (Kredit)</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-12 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary" title="Reset"><i class="bx bx-reset"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-sm-6 col-md-3 mb-3 mb-md-0">
        <div class="card card-border-shadow-primary h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                    <div class="avatar me-2">
                        <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-receipt fs-4"></i></span>
                    </div>
                    <h4 class="ms-1 mb-0">{{ number_format($totalTransactions) }}</h4>
                </div>
                <p class="mb-1 text-muted">Total Transaksi</p>
                <small class="text-primary fw-medium">Struk Penjualan</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 mb-3 mb-md-0">
        <div class="card card-border-shadow-success h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                    <div class="avatar me-2">
                        <span class="avatar-initial rounded bg-label-success"><i class="bx bx-dollar-circle fs-4"></i></span>
                    </div>
                    <h4 class="ms-1 mb-0 text-success">Rp {{ number_format($totalNetSales, 0, ',', '.') }}</h4>
                </div>
                <p class="mb-1 text-muted">Total Pendapatan Bersih</p>
                <small class="text-muted">Kotor: Rp {{ number_format($totalGrossSales, 0, ',', '.') }}</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 mb-3 mb-md-0">
        <div class="card card-border-shadow-warning h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                    <div class="avatar me-2">
                        <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-package fs-4"></i></span>
                    </div>
                    <h4 class="ms-1 mb-0 text-warning">{{ number_format($totalQtySold) }}</h4>
                </div>
                <p class="mb-1 text-muted">Barang Terjual (Qty)</p>
                <small class="text-muted">Diskon: Rp {{ number_format($totalDiscounts, 0, ',', '.') }}</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 mb-3 mb-md-0">
        <div class="card card-border-shadow-info h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                    <div class="avatar me-2">
                        <span class="avatar-initial rounded bg-label-info"><i class="bx bx-wallet fs-4"></i></span>
                    </div>
                    <h4 class="ms-1 mb-0">Rp {{ number_format($averagePerTrx, 0, ',', '.') }}</h4>
                </div>
                <p class="mb-1 text-muted">Rata-rata per Transaksi</p>
                <small class="text-muted">Basket Size</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Rincian Laporan Penjualan ({{ Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} s/d {{ Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }})</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tanggal & Jam</th>
                    <th>No. Faktur</th>
                    <th>Kasir</th>
                    <th>Pelanggan</th>
                    <th>Jml Item</th>
                    <th>Metode</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-end">Diskon</th>
                    <th class="text-end">Total Akhir</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($sales as $sale)
                <tr>
                    <td>
                        <div>{{ $sale->created_at->translatedFormat('d M Y') }}</div>
                        <small class="text-muted">{{ $sale->created_at->format('H:i') }}</small>
                    </td>
                    <td>
                        <a href="{{ route('transactions.show', $sale->id) }}" class="fw-semibold text-primary">
                            {{ $sale->invoice_number }}
                        </a>
                    </td>
                    <td><small class="text-muted">{{ $sale->user?->name ?? 'Kasir' }}</small></td>
                    <td>{{ $sale->customer?->name ?? 'Pelanggan Umum' }}</td>
                    <td>{{ $sale->items->sum('quantity') }} item</td>
                    <td>
                        @php
                            $methodBadge = match($sale->payment_method) {
                                'cash'     => 'bg-label-success',
                                'qris'     => 'bg-label-info',
                                'transfer' => 'bg-label-primary',
                                'debt'     => 'bg-label-warning',
                                default    => 'bg-label-secondary',
                            };
                            $methodLabel = match($sale->payment_method) {
                                'cash'     => 'Tunai',
                                'qris'     => 'QRIS',
                                'transfer' => 'Transfer',
                                'debt'     => 'Hutang',
                                default    => ucfirst($sale->payment_method),
                            };
                        @endphp
                        <span class="badge {{ $methodBadge }}">{{ $methodLabel }}</span>
                    </td>
                    <td class="text-end">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                    <td class="text-end text-danger">
                        {{ $sale->discount > 0 ? '-Rp ' . number_format($sale->discount, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-end fw-bold">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="bx bx-receipt fs-4 d-block mb-1"></i>
                        Tidak ada data penjualan pada rentang filter ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="8" class="text-end">TOTAL PENDAPATAN BERSIH:</td>
                    <td class="text-end text-primary fs-5">Rp {{ number_format($totalNetSales, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($sales->hasPages())
    <div class="card-footer d-flex justify-content-center d-print-none">
        {{ $sales->links() }}
    </div>
    @endif
</div>
@endsection

@section('page-style')
<style>
    @media print {
        .layout-menu, .layout-navbar, .d-print-none, .btn, .footer { display: none !important; }
        .content-wrapper { padding: 0 !important; margin: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        @page { size: landscape; margin: 10mm; }
    }
</style>
@endsection
