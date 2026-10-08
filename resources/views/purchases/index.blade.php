@extends('layouts.app')

@section('title', 'Riwayat Pembelian Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Pembelian /</span> Riwayat Pembelian
    </h4>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">
        <i class="bx bx-plus me-1"></i> Catat Pembelian Baru
    </a>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row gx-3 gy-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="filter-tanggal">Rentang Tanggal</label>
                <select id="filter-tanggal" class="form-select">
                    <option value="hari_ini">Hari Ini</option>
                    <option value="minggu_ini">Minggu Ini</option>
                    <option value="bulan_ini" selected>Bulan Ini</option>
                    <option value="tahun_ini">Tahun Ini</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="filter-supplier">Supplier</label>
                <select id="filter-supplier" class="form-select">
                    <option value="">Semua Supplier</option>
                    <option value="1">PT. Indofood Sukses Makmur</option>
                    <option value="2">PT. Tirta Investama (Aqua)</option>
                    <option value="3">Grosir Sembako Jaya</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="filter-status">Status Pembayaran</label>
                <select id="filter-status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="Lunas">Lunas</option>
                    <option value="Hutang">Hutang / Tempo</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="search-po">Cari No Pembelian (PO)</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" id="search-po" class="form-control" placeholder="Contoh: PO-...">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Daftar Pembelian (Barang Masuk)</h5>
    </div>
    
    <div class="table-responsive text-nowrap">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No. PO</th>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th>Total Item</th>
                    <th>Total Biaya</th>
                    <th>Status Bayar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @php
                $purchases = [
                    ['no' => 'PO-202610-005', 'date' => '06 Okt 2026', 'supplier' => 'PT. Indofood Sukses Makmur', 'items' => 120, 'total' => 2550000, 'status' => 'Lunas'],
                    ['no' => 'PO-202610-004', 'date' => '05 Okt 2026', 'supplier' => 'PT. Tirta Investama (Aqua)', 'items' => 50, 'total' => 1200000, 'status' => 'Lunas'],
                    ['no' => 'PO-202610-003', 'date' => '02 Okt 2026', 'supplier' => 'Grosir Sembako Jaya', 'items' => 25, 'total' => 3800000, 'status' => 'Hutang'],
                    ['no' => 'PO-202609-012', 'date' => '28 Sep 2026', 'supplier' => 'PT. Indofood Sukses Makmur', 'items' => 80, 'total' => 1750000, 'status' => 'Lunas'],
                ];
                @endphp

                @foreach($purchases as $po)
                <tr>
                    <td><span class="fw-medium text-primary">{{ $po['no'] }}</span></td>
                    <td>{{ $po['date'] }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3 bg-label-secondary">
                                <span class="avatar-initial rounded"><i class="bx bx-buildings"></i></span>
                            </div>
                            <span class="fw-medium">{{ $po['supplier'] }}</span>
                        </div>
                    </td>
                    <td>{{ $po['items'] }} pcs</td>
                    <td class="fw-bold">Rp {{ number_format($po['total'], 0, ',', '.') }}</td>
                    <td>
                        @if($po['status'] == 'Lunas')
                            <span class="badge bg-label-success">Lunas</span>
                        @else
                            <span class="badge bg-label-warning">Tempo / Hutang</span>
                        @endif
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="javascript:void(0);"><i class="icon-base bx bx-show me-1"></i> Detail Pembelian</a>
                                @if($po['status'] != 'Lunas')
                                <a class="dropdown-item text-success" href="javascript:void(0);"><i class="icon-base bx bx-money me-1"></i> Bayar Hutang</a>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="card-footer d-flex justify-content-end pb-0 border-top">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <li class="page-item prev disabled"><a class="page-link" href="javascript:void(0);"><i class="tf-icon bx bx-chevron-left"></i></a></li>
                <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                <li class="page-item next disabled"><a class="page-link" href="javascript:void(0);"><i class="tf-icon bx bx-chevron-right"></i></a></li>
            </ul>
        </nav>
    </div>
</div>
@endsection
