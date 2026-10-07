@extends('layouts.app')

@section('title', 'Kategori Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Produk /</span> Kategori
    </h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahKategoriModal">
        <i class="bx bx-plus me-1"></i> Tambah Kategori
    </button>
</div>

<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Daftar Kategori</h5>
    </div>
    <div class="card-body mt-4">
        <div class="row mb-3">
            <div class="col-md-4 offset-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" class="form-control" placeholder="Cari kategori...">
                </div>
            </div>
        </div>

        <div class="table-responsive text-nowrap border rounded">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah Produk</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $categories = [
                        ['id' => 1, 'name' => 'Sembako', 'count' => 42, 'status' => 'Aktif'],
                        ['id' => 2, 'name' => 'Minuman', 'count' => 28, 'status' => 'Aktif'],
                        ['id' => 3, 'name' => 'Snack', 'count' => 56, 'status' => 'Aktif'],
                        ['id' => 4, 'name' => 'Rokok', 'count' => 15, 'status' => 'Aktif'],
                        ['id' => 5, 'name' => 'Sabun & Sampo', 'count' => 20, 'status' => 'Aktif'],
                        ['id' => 6, 'name' => 'Alat Rumah Tangga', 'count' => 8, 'status' => 'Nonaktif'],
                    ];
                    @endphp

                    @foreach($categories as $index => $cat)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><span class="fw-medium text-heading">{{ $cat['name'] }}</span></td>
                        <td>{{ $cat['count'] }} produk</td>
                        <td>
                            @if($cat['status'] == 'Aktif')
                                <span class="badge bg-label-success">Aktif</span>
                            @else
                                <span class="badge bg-label-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-icon btn-text-secondary" data-bs-toggle="modal" data-bs-target="#editKategoriModal" onclick="populateEditModal('{{ $cat['name'] }}', '{{ $cat['status'] }}')">
                                <i class="icon-base bx bx-edit-alt"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon btn-text-danger" data-bs-toggle="modal" data-bs-target="#hapusKategoriModal">
                                <i class="icon-base bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="tambahKategoriModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">Tambah Kategori Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Contoh: Alat Tulis" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="status" name="status" checked>
                                <label class="form-check-label" for="status">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
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
                <h5 class="modal-title fw-bold">Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <!-- <input type="hidden" name="_method" value="PUT"> -->
                <div class="modal-body py-4">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="edit_name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" id="edit_name" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="edit_status" name="status">
                                <label class="form-check-label" for="edit_status">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Kategori -->
<div class="modal fade" id="hapusKategoriModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="avatar avatar-xl bg-label-danger mx-auto mb-4">
                    <span class="avatar-initial rounded-circle"><i class="bx bx-trash fs-2"></i></span>
                </div>
                <h5 class="fw-bold">Hapus Kategori?</h5>
                <p class="text-muted mb-4">Apakah Anda yakin ingin menghapus kategori ini? Aksi ini tidak dapat dibatalkan.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <form action="#" method="POST">
                        @csrf
                        <!-- <input type="hidden" name="_method" value="DELETE"> -->
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    function populateEditModal(name, status) {
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_status').checked = status === 'Aktif';
    }
</script>
@endsection
