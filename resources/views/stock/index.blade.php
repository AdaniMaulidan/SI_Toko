@extends('layouts.app')

@section('title', 'Manajemen Stok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Stok /</span> Daftar Stok Barang
    </h4>
    <a href="{{ route('stock.adjustment') }}" class="btn btn-primary">
        <i class="bx bx-adjust me-1"></i> Penyesuaian Stok (Opname)
    </a>
</div>

<!-- Statistik Ringkas -->
<div class="row mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                    <div class="avatar me-3">
                        <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-package"></i></span>
                    </div>
                    <h4 class="mb-0">248</h4>
                </div>
                <h6 class="mb-0">Total Item Produk</h6>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                    <div class="avatar me-3">
                        <span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-circle"></i></span>
                    </div>
                    <h4 class="mb-0">241</h4>
                </div>
                <h6 class="mb-0">Stok Aman</h6>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card h-100">
            <div class="card-body border-bottom border-warning border-3">
                <div class="d-flex align-items-center mb-2 pb-1">
                    <div class="avatar me-3">
                        <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-error"></i></span>
                    </div>
                    <h4 class="mb-0 text-warning">5</h4>
                </div>
                <h6 class="mb-0">Stok Menipis</h6>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card h-100">
            <div class="card-body border-bottom border-danger border-3">
                <div class="d-flex align-items-center mb-2 pb-1">
                    <div class="avatar me-3">
                        <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-x-circle"></i></span>
                    </div>
                    <h4 class="mb-0 text-danger">2</h4>
                </div>
                <h6 class="mb-0">Stok Habis</h6>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Status Stok Produk</h5>
    </div>
    <div class="card-body mt-4">
        <div class="row gx-3 gy-2 align-items-center mb-3">
            <div class="col-md-3">
                <label class="form-label">Kategori</label>
                <select class="form-select">
                    <option value="">Semua Kategori</option>
                    <option value="1">Sembako</option>
                    <option value="2">Minuman</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status Stok</label>
                <select class="form-select">
                    <option value="">Semua Status</option>
                    <option value="aman">Aman</option>
                    <option value="menipis">Menipis</option>
                    <option value="habis">Habis</option>
                </select>
            </div>
            <div class="col-md-4 offset-md-2">
                <label class="form-label">Cari Produk</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" class="form-control" placeholder="Nama atau kode produk...">
                </div>
            </div>
        </div>

        <div class="table-responsive text-nowrap border rounded">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Min. Stok</th>
                        <th>Sisa Stok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $stocks = [
                        ['code' => 'BRG-001', 'name' => 'Indomie Goreng', 'category' => 'Sembako', 'min' => 10, 'qty' => 150, 'unit' => 'Bungkus', 'status' => 'Aman'],
                        ['code' => 'BRG-003', 'name' => 'Beras Sania 5kg', 'category' => 'Sembako', 'min' => 5, 'qty' => 5, 'unit' => 'Pcs', 'status' => 'Menipis'],
                        ['code' => 'BRG-008', 'name' => 'Minyak Bimoli 2L', 'category' => 'Sembako', 'min' => 10, 'qty' => 8, 'unit' => 'Pcs', 'status' => 'Menipis'],
                        ['code' => 'BRG-004', 'name' => 'Chitato Sapi Panggang', 'category' => 'Snack', 'min' => 15, 'qty' => 0, 'unit' => 'Bungkus', 'status' => 'Habis'],
                        ['code' => 'BRG-002', 'name' => 'Aqua Botol 600ml', 'category' => 'Minuman', 'min' => 20, 'qty' => 85, 'unit' => 'Botol', 'status' => 'Aman'],
                    ];
                    @endphp

                    @foreach($stocks as $stk)
                    <tr>
                        <td><span class="fw-medium">{{ $stk['code'] }}</span></td>
                        <td>{{ $stk['name'] }}</td>
                        <td>{{ $stk['category'] }}</td>
                        <td>{{ $stk['min'] }}</td>
                        <td><span class="fw-bold">{{ $stk['qty'] }}</span> <small class="text-muted">{{ $stk['unit'] }}</small></td>
                        <td>
                            @if($stk['status'] == 'Aman')
                                <span class="badge bg-label-success">Aman</span>
                            @elseif($stk['status'] == 'Menipis')
                                <span class="badge bg-label-warning">Menipis</span>
                            @else
                                <span class="badge bg-label-danger">Habis</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('products.show', 1) }}" class="btn btn-sm btn-outline-primary" title="Riwayat Stok">
                                <i class="bx bx-history me-1"></i> Riwayat
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
