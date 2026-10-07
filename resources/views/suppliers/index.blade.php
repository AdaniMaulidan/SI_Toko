@extends('layouts.app')

@section('title', 'Manajemen Supplier')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Data Master /</span> Supplier
    </h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahSupplierModal">
        <i class="bx bx-plus me-1"></i> Tambah Supplier
    </button>
</div>

<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Daftar Pemasok (Supplier)</h5>
    </div>
    <div class="card-body mt-4">
        <div class="row mb-3">
            <div class="col-md-4 offset-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" class="form-control" placeholder="Cari nama, telp, atau alamat...">
                </div>
            </div>
        </div>

        <div class="table-responsive text-nowrap border rounded">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Supplier</th>
                        <th>Kontak & Telp</th>
                        <th>Alamat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $suppliers = [
                        ['id' => 1, 'name' => 'PT. Indofood Sukses Makmur', 'contact' => 'Bpk. Budi', 'phone' => '0812-3456-7890', 'address' => 'Jl. Sudirman No. 45, Jakarta', 'status' => 'Aktif'],
                        ['id' => 2, 'name' => 'PT. Tirta Investama (Aqua)', 'contact' => 'Ibu Siti', 'phone' => '0857-1122-3344', 'address' => 'Kawasan Industri Pulo Gadung', 'status' => 'Aktif'],
                        ['id' => 3, 'name' => 'Grosir Sembako Jaya', 'contact' => 'Ko Aseng', 'phone' => '0819-9988-7766', 'address' => 'Pasar Induk Kramat Jati Blok A', 'status' => 'Aktif'],
                        ['id' => 4, 'name' => 'Distributor Unilever', 'contact' => 'Andi', 'phone' => '0811-2233-4455', 'address' => 'Jl. Merdeka Raya No. 12', 'status' => 'Nonaktif'],
                    ];
                    @endphp

                    @foreach($suppliers as $index => $sup)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3 bg-label-primary">
                                    <span class="avatar-initial rounded"><i class="bx bx-buildings"></i></span>
                                </div>
                                <span class="fw-medium text-heading">{{ $sup['name'] }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span><i class="bx bx-user me-1 text-muted"></i>{{ $sup['contact'] }}</span>
                                <small class="text-muted"><i class="bx bx-phone me-1"></i>{{ $sup['phone'] }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="d-inline-block text-truncate" style="max-width: 200px;" title="{{ $sup['address'] }}">
                                {{ $sup['address'] }}
                            </span>
                        </td>
                        <td>
                            @if($sup['status'] == 'Aktif')
                                <span class="badge bg-label-success">Aktif</span>
                            @else
                                <span class="badge bg-label-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-icon btn-text-secondary" data-bs-toggle="modal" data-bs-target="#editSupplierModal" 
                                onclick="populateEditModal('{{ $sup['name'] }}', '{{ $sup['contact'] }}', '{{ $sup['phone'] }}', '{{ $sup['address'] }}', '{{ $sup['status'] }}')">
                                <i class="icon-base bx bx-edit-alt text-info"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon btn-text-secondary" data-bs-toggle="modal" data-bs-target="#hapusSupplierModal">
                                <i class="icon-base bx bx-trash text-danger"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Supplier -->
<div class="modal fade" id="tambahSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">Tambah Supplier Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nama Perusahaan / Supplier <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-buildings"></i></span>
                                <input type="text" id="name" name="name" class="form-control" placeholder="Contoh: PT. Indofood" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="contact_name" class="form-label">Nama Kontak (PIC) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" id="contact_name" name="contact_name" class="form-control" placeholder="Nama orang yang bisa dihubungi" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                <input type="text" id="phone" name="phone" class="form-control" placeholder="0812-xxxx-xxxx" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="status" name="status" checked>
                                <label class="form-check-label" for="status">Aktif Beroperasi</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Alamat Lengkap</label>
                            <textarea id="address" name="address" class="form-control" rows="3" placeholder="Alamat lengkap supplier..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Supplier -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">Edit Data Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <!-- <input type="hidden" name="_method" value="PUT"> -->
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_name" class="form-label">Nama Perusahaan / Supplier <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-buildings"></i></span>
                                <input type="text" id="edit_name" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_contact_name" class="form-label">Nama Kontak (PIC) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" id="edit_contact_name" name="contact_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_phone" class="form-label">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                <input type="text" id="edit_phone" name="phone" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="edit_status" name="status">
                                <label class="form-check-label" for="edit_status">Aktif Beroperasi</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="edit_address" class="form-label">Alamat Lengkap</label>
                            <textarea id="edit_address" name="address" class="form-control" rows="3"></textarea>
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

<!-- Modal Hapus Supplier -->
<div class="modal fade" id="hapusSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="avatar avatar-xl bg-label-danger mx-auto mb-4">
                    <span class="avatar-initial rounded-circle"><i class="bx bx-trash fs-2"></i></span>
                </div>
                <h5 class="fw-bold">Hapus Supplier?</h5>
                <p class="text-muted mb-4">Apakah Anda yakin ingin menghapus supplier ini? Aksi ini tidak dapat dibatalkan.</p>
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
    function populateEditModal(name, contact, phone, address, status) {
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_contact_name').value = contact;
        document.getElementById('edit_phone').value = phone;
        document.getElementById('edit_address').value = address;
        document.getElementById('edit_status').checked = status === 'Aktif';
    }
</script>
@endsection
