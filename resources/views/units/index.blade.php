@extends('layouts.app')

@section('title', 'Satuan Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-1">
            <span class="text-muted fw-light">Produk /</span> Satuan
        </h4>
        <p class="text-muted mb-0">Kelola satuan barang untuk keperluan pencatatan produk toko.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahSatuanModal">
        <i class="bx bx-plus me-1"></i> Tambah Satuan
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
                    <i class="bx bx-cube fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Total Satuan</span>
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
                    <span class="d-block text-muted small fw-semibold">Satuan Terpakai</span>
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
        <form method="GET" action="{{ route('units.index') }}" class="row g-3 align-items-end">
            <div class="col-md-8 col-12">
                <label class="form-label fw-semibold">Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama satuan, singkatan, atau keterangan..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4 col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bx bx-filter-alt me-1"></i> Cari
                </button>
                @if (request()->hasAny(['search']))
                <a href="{{ route('units.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                    <i class="bx bx-reset"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Main Units Table Card --}}
<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold">Daftar Satuan</h5>
        <span class="badge bg-label-primary">{{ $units->total() }} Satuan Terdaftar</span>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Nama Satuan</th>
                    <th>Singkatan</th>
                    <th>Keterangan</th>
                    <th>Jumlah Produk</th>
                    <th class="text-center" style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($units as $unit)
                <tr>
                    <td class="text-muted">{{ ($units->currentPage() - 1) * $units->perPage() + $loop->iteration }}</td>
                    <td><span class="fw-medium text-heading">{{ $unit->name }}</span></td>
                    <td>
                        @if($unit->short_name)
                            <span class="badge bg-label-info">{{ $unit->short_name }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $unit->description ?? '-' }}</td>
                    <td>
                        @if($unit->products_count > 0)
                            <span class="badge bg-label-success">{{ $unit->products_count }} produk</span>
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
                                   data-bs-target="#editSatuanModal"
                                   onclick="populateEditModal('{{ $unit->id }}', '{{ addslashes($unit->name) }}', '{{ addslashes($unit->short_name) }}', '{{ addslashes($unit->description) }}')">
                                    <i class="bx bx-edit-alt me-2 text-primary"></i> Edit
                                </a>

                                <div class="dropdown-divider"></div>

                                {{-- Hapus Button --}}
                                @if($unit->products_count > 0)
                                    <a class="dropdown-item text-muted" href="javascript:void(0);"
                                       onclick="alert('Satuan ini tidak dapat dihapus karena masih digunakan oleh {{ $unit->products_count }} produk.')">
                                        <i class="bx bx-lock-alt me-2"></i> Hapus (Terkunci)
                                    </a>
                                @else
                                    <button type="button" class="dropdown-item text-danger"
                                            onclick="confirmDeleteUnit('{{ $unit->id }}', '{{ addslashes($unit->name) }}')">
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
                            <i class="bx bx-cube fs-1 text-muted mb-2"></i>
                            <h6 class="fw-bold mb-1">Tidak ada data satuan</h6>
                            <p class="small mb-3">Belum ada satuan yang sesuai dengan kata kunci pencarian.</p>
                            @if(request('search'))
                                <a href="{{ route('units.index') }}" class="btn btn-sm btn-outline-primary">Reset Pencarian</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($units->hasPages())
    <div class="card-footer border-top d-flex justify-content-between align-items-center py-3">
        <small class="text-muted">Menampilkan {{ $units->firstItem() ?? 0 }} - {{ $units->lastItem() ?? 0 }} dari {{ $units->total() }} satuan</small>
        <div>
            {{ $units->links() }}
        </div>
    </div>
    @endif
</div>

{{-- Form Penghapusan Tersembunyi --}}
<form id="deleteUnitForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Modal Tambah Satuan -->
<div class="modal fade" id="tambahSatuanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold"><i class="bx bx-plus-circle me-1 text-primary"></i> Tambah Satuan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('units.store') }}" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Nama Satuan <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="cth. Bungkus" required value="{{ old('name') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="short_name" class="form-label fw-semibold">Singkatan</label>
                            <input type="text" id="short_name" name="short_name" class="form-control" placeholder="cth. bks" value="{{ old('short_name') }}">
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">Keterangan</label>
                            <input type="text" id="description" name="description" class="form-control" placeholder="cth. Kemasan Bungkus" value="{{ old('description') }}">
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

<!-- Modal Edit Satuan -->
<div class="modal fade" id="editSatuanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold"><i class="bx bx-edit-alt me-1 text-primary"></i> Edit Satuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUnitForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_name" class="form-label fw-semibold">Nama Satuan <span class="text-danger">*</span></label>
                            <input type="text" id="edit_name" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_short_name" class="form-label fw-semibold">Singkatan</label>
                            <input type="text" id="edit_short_name" name="short_name" class="form-control">
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
    function populateEditModal(id, name, shortName, description) {
        document.getElementById('editUnitForm').action = '/units/' + id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_short_name').value = shortName || '';
        document.getElementById('edit_description').value = description || '';
    }

    function confirmDeleteUnit(id, name) {
        if (confirm('Apakah Anda yakin ingin menghapus satuan "' + name + '"? Tindakan ini tidak dapat dibatalkan.')) {
            const form = document.getElementById('deleteUnitForm');
            form.action = '/units/' + id;
            form.submit();
        }
    }
</script>
@endsection
