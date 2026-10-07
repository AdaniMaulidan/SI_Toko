@extends('layouts.app')

@section('title', 'Manajemen Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Produk /</span> Daftar Produk
    </h4>
    <a href="{{ route('products.create') }}" class="btn btn-primary">
        <i class="bx bx-plus me-1"></i> Tambah Produk
    </a>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row gx-3 gy-2 align-items-center">
            <div class="col-md-4">
                <label class="form-label" for="filter-kategori">Kategori</label>
                <select id="filter-kategori" class="form-select">
                    <option value="">Semua Kategori</option>
                    <option value="sembako">Sembako</option>
                    <option value="minuman">Minuman</option>
                    <option value="snack">Snack</option>
                    <option value="rokok">Rokok</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="filter-status">Status Stok</label>
                <select id="filter-status" class="form-select">
                    <option value="">Semua</option>
                    <option value="aman">Aman</option>
                    <option value="menipis">Menipis</option>
                    <option value="habis">Habis</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="search-produk">Cari</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" id="search-produk" class="form-control" placeholder="Nama, kode, atau barcode...">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Produk Info</th>
                    <th>Kategori</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @php
                $products = [
                    ['id' => 1, 'name' => 'Indomie Goreng', 'code' => 'BRG-001', 'category' => 'Sembako', 'buy' => 2500, 'sell' => 3000, 'stock' => 150, 'status' => 'Aktif'],
                    ['id' => 2, 'name' => 'Aqua Botol 600ml', 'code' => 'BRG-002', 'category' => 'Minuman', 'buy' => 2800, 'sell' => 3500, 'stock' => 85, 'status' => 'Aktif'],
                    ['id' => 3, 'name' => 'Beras Sania 5kg', 'code' => 'BRG-003', 'category' => 'Sembako', 'buy' => 68000, 'sell' => 72000, 'stock' => 5, 'status' => 'Aktif'], // Menipis
                    ['id' => 4, 'name' => 'Chitato Sapi Panggang', 'code' => 'BRG-004', 'category' => 'Snack', 'buy' => 9000, 'sell' => 11000, 'stock' => 0, 'status' => 'Aktif'], // Habis
                    ['id' => 5, 'name' => 'Kopi Kapal Api Mix', 'code' => 'BRG-005', 'category' => 'Minuman', 'buy' => 12000, 'sell' => 14000, 'stock' => 50, 'status' => 'Nonaktif'],
                ];
                @endphp

                @foreach($products as $prod)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3 bg-label-secondary">
                                <span class="avatar-initial rounded"><i class="bx bx-package"></i></span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-medium text-heading">{{ $prod['name'] }}</span>
                                <small class="text-muted">{{ $prod['code'] }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $prod['category'] }}</td>
                    <td>Rp {{ number_format($prod['buy'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($prod['sell'], 0, ',', '.') }}</td>
                    <td>
                        @if($prod['stock'] == 0)
                            <span class="badge bg-label-danger">{{ $prod['stock'] }}</span>
                        @elseif($prod['stock'] <= 5)
                            <span class="badge bg-label-warning">{{ $prod['stock'] }}</span>
                        @else
                            {{ $prod['stock'] }}
                        @endif
                    </td>
                    <td>
                        @if($prod['status'] == 'Aktif')
                            <span class="badge bg-label-success">Aktif</span>
                        @else
                            <span class="badge bg-label-secondary">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('products.show', $prod['id']) }}"><i class="bx bx-show me-1"></i> Detail</a>
                                <a class="dropdown-item" href="{{ route('products.edit', $prod['id']) }}"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                <a class="dropdown-item text-danger" href="javascript:void(0);"><i class="bx bx-trash me-1"></i> Hapus</a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="card-footer d-flex justify-content-end pb-0">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <li class="page-item prev disabled"><a class="page-link" href="javascript:void(0);"><i class="tf-icon bx bx-chevron-left"></i></a></li>
                <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                <li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
                <li class="page-item next"><a class="page-link" href="javascript:void(0);"><i class="tf-icon bx bx-chevron-right"></i></a></li>
            </ul>
        </nav>
    </div>
</div>
@endsection
