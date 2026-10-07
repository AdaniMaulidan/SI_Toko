@extends('layouts.app')

@section('title', 'Satuan Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Produk /</span> Satuan
    </h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahSatuanModal">
        <i class="bx bx-plus me-1"></i> Tambah Satuan
    </button>
</div>

<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Daftar Satuan</h5>
    </div>
    <div class="card-body mt-4">
        <div class="row mb-3">
            <div class="col-md-4 offset-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" class="form-control" placeholder="Cari satuan...">
                </div>
            </div>
        </div>

        <div class="table-responsive text-nowrap border rounded">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Satuan</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $units = [
                        ['id' => 1, 'name' => 'Pcs', 'description' => 'Pieces / Biji'],
                        ['id' => 2, 'name' => 'Bungkus', 'description' => 'Kemasan Bungkus'],
                        ['id' => 3, 'name' => 'Botol', 'description' => 'Kemasan Botol'],
                        ['id' => 4, 'name' => 'Dus', 'description' => 'Karton / Dus'],
                        ['id' => 5, 'name' => 'Kg', 'description' => 'Kilogram'],
                        ['id' => 6, 'name' => 'Liter', 'description' => 'Liter'],
                        ['id' => 7, 'name' => 'Sachet', 'description' => 'Kemasan Sachet'],
                    ];
                    @endphp

                    @foreach($units as $index => $unit)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><span class="fw-medium text-heading">{{ $unit['name'] }}</span></td>
                        <td>{{ $unit['description'] }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-icon btn-text-secondary" data-bs-toggle="modal" data-bs-target="#editSatuanModal" onclick="populateEditModal('{{ $unit['name'] }}', '{{ $unit['description'] }}')">
                                <i class="icon-base bx bx-edit-alt"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon btn-text-danger" data-bs-toggle="modal" data-bs-target="#hapusSatuanModal">
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

<!-- Modal Tambah Satuan -->
<div class="modal fade" id="tambahSatuanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">Tambah Satuan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="name" class="form-label">Nama Satuan <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Contoh: Pcs" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <label for="description" class="form-label">Keterangan</label>
                            <input type="text" id="description" name="description" class="form-control" placeholder="Contoh: Pieces / Biji">
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

<!-- Modal Edit Satuan -->
<div class="modal fade" id="editSatuanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">Edit Satuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <!-- <input type="hidden" name="_method" value="PUT"> -->
                <div class="modal-body py-4">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="edit_name" class="form-label">Nama Satuan <span class="text-danger">*</span></label>
                            <input type="text" id="edit_name" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <label for="edit_description" class="form-label">Keterangan</label>
                            <input type="text" id="edit_description" name="description" class="form-control">
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

<!-- Modal Hapus Satuan -->
<div class="modal fade" id="hapusSatuanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="avatar avatar-xl bg-label-danger mx-auto mb-4">
                    <span class="avatar-initial rounded-circle"><i class="bx bx-trash fs-2"></i></span>
                </div>
                <h5 class="fw-bold">Hapus Satuan?</h5>
                <p class="text-muted mb-4">Apakah Anda yakin ingin menghapus satuan ini? Aksi ini tidak dapat dibatalkan.</p>
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
    function populateEditModal(name, description) {
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = description;
    }
</script>
@endsection
