@extends('layouts.app')

@section('title', 'Kategori Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-1">
            <span class="text-muted fw-light">Produk /</span> Kategori
        </h4>
        <p class="text-muted mb-0">Kelola kategori barang untuk pengelompokan produk toko.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahKategoriModal">
        <i class="bx bx-plus me-1"></i> Tambah Kategori
    </button>
</div>

{{-- Flash Messages --}}
@if (session('success'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
    <i class="bx bx-check-circle me-2 fs-4"></i>
    <div>{{ session('success') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
    <i class="bx bx-error-circle me-2 fs-4"></i>
    <div>{{ session('error') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="fw-bold mb-1"><i class="bx bx-error-circle me-1"></i> Terjadi kesalahan validasi:</div>
    <ul class="mb-0 ps-3">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Metric Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="avatar avatar-md me-3 bg-label-primary rounded p-2 d-flex align-items-center justify-content-center">
                    <i class="bx bx-category fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Total Kategori</span>
                    <h4 class="card-title mb-0 fw-bold">{{ $stats['total'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="avatar avatar-md me-3 bg-label-success rounded p-2 d-flex align-items-center justify-content-center">
                    <i class="bx bx-check-circle fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Kategori Terpakai</span>
                    <h4 class="card-title mb-0 fw-bold">{{ $stats['used'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="avatar avatar-md me-3 bg-label-warning rounded p-2 d-flex align-items-center justify-content-center">
                    <i class="bx bx-info-circle fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Belum Terpakai</span>
                    <h4 class="card-title mb-0 fw-bold">{{ $stats['unused'] }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter & Search Card --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('categories.index') }}" class="row g-3 align-items-end">
            <div class="col-md-8 col-12">
                <label class="form-label fw-semibold">Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama kategori atau keterangan..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4 col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bx bx-filter-alt me-1"></i> Cari
                </button>
                @if (request()->hasAny(['search']))
                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                    <i class="bx bx-reset"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Main Categories Table Card --}}
<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold">Daftar Kategori</h5>
        <span class="badge bg-label-primary">{{ $categories->total() }} Kategori Terdaftar</span>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Nama Kategori</th>
                    <th>Slug</th>
                    <th>Keterangan</th>
                    <th>Jumlah Produk</th>
                    <th class="text-center" style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($categories as $category)
                <tr>
                    <td class="text-muted">{{ ($categories->currentPage() - 1) * $categories->perPage() + $loop->iteration }}</td>
                    <td><span class="fw-medium text-heading">{{ $category->name }}</span></td>
                    <td><code class="small">{{ $category->slug }}</code></td>
                    <td>{{ $category->description ?? '-' }}</td>
                    <td>
                        @if($category->products_count > 0)
                            <span class="badge bg-label-success">{{ $category->products_count }} produk</span>
                        @else
                            <span class="badge bg-label-secondary">0 produk</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded fs-5 text-muted"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                {{-- Edit Button --}}
                                <a class="dropdown-item" href="javascript:void(0);"
                                   data-bs-toggle="modal"
                                   data-bs-target="#editKategoriModal"
                                   onclick="populateEditModal('{{ $category->id }}', '{{ addslashes($category->name) }}', '{{ addslashes($category->description) }}')">
                                    <i class="bx bx-edit-alt me-2 text-primary"></i> Edit
                                </a>

                                <div class="dropdown-divider"></div>

                                {{-- Hapus Button --}}
                                @if($category->products_count > 0)
                                    <a class="dropdown-item text-muted" href="javascript:void(0);"
                                       onclick="alert('Kategori ini tidak dapat dihapus karena masih digunakan oleh {{ $category->products_count }} produk.')">
                                        <i class="bx bx-lock-alt me-2"></i> Hapus (Terkunci)
                                    </a>
                                @else
                                    <button type="button" class="dropdown-item text-danger"
                                            onclick="confirmDeleteCategory('{{ $category->id }}', '{{ addslashes($category->name) }}')">
                                        <i class="bx bx-trash me-2"></i> Hapus
                                    </button>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <i class="bx bx-category fs-1 text-muted mb-2"></i>
                            <h6 class="fw-bold mb-1">Tidak ada data kategori</h6>
                            <p class="small mb-3">Belum ada kategori yang sesuai dengan kata kunci pencarian.</p>
                            @if(request('search'))
                                <a href="{{ route('categories.index') }}" class="btn btn-sm btn-outline-primary">Reset Pencarian</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
    <div class="card-footer border-top d-flex justify-content-between align-items-center py-3">
        <small class="text-muted">Menampilkan {{ $categories->firstItem() ?? 0 }} - {{ $categories->lastItem() ?? 0 }} dari {{ $categories->total() }} kategori</small>
        <div>
            {{ $categories->links() }}
        </div>
    </div>
    @endif
</div>

{{-- Form Penghapusan Tersembunyi --}}
<form id="deleteCategoryForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="tambahKategoriModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold"><i class="bx bx-plus-circle me-1 text-primary"></i> Tambah Kategori Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="cth. Sembako" required value="{{ old('name') }}">
                            <small class="text-muted">Slug akan dibuat otomatis dari nama kategori.</small>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">Keterangan</label>
                            <input type="text" id="description" name="description" class="form-control" placeholder="cth. Bahan kebutuhan pokok sehari-hari" value="{{ old('description') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kategori -->
<div class="modal fade" id="editKategoriModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold"><i class="bx bx-edit-alt me-1 text-primary"></i> Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="edit_name" class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" id="edit_name" name="name" class="form-control" required>
                            <small class="text-muted">Slug akan diperbarui otomatis jika nama berubah.</small>
                        </div>
                        <div class="col-12">
                            <label for="edit_description" class="form-label fw-semibold">Keterangan</label>
                            <input type="text" id="edit_description" name="description" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    function populateEditModal(id, name, description) {
        document.getElementById('editCategoryForm').action = '/categories/' + id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = description || '';
    }

    function confirmDeleteCategory(id, name) {
        if (confirm('Apakah Anda yakin ingin menghapus kategori "' + name + '"? Tindakan ini tidak dapat dibatalkan.')) {
            const form = document.getElementById('deleteCategoryForm');
            form.action = '/categories/' + id;
            form.submit();
        }
    }
</script>
@endsection
