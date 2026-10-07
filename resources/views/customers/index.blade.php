@extends('layouts.app')

@section('title', 'Manajemen Pelanggan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Pelanggan /</span> Daftar Pelanggan
    </h4>
    <div>
        <a href="{{ route('customers.debt') }}" class="btn btn-outline-danger me-2">
            <i class="bx bx-wallet me-1"></i> Data Hutang
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahPelangganModal">
            <i class="bx bx-plus me-1"></i> Tambah Pelanggan
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Daftar Pelanggan Member</h5>
    </div>
    <div class="card-body mt-4">
        <div class="row mb-3">
            <div class="col-md-4 offset-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" class="form-control" placeholder="Cari nama atau telepon...">
                </div>
            </div>
        </div>

        <div class="table-responsive text-nowrap border rounded">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID Pelanggan</th>
                        <th>Nama Pelanggan</th>
                        <th>Telepon/WA</th>
                        <th>Alamat</th>
                        <th>Total Transaksi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $customers = [
                        ['id' => 'CUST-001', 'name' => 'Budi Santoso', 'phone' => '0812-1234-5678', 'address' => 'Jl. Mawar No. 5', 'trx' => 15],
                        ['id' => 'CUST-002', 'name' => 'Siti Aminah', 'phone' => '0857-9876-5432', 'address' => 'Perum Indah Blok B/12', 'trx' => 8],
                        ['id' => 'CUST-003', 'name' => 'Ahmad Junaidi', 'phone' => '0819-1122-3344', 'address' => 'Jl. Melati Raya', 'trx' => 24],
                        ['id' => 'CUST-004', 'name' => 'Rini Wulandari', 'phone' => '0813-5566-7788', 'address' => 'Gg. Kancil No. 3', 'trx' => 2],
                    ];
                    @endphp

                    @foreach($customers as $cust)
                    <tr>
                        <td><span class="fw-medium text-primary">{{ $cust['id'] }}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($cust['name'], 0, 1) }}</span>
                                </div>
                                <span class="fw-medium">{{ $cust['name'] }}</span>
                            </div>
                        </td>
                        <td>{{ $cust['phone'] }}</td>
                        <td><span class="d-inline-block text-truncate" style="max-width: 200px;">{{ $cust['address'] }}</span></td>
                        <td>{{ $cust['trx'] }} kali</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-icon btn-text-secondary" data-bs-toggle="modal" data-bs-target="#editPelangganModal" 
                                onclick="populateEditModal('{{ $cust['name'] }}', '{{ $cust['phone'] }}', '{{ $cust['address'] }}')">
                                <i class="icon-base bx bx-edit-alt text-info"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon btn-text-secondary" data-bs-toggle="modal" data-bs-target="#hapusPelangganModal">
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

<!-- Modal Tambah Pelanggan -->
<div class="modal fade" id="tambahPelangganModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">Tambah Pelanggan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Contoh: Budi Santoso" required>
                        </div>
                        <div class="col-12">
                            <label for="phone" class="form-label">Nomor WhatsApp / HP</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bxl-whatsapp"></i></span>
                                <input type="text" id="phone" name="phone" class="form-control" placeholder="0812-xxxx-xxxx">
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Alamat Lengkap</label>
                            <textarea id="address" name="address" class="form-control" rows="3" placeholder="Alamat pelanggan..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pelanggan -->
<div class="modal fade" id="editPelangganModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">Edit Data Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <!-- <input type="hidden" name="_method" value="PUT"> -->
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="edit_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" id="edit_name" name="name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label for="edit_phone" class="form-label">Nomor WhatsApp / HP</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bxl-whatsapp"></i></span>
                                <input type="text" id="edit_phone" name="phone" class="form-control">
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

<!-- Modal Hapus Pelanggan -->
<div class="modal fade" id="hapusPelangganModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="avatar avatar-xl bg-label-danger mx-auto mb-4">
                    <span class="avatar-initial rounded-circle"><i class="bx bx-trash fs-2"></i></span>
                </div>
                <h5 class="fw-bold">Hapus Pelanggan?</h5>
                <p class="text-muted mb-4">Apakah Anda yakin ingin menghapus data pelanggan ini?</p>
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
    function populateEditModal(name, phone, address) {
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_phone').value = phone;
        document.getElementById('edit_address').value = address;
    }
</script>
@endsection
