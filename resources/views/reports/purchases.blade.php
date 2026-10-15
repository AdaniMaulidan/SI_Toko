@extends('layouts.app')

@section('title', 'Laporan Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-0">
            <span class="text-muted fw-light">Laporan /</span> Pembelian (Kulakan)
        </h4>
        <small class="text-muted">Rekap pengadaan stok produk per supplier, nominal belanja, dan status pembayaran</small>
    </div>
    <div class="d-print-none d-flex gap-2 flex-wrap">
        <a href="{{ route('reports.purchases.excel', request()->all()) }}" class="btn btn-outline-success">
            <i class="bx bxs-file-export me-1"></i> Excel (.csv)
        </a>
        <a href="{{ route('reports.purchases.pdf', request()->all()) }}" class="btn btn-outline-danger" target="_blank">
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
        <h4 class="fw-bold mt-3 mb-1 text-uppercase">LAPORAN PEMBELIAN / PENGADAAN (KULAKAN)</h4>
        <small class="text-muted">Periode: {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</small>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-4 d-print-none">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.purchases') }}" class="row gx-3 gy-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label" for="month">Bulan Laporan</label>
                <input type="month" id="month" name="month" class="form-control" value="{{ $month }}">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label" for="supplier_id">Supplier</label>
                <select id="supplier_id" name="supplier_id" class="form-select">
                    <option value="all">Semua Supplier</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label" for="payment_status">Status Bayar</label>
                <select id="payment_status" name="payment_status" class="form-select">
                    <option value="all">Semua Status</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Sebagian (Tempo)</option>
                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Belum Bayar (Hutang)</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                <a href="{{ route('reports.purchases') }}" class="btn btn-outline-secondary" title="Reset"><i class="bx bx-reset"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-4 mb-3 mb-lg-0">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <h6 class="text-white mb-2">Total Belanja Kulakan</h6>
                <h3 class="text-white fw-bold mb-1">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</h3>
                <small class="text-white-50">Dari {{ $totalCount }} Faktur / Dokumen PO</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4 mb-3 mb-lg-0">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <h6 class="text-white mb-2">Pembelian Terbayar (Lunas)</h6>
                <h3 class="text-white fw-bold mb-1">Rp {{ number_format($totalPaid, 0, ',', '.') }}</h3>
                <small class="text-white-50">{{ $paidCount }} Faktur Lunas</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4 mb-3 mb-lg-0">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <h6 class="text-white mb-2">Sisa Hutang ke Supplier</h6>
                <h3 class="text-white fw-bold mb-1">Rp {{ number_format($totalDebt, 0, ',', '.') }}</h3>
                <small class="text-white-50">{{ $debtCount }} Faktur Tempo / Hutang</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Rincian Pembelian (Periode: {{ Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }})</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>No. Faktur / PO</th>
                    <th>Supplier</th>
                    <th>Item Barang Masuk</th>
                    <th>Status</th>
                    <th class="text-end">Total Biaya</th>
                    <th class="text-end">Dibayar</th>
                    <th class="text-end">Sisa Hutang</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($purchases as $p)
                <tr>
                    <td>{{ $p->purchase_date->translatedFormat('d M Y') }}</td>
                    <td>
                        <a href="{{ route('purchases.show', $p->id) }}" class="fw-semibold text-primary">
                            {{ $p->invoice_number }}
                        </a>
                    </td>
                    <td>{{ $p->supplier?->name ?? 'Supplier Umum' }}</td>
                    <td>{{ $p->items->sum('quantity') }} unit ({{ $p->items->count() }} macam)</td>
                    <td>
                        @if($p->payment_status === 'paid')
                            <span class="badge bg-label-success">Lunas</span>
                        @elseif($p->payment_status === 'partial')
                            <span class="badge bg-label-warning">Sebagian</span>
                        @else
                            <span class="badge bg-label-danger">Hutang</span>
                        @endif
                    </td>
                    <td class="text-end fw-bold">Rp {{ number_format($p->total_amount, 0, ',', '.') }}</td>
                    <td class="text-end text-success">Rp {{ number_format($p->paid_amount, 0, ',', '.') }}</td>
                    <td class="text-end {{ ($p->total_amount - $p->paid_amount) > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                        Rp {{ number_format(max(0, $p->total_amount - $p->paid_amount), 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="bx bx-package fs-4 d-block mb-1"></i>
                        Tidak ada data pembelian pada bulan ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="5" class="text-end">TOTAL:</td>
                    <td class="text-end text-primary">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</td>
                    <td class="text-end text-success">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td>
                    <td class="text-end text-danger">Rp {{ number_format($totalDebt, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($purchases->hasPages())
    <div class="card-footer d-flex justify-content-center d-print-none">
        {{ $purchases->links() }}
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
