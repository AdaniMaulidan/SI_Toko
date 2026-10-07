@extends('layouts.app')

@section('title', 'Import & Export Data')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Data Master /</span> Import & Export
    </h4>
</div>

<div class="row">
    <!-- Kolom Export -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <h5 class="card-header border-bottom bg-label-info text-info"><i class="bx bx-export me-2"></i> Export Data (Unduh)</h5>
            <div class="card-body mt-4">
                <p class="text-muted mb-4">
                    Unduh data master toko ke dalam format Excel (.xlsx) atau CSV untuk keperluan pelaporan eksternal atau diolah di aplikasi lain.
                </p>
                
                <form action="#" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Data yang Diexport</label>
                        <select class="form-select form-select-lg" name="export_type">
                            <option value="products">Data Produk & Stok</option>
                            <option value="customers">Data Pelanggan (Member)</option>
                            <option value="suppliers">Data Supplier</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Format File</label>
                        <select class="form-select" name="export_format">
                            <option value="xlsx">Excel (.xlsx)</option>
                            <option value="csv">CSV (.csv)</option>
                        </select>
                    </div>
                    
                    <button type="button" class="btn btn-info w-100">
                        <i class="bx bx-download me-2"></i> Download File Data
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Import -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <h5 class="card-header border-bottom bg-label-success text-success"><i class="bx bx-import me-2"></i> Import Data (Unggah)</h5>
            <div class="card-body mt-4">
                <p class="text-muted mb-4">
                    Masukkan banyak data sekaligus menggunakan template Excel. Sangat berguna untuk migrasi data awal atau menambah produk dalam jumlah besar.
                </p>
                
                <form action="#" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Jenis Data</label>
                        <select class="form-select form-select-lg" name="import_type">
                            <option value="products">Data Produk</option>
                            <option value="customers">Data Pelanggan</option>
                        </select>
                        <div class="mt-2 text-end">
                            <a href="#" class="text-success small fw-medium"><i class="bx bx-download me-1"></i> Download Template Excel</a>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Unggah File (Excel)</label>
                        <input class="form-control" type="file" name="import_file" accept=".xlsx, .xls, .csv" required>
                    </div>
                    
                    <div class="alert alert-warning py-2 mb-4">
                        <small><strong>Info:</strong> Pastikan format kolom sama persis dengan template yang disediakan agar proses import tidak gagal.</small>
                    </div>
                    
                    <button type="button" class="btn btn-success w-100">
                        <i class="bx bx-upload me-2"></i> Proses Import Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
