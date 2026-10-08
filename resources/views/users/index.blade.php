@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Pengaturan /</span> Manajemen Pengguna
    </h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahUserModal">
        <i class="bx bx-user-plus me-1"></i> Tambah Pengguna
    </button>
</div>

<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Daftar Akun Karyawan (Kasir & Admin)</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-striped">
            <thead class="table-light">
                <tr>
                    <th>Pengguna</th>
                    <th>Username</th>
                    <th>Role (Hak Akses)</th>
                    <th>Status</th>
                    <th>Terakhir Login</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @php
                $users = [
                    ['name' => 'Owner Toko', 'user' => 'admin', 'role' => 'Administrator', 'status' => 'Aktif', 'last' => 'Hari ini, 08:30', 'avatar' => '1.png'],
                    ['name' => 'Budi Santoso', 'user' => 'kasir1', 'role' => 'Kasir', 'status' => 'Aktif', 'last' => 'Kemarin, 14:00', 'avatar' => '2.png'],
                    ['name' => 'Siti Aminah', 'user' => 'kasir2', 'role' => 'Kasir', 'status' => 'Nonaktif', 'last' => '10 Sep 2026', 'avatar' => '3.png'],
                ];
                @endphp

                @foreach($users as $u)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <img src="{{ asset('assets/img/avatars/' . $u['avatar']) }}" alt="Avatar" class="rounded-circle">
                            </div>
                            <span class="fw-medium text-heading">{{ $u['name'] }}</span>
                        </div>
                    </td>
                    <td>{{ $u['user'] }}</td>
                    <td>
                        <span class="badge bg-label-{{ $u['role'] == 'Administrator' ? 'danger' : 'primary' }}">
                            <i class="bx {{ $u['role'] == 'Administrator' ? 'bx-crown' : 'bx-store-alt' }} me-1"></i>
                            {{ $u['role'] }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-label-{{ $u['status'] == 'Aktif' ? 'success' : 'secondary' }}">
                            {{ $u['status'] }}
                        </span>
                    </td>
                    <td><small class="text-muted">{{ $u['last'] }}</small></td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editUserModal" onclick="populateEditUserModal('{{ $u['name'] }}', '{{ $u['user'] }}', '{{ $u['role'] }}', '{{ $u['status'] }}')"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#resetPasswordModal"><i class="icon-base bx bx-key me-1"></i> Ganti Password</a>
                                @if($u['user'] != 'admin')
                                <a class="dropdown-item text-danger" href="javascript:void(0);"><i class="icon-base bx bx-trash me-1"></i> Hapus</a>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Pengguna -->
<div class="modal fade" id="tambahUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" required placeholder="Nama Karyawan">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" required placeholder="Untuk login">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Role (Hak Akses) <span class="text-danger">*</span></label>
                            <select class="form-select" required>
                                <option value="Kasir">Kasir (Hanya bisa transaksi)</option>
                                <option value="Administrator">Administrator (Akses Penuh)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="status" checked>
                                <label class="form-check-label" for="status">Akun Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pengguna -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">Edit Data Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" id="edit_user" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Role (Hak Akses) <span class="text-danger">*</span></label>
                            <select id="edit_role" class="form-select" required>
                                <option value="Kasir">Kasir (Hanya bisa transaksi)</option>
                                <option value="Administrator">Administrator (Akses Penuh)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="edit_status">
                                <label class="form-check-label" for="edit_status">Akun Aktif</label>
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

<!-- Modal Ganti Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">Ganti Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <p class="text-muted">Masukkan password baru untuk akun terpilih.</p>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Perbarui Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    function populateEditUserModal(name, user, role, status) {
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_user').value = user;
        document.getElementById('edit_role').value = role;
        document.getElementById('edit_status').checked = status === 'Aktif';
    }
</script>
@endsection
