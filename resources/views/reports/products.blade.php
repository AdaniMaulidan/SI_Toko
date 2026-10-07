@extends('layouts.app')

@section('title', 'Laporan Kinerja Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Laporan /</span> Kinerja Produk Terlaris
    </h4>
    <button type="button" class="btn btn-primary" onclick="window.print()">
        <i class="bx bx-printer me-1"></i> Cetak Laporan
    </button>
</div>

<!-- Filter Card -->
<div class="card mb-4 d-print-none">
    <div class="card-body">
        <form class="row gx-3 gy-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" class="form-control" value="{{ date('Y-m-01') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Kategori</label>
                <select class="form-select">
                    <option value="all">Semua Kategori</option>
                    <option value="1">Sembako</option>
                    <option value="2">Minuman</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-primary w-100">Filter Laporan</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <!-- Top 5 Products -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0 text-success"><i class="bx bx-trending-up me-1"></i> 5 Produk Terlaris (Berdasarkan Qty)</h5>
            </div>
            <div class="card-body mt-3">
                <ul class="p-0 m-0">
                    <li class="d-flex mb-4 pb-1">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-success"><i class="bx bx-package"></i></span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Indomie Goreng</h6>
                                <small class="text-muted">BRG-001</small>
                            </div>
                            <div class="user-progress text-end">
                                <h6 class="mb-0 text-success">150 pcs</h6>
                                <small class="text-muted">Rp 450.000</small>
                            </div>
                        </div>
                    </li>
                    <li class="d-flex mb-4 pb-1">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-package"></i></span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Aqua Botol 600ml</h6>
                                <small class="text-muted">BRG-002</small>
                            </div>
                            <div class="user-progress text-end">
                                <h6 class="mb-0 text-primary">120 pcs</h6>
                                <small class="text-muted">Rp 420.000</small>
                            </div>
                        </div>
                    </li>
                    <li class="d-flex mb-4 pb-1">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-info"><i class="bx bx-package"></i></span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Teh Pucuk Harum</h6>
                                <small class="text-muted">BRG-005</small>
                            </div>
                            <div class="user-progress text-end">
                                <h6 class="mb-0 text-info">95 pcs</h6>
                                <small class="text-muted">Rp 380.000</small>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Worst 5 Products -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0 text-danger"><i class="bx bx-trending-down me-1"></i> Produk Kurang Diminati (Slow Moving)</h5>
            </div>
            <div class="card-body mt-3">
                <ul class="p-0 m-0">
                    <li class="d-flex mb-4 pb-1">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-time-five"></i></span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Sarden ABC</h6>
                                <small class="text-muted">BRG-012</small>
                            </div>
                            <div class="user-progress text-end">
                                <h6 class="mb-0 text-danger">2 pcs</h6>
                                <small class="text-muted">Sisa Stok: 45</small>
                            </div>
                        </div>
                    </li>
                    <li class="d-flex mb-4 pb-1">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time-five"></i></span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Kecap Bango Pouch Kecil</h6>
                                <small class="text-muted">BRG-015</small>
                            </div>
                            <div class="user-progress text-end">
                                <h6 class="mb-0 text-warning">5 pcs</h6>
                                <small class="text-muted">Sisa Stok: 20</small>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Rincian Penjualan per Produk</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Kode Barang</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Terjual (Qty)</th>
                    <th>Harga Jual Rata-rata</th>
                    <th>Total Pendapatan (Omzet)</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @php
                $products = [
                    ['code' => 'BRG-001', 'name' => 'Indomie Goreng', 'cat' => 'Sembako', 'qty' => 150, 'price' => 3000, 'total' => 450000],
                    ['code' => 'BRG-002', 'name' => 'Aqua Botol 600ml', 'cat' => 'Minuman', 'qty' => 120, 'price' => 3500, 'total' => 420000],
                    ['code' => 'BRG-005', 'name' => 'Teh Pucuk Harum', 'cat' => 'Minuman', 'qty' => 95, 'price' => 4000, 'total' => 380000],
                    ['code' => 'BRG-003', 'name' => 'Beras Sania 5kg', 'cat' => 'Sembako', 'qty' => 15, 'price' => 72000, 'total' => 1080000],
                ];
                $total_omzet = array_sum(array_column($products, 'total'));
                @endphp

                @foreach($products as $p)
                <tr>
                    <td><span class="fw-medium">{{ $p['code'] }}</span></td>
                    <td>{{ $p['name'] }}</td>
                    <td>{{ $p['cat'] }}</td>
                    <td class="fw-bold text-success">{{ $p['qty'] }} pcs</td>
                    <td>Rp {{ number_format($p['price'], 0, ',', '.') }}</td>
                    <td class="fw-bold">Rp {{ number_format($p['total'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="5" class="text-end">TOTAL OMZET PRODUK (Tampil):</td>
                    <td class="text-primary">Rp {{ number_format($total_omzet, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@section('page-style')
<style>
    @media print {
        .layout-menu, .layout-navbar, .d-print-none, .btn { display: none !important; }
        .content-wrapper { padding: 0 !important; margin: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    }
</style>
@endsection
