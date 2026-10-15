@extends('layouts.app')

@section('title', 'Import & Export Data Master')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-1">
            <span class="text-muted fw-light">Data Master /</span> Import & Export Data
        </h4>
        <p class="text-muted mb-0 small">Unduh data master toko ke Excel/CSV atau impor data massal dari file spreadsheet secara cepat.</p>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert">
    <i class="bx bx-check-circle fs-4 me-2"></i>
    <div class="flex-grow-1">{{ session('success') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert">
    <i class="bx bx-error-circle fs-4 me-2"></i>
    <div class="flex-grow-1">{{ session('error') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
    <div class="d-flex align-items-center mb-1">
        <i class="bx bx-error fs-4 me-2"></i>
        <strong>Terdapat kesalahan pada formulir:</strong>
    </div>
    <ul class="mb-0 ps-4 small">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Summary Result Banner setelah Import --}}
@if(session('import_summary'))
@php $summary = session('import_summary'); @endphp
<div class="card mb-4 border-primary">
    <div class="card-body p-3">
        <h6 class="card-title fw-bold text-primary mb-2">
            <i class="bx bx-file-find me-1"></i> Rincian Hasil Proses Import:
        </h6>
        <div class="d-flex flex-wrap gap-2 mb-2">
            <span class="badge bg-success p-2">
                <i class="bx bx-plus-circle me-1"></i> {{ $summary['imported'] ?? 0 }} Data Baru Ditambahkan
            </span>
            @if(isset($summary['updated']) && $summary['updated'] > 0)
            <span class="badge bg-info p-2">
                <i class="bx bx-refresh me-1"></i> {{ $summary['updated'] }} Data Diperbarui
            </span>
            @endif
            @if(isset($summary['skipped']) && $summary['skipped'] > 0)
            <span class="badge bg-warning p-2 text-dark">
                <i class="bx bx-skip-next-circle me-1"></i> {{ $summary['skipped'] }} Data Dilewati
            </span>
            @endif
        </div>
        @if(!empty($summary['errors']))
        <div class="alert alert-light-warning py-2 px-3 mt-2 mb-0 border border-warning rounded">
            <small class="fw-bold d-block mb-1 text-warning">Catatan / Peringatan:</small>
            <ul class="mb-0 ps-3 small text-muted">
                @foreach(array_slice($summary['errors'], 0, 5) as $errLine)
                    <li>{{ $errLine }}</li>
                @endforeach
                @if(count($summary['errors']) > 5)
                    <li>...dan {{ count($summary['errors']) - 5 }} catatan lainnya.</li>
                @endif
            </ul>
        </div>
        @endif
    </div>
</div>
@endif

{{-- Metric Info Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-primary rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-package fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Master Produk</span>
                    <h4 class="card-title mb-0 fw-bold">{{ number_format($stats['total_products']) }} <small class="text-muted fs-6">item</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-info rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-user-circle fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Master Pelanggan</span>
                    <h4 class="card-title mb-0 fw-bold">{{ number_format($stats['total_customers']) }} <small class="text-muted fs-6">orang</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-success rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-truck fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Master Supplier</span>
                    <h4 class="card-title mb-0 fw-bold">{{ number_format($stats['total_suppliers']) }} <small class="text-muted fs-6">rekanan</small></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Kolom Export (Unduh Data Master) -->
    <div class="col-md-6 mb-4">
        <div class="card h-100 shadow-sm">
            <h5 class="card-header border-bottom bg-label-info text-info d-flex align-items-center">
                <i class="bx bx-export me-2 fs-4"></i> Export Data Master (Unduh)
            </h5>
            <div class="card-body mt-4">
                <p class="text-muted mb-4">
                    Unduh salinan data master toko ke dalam format <strong>Excel (.xlsx)</strong> atau <strong>CSV (.csv)</strong> untuk keperluan arsip, laporan, atau olah data eksternal.
                </p>
                
                <form action="{{ route('import_export.export') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Data yang Diekspor <span class="text-danger">*</span></label>
                        <select class="form-select form-select-lg" name="export_type" required>
                            <option value="products" selected>📦 Data Produk & Stok Gudang</option>
                            <option value="customers">👥 Data Pelanggan (Member & Piutang)</option>
                            <option value="suppliers">🚚 Data Supplier (Pemasok Barang)</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilihan Format File <span class="text-danger">*</span></label>
                        <select class="form-select" name="export_format" required>
                            <option value="xlsx" selected>Microsoft Excel (.xlsx) — Disarankan</option>
                            <option value="csv">Comma Separated Values (.csv)</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-info w-100 py-2 fw-semibold">
                        <i class="bx bx-download me-2"></i> Download File Data
                    </button>
                </form>

                <div class="mt-4 p-3 rounded bg-light border">
                    <small class="text-muted d-block">
                        <i class="bx bx-info-circle text-info me-1"></i>
                        File Excel yang diunduh menyertakan penataan tabel rapi, ukuran kolom otomatis, dan format angka sesuai standar sistem toko.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Import (Unggah Data Master) -->
    <div class="col-md-6 mb-4">
        <div class="card h-100 shadow-sm">
            <h5 class="card-header border-bottom bg-label-success text-success d-flex align-items-center">
                <i class="bx bx-import me-2 fs-4"></i> Import Data Master (Unggah)
            </h5>
            <div class="card-body mt-4">
                <p class="text-muted mb-4">
                    Tambahkan banyak data sekaligus secara instan menggunakan template spreadsheet. Sangat ideal untuk migrasi data awal produk atau pelanggan baru.
                </p>
                
                <form action="{{ route('import_export.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold mb-0">Pilih Jenis Data <span class="text-danger">*</span></label>
                            <a id="btnDownloadTemplate" href="{{ route('import_export.template', ['type' => 'products']) }}" class="text-success small fw-semibold text-decoration-none">
                                <i class="bx bx-download me-1"></i> Download Template Excel
                            </a>
                        </div>
                        <select class="form-select form-select-lg" name="import_type" id="selectImportType" required onchange="updateTemplateLink(this.value)">
                            <option value="products" selected>📦 Data Produk & Stok</option>
                            <option value="customers">👥 Data Pelanggan</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Unggah Berkas File (Excel / CSV) <span class="text-danger">*</span></label>
                        <input class="form-control" type="file" name="import_file" accept=".xlsx, .xls, .csv" required>
                        <small class="text-muted">Mendukung format: <code>.xlsx</code>, <code>.xls</code>, atau <code>.csv</code> (Maks. 10 MB)</small>
                    </div>

                    <div class="form-check mb-4" id="updateExistingContainer">
                        <input class="form-check-input" type="checkbox" name="update_existing" value="1" id="updateExistingCheck" checked>
                        <label class="form-check-label small" for="updateExistingCheck">
                            Perbarui data (update) jika barcode atau nama produk sudah ada sebelumnya
                        </label>
                    </div>
                    
                    <div class="alert alert-warning py-2 mb-4 d-flex align-items-start">
                        <i class="bx bx-bulb fs-4 me-2 text-warning flex-shrink-0"></i>
                        <small>
                            <strong>Tips Penting:</strong> Gunakan tombol <strong>Download Template Excel</strong> di atas sebagai acuan nama kolom agar proses import berjalan sukses tanpa kegagalan format.
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
                        <i class="bx bx-upload me-2"></i> Proses Import Data Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Panduan Kolom Template Import --}}
<div class="card shadow-sm mb-4">
    <div class="card-header border-bottom py-3">
        <h5 class="card-title mb-0">
            <i class="bx bx-book-open me-2 text-primary"></i> Panduan Format Kolom Template Import
        </h5>
    </div>
    <div class="card-body pt-3">
        <div class="row">
            <div class="col-md-6 mb-3 mb-md-0 border-end">
                <h6 class="fw-bold text-primary mb-2"><i class="bx bx-package me-1"></i> Kolom Import Produk</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Kolom</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>Barcode</code></td>
                                <td><small class="text-muted">Opsional. Jika kosong, sistem otomatis membuatkan barcode unik.</small></td>
                            </tr>
                            <tr>
                                <td><code>Nama Produk</code></td>
                                <td><small class="text-danger fw-bold">Wajib diisi.</small> <small class="text-muted">Nama barang dagangan.</small></td>
                            </tr>
                            <tr>
                                <td><code>Kategori</code></td>
                                <td><small class="text-muted">Nama kategori. Otomatis dibuatkan baru jika belum ada di database.</small></td>
                            </tr>
                            <tr>
                                <td><code>Satuan</code></td>
                                <td><small class="text-muted">Nama/simbol satuan (contoh: Pcs, Box, Kg). Default: Pcs.</small></td>
                            </tr>
                            <tr>
                                <td><code>Harga Modal</code></td>
                                <td><small class="text-muted">Harga beli (HPP) dalam format angka.</small></td>
                            </tr>
                            <tr>
                                <td><code>Harga Jual</code></td>
                                <td><small class="text-danger fw-bold">Wajib diisi.</small> <small class="text-muted">Harga jual ke konsumen.</small></td>
                            </tr>
                            <tr>
                                <td><code>Stok Awal</code></td>
                                <td><small class="text-muted">Jumlah kuantitas stok barang saat ini.</small></td>
                            </tr>
                            <tr>
                                <td><code>Stok Minimum</code></td>
                                <td><small class="text-muted">Batas peringatan stok menipis (default: 5).</small></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-6">
                <h6 class="fw-bold text-success mb-2"><i class="bx bx-user-circle me-1"></i> Kolom Import Pelanggan</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Kolom</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>Nama Pelanggan</code></td>
                                <td><small class="text-danger fw-bold">Wajib diisi.</small> <small class="text-muted">Nama lengkap member / pelanggan.</small></td>
                            </tr>
                            <tr>
                                <td><code>No WhatsApp / Telp</code></td>
                                <td><small class="text-muted">Nomor kontak HP/WhatsApp aktif pelanggan.</small></td>
                            </tr>
                            <tr>
                                <td><code>Email</code></td>
                                <td><small class="text-muted">Opsional. Alamat surel pelanggan.</small></td>
                            </tr>
                            <tr>
                                <td><code>Alamat</code></td>
                                <td><small class="text-muted">Opsional. Alamat tempat tinggal / toko pelanggan.</small></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    function updateTemplateLink(type) {
        const btn = document.getElementById('btnDownloadTemplate');
        const updateCheckContainer = document.getElementById('updateExistingContainer');
        const baseUrl = "{{ route('import_export.template') }}";
        
        btn.href = `${baseUrl}?type=${type}`;
        
        if (type === 'customers') {
            btn.innerHTML = '<i class="bx bx-download me-1"></i> Download Template Excel (Pelanggan)';
            updateCheckContainer.style.display = 'none';
        } else {
            btn.innerHTML = '<i class="bx bx-download me-1"></i> Download Template Excel (Produk)';
            updateCheckContainer.style.display = 'block';
        }
    }
</script>
@endsection
