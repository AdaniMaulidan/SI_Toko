@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-1">
            <span class="text-muted fw-light">Pengaturan /</span> Manajemen Pengguna
        </h4>
        <p class="text-muted mb-0">Kelola akun kasir, staf gudang, dan administrator sistem.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahUserModal">
        <i class="bx bx-user-plus me-1"></i> Tambah Pengguna
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
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="avatar avatar-md me-3 bg-label-primary rounded p-2 d-flex align-items-center justify-content-center">
                    <i class="bx bx-group fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Total Pengguna</span>
                    <h4 class="card-title mb-0 fw-bold">{{ $stats['total'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="avatar avatar-md me-3 bg-label-danger rounded p-2 d-flex align-items-center justify-content-center">
                    <i class="bx bx-shield-quarter fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Administrator</span>
                    <h4 class="card-title mb-0 fw-bold">{{ $stats['admin'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="avatar avatar-md me-3 bg-label-info rounded p-2 d-flex align-items-center justify-content-center">
                    <i class="bx bx-desktop fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Kasir (POS)</span>
                    <h4 class="card-title mb-0 fw-bold">{{ $stats['cashier'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="avatar avatar-md me-3 bg-label-success rounded p-2 d-flex align-items-center justify-content-center">
                    <i class="bx bx-check-shield fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Akun Aktif</span>
                    <h4 class="card-title mb-0 fw-bold">{{ $stats['active'] }} <small class="text-muted fs-6 fw-normal">({{ $stats['inactive'] }} non-aktif)</small></h4>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter & Search Card --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5 col-12">
                <label class="form-label fw-semibold">Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau no telepon..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label fw-semibold">Filter Role</label>
                <select name="role" class="form-select">
                    <option value="">-- Semua Role --</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                    <option value="cashier" {{ request('role') === 'cashier' ? 'selected' : '' }}>Kasir</option>
                    <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Gudang / Staf</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label fw-semibold">Status Akun</label>
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>
            <div class="col-md-2 col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bx bx-filter-alt me-1"></i> Filter
                </button>
                @if (request()->hasAny(['search', 'role', 'status']))
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                    <i class="bx bx-reset"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Main Users Table Card --}}
<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold">Daftar Pengguna Sistem</h5>
        <span class="badge bg-label-primary">{{ $users->total() }} Pengguna Terdaftar</span>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Nama & Email</th>
                    <th>No. Telepon</th>
                    <th>Role (Hak Akses)</th>
                    <th>Status Akun</th>
                    <th>Terdaftar</th>
                    <th class="text-center" style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($users as $user)
                <tr>
                    <td class="text-muted">{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                @if($user->hasCustomImage())
                                    <img src="{{ $user->image_url }}" alt="{{ $user->name }}" class="rounded-circle w-100 h-100 object-fit-cover border">
                                @else
                                    <span class="avatar-initial rounded-circle bg-label-{{ $user->isAdmin() ? 'danger' : ($user->isCashier() ? 'info' : 'warning') }} fw-bold">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                @endif
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold">{{ $user->name }}
                                    @if(Auth::id() === $user->id)
                                    <span class="badge bg-label-success ms-1 small">Anda</span>
                                    @endif
                                </h6>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        {{ $user->phone ?? '-' }}
                    </td>
                    <td>
                        @if($user->isAdmin())
                            <span class="badge bg-label-danger"><i class="bx bx-shield-quarter me-1"></i> Administrator</span>
                        @elseif($user->isCashier())
                            <span class="badge bg-label-info"><i class="bx bx-desktop me-1"></i> Kasir</span>
                        @else
                            <span class="badge bg-label-warning"><i class="bx bx-box me-1"></i> Gudang / Staf</span>
                        @endif
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i> Aktif</span>
                        @else
                            <span class="badge bg-label-secondary"><i class="bx bx-x-circle me-1"></i> Non-Aktif</span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</small>
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
                                   data-bs-target="#editUserModal"
                                   onclick="populateEditUserModal('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->phone }}', '{{ $user->role }}', {{ $user->is_active ? 'true' : 'false' }}, {{ Auth::id() === $user->id ? 'true' : 'false' }}, '{{ $user->image_url }}', {{ $user->hasCustomImage() ? 'true' : 'false' }})">
                                    <i class="bx bx-edit-alt me-2 text-primary"></i> Edit Profil
                                </a>

                                {{-- Ganti Password Button --}}
                                <a class="dropdown-item" href="javascript:void(0);" 
                                   data-bs-toggle="modal" 
                                   data-bs-target="#resetPasswordModal"
                                   onclick="populateResetPasswordModal('{{ $user->id }}', '{{ addslashes($user->name) }}')">
                                    <i class="bx bx-key me-2 text-warning"></i> Ganti Password
                                </a>

                                {{-- Toggle Status Button --}}
                                @if(Auth::id() !== $user->id)
                                <form action="{{ route('users.toggle', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="dropdown-item">
                                        @if($user->is_active)
                                            <i class="bx bx-user-x me-2 text-secondary"></i> Non-aktifkan Akun
                                        @else
                                            <i class="bx bx-user-check me-2 text-success"></i> Aktifkan Akun
                                        @endif
                                    </button>
                                </form>

                                <div class="dropdown-divider"></div>

                                {{-- Hapus Button --}}
                                <button type="button" class="dropdown-item text-danger" 
                                        onclick="confirmDeleteUser('{{ $user->id }}', '{{ addslashes($user->name) }}')">
                                    <i class="bx bx-trash me-2"></i> Hapus Pengguna
                                </button>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <i class="bx bx-user-x fs-1 text-muted mb-2"></i>
                            <h6 class="fw-bold mb-1">Tidak ada data pengguna</h6>
                            <p class="small mb-3">Belum ada pengguna yang sesuai dengan filter atau kata kunci pencarian.</p>
                            <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-primary">Reset Filter</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="card-footer border-top d-flex justify-content-between align-items-center py-3">
        <small class="text-muted">Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} pengguna</small>
        <div>
            {{ $users->links() }}
        </div>
    </div>
    @endif
</div>

{{-- Form Penghapusan Tersembunyi --}}
<form id="deleteUserForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Modal Tambah Pengguna -->
<div class="modal fade" id="tambahUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold"><i class="bx bx-user-plus me-1 text-primary"></i> Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body py-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Foto Profil (Opsional)</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar avatar-md border rounded-circle overflow-hidden bg-light" style="width: 52px; height: 52px;">
                                    <img id="preview_add_avatar" src="{{ asset('assets/img/avatars/1.png') }}" alt="Preview" class="w-100 h-100 object-fit-cover">
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="image" class="form-control form-control-sm" accept="image/png, image/jpeg, image/jpg, image/webp" onchange="previewAvatar(this, 'preview_add_avatar')">
                                    <small class="text-muted d-block mt-1">Format: JPG, PNG, WEBP (Maks. 2MB)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="cth. Budi Santoso" value="{{ old('name') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Email Login <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required placeholder="cth. kasir1@sitoko.com" value="{{ old('email') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">No. Telepon / WhatsApp</label>
                            <input type="text" name="phone" class="form-control" placeholder="cth. 081234567890" value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Role (Hak Akses) <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="cashier" selected>Kasir (POS Transaksi)</option>
                                <option value="staff">Gudang / Staf (Stok & Produk)</option>
                                <option value="admin">Administrator (Akses Penuh)</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required placeholder="Min. 6 Karakter">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="status_add" value="1" checked>
                                <label class="form-check-label fw-semibold" for="status_add">Akun Langsung Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan Pengguna</button>
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
                <h5 class="modal-title fw-bold"><i class="bx bx-edit-alt me-1 text-primary"></i> Edit Profil Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body py-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Foto Profil</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar avatar-md border rounded-circle overflow-hidden bg-light" style="width: 52px; height: 52px;">
                                    <img id="preview_edit_avatar" src="{{ asset('assets/img/avatars/1.png') }}" alt="Preview" class="w-100 h-100 object-fit-cover">
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="image" class="form-control form-control-sm" accept="image/png, image/jpeg, image/jpg, image/webp" onchange="previewAvatar(this, 'preview_edit_avatar')">
                                    <div id="remove_image_container" class="mt-2 d-none">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image_check" value="1">
                                            <label class="form-check-label text-danger small" for="remove_image_check">
                                                <i class="bx bx-trash me-1"></i> Hapus Foto Profil Saat Ini
                                            </label>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1">Format: JPG, PNG, WEBP (Maks. 2MB)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Email Login <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">No. Telepon / WhatsApp</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Role (Hak Akses) <span class="text-danger">*</span></label>
                            <select name="role" id="edit_role" class="form-select" required>
                                <option value="cashier">Kasir (POS Transaksi)</option>
                                <option value="staff">Gudang / Staf (Stok & Produk)</option>
                                <option value="admin">Administrator (Akses Penuh)</option>
                            </select>
                            <small id="self_role_note" class="text-muted small mt-1 d-none">Anda tidak dapat mengubah role akun Anda sendiri.</small>
                        </div>
                        <div class="col-12" id="edit_status_container">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="edit_status" value="1">
                                <label class="form-check-label fw-semibold" for="edit_status">Akun Aktif</label>
                            </div>
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

<!-- Modal Ganti Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold"><i class="bx bx-key me-1 text-warning"></i> Ganti Password Akun</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="resetPasswordForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body py-3">
                    <p class="text-muted mb-3">Ganti password akun untuk pengguna: <span class="fw-bold text-dark" id="reset_user_display"></span></p>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Password Baru <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control" required placeholder="Minimal 6 Karakter">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <input type="password" name="new_password_confirmation" class="form-control" required placeholder="Ketik ulang password baru">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="bx bx-shield-check me-1"></i> Perbarui Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    function populateEditUserModal(id, name, email, phone, role, isActive, isSelf, imageUrl, hasCustomImage) {
        document.getElementById('editUserForm').action = '/users/' + id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_phone').value = phone || '';
        
        const roleSelect = document.getElementById('edit_role');
        roleSelect.value = role;
        
        const statusCheckbox = document.getElementById('edit_status');
        statusCheckbox.checked = isActive;

        // Foto profil
        const previewAvatar = document.getElementById('preview_edit_avatar');
        if (previewAvatar) previewAvatar.src = imageUrl || '/assets/img/avatars/1.png';

        const removeContainer = document.getElementById('remove_image_container');
        const removeCheck = document.getElementById('remove_image_check');
        if (removeCheck) removeCheck.checked = false;
        if (removeContainer) {
            if (hasCustomImage) {
                removeContainer.classList.remove('d-none');
            } else {
                removeContainer.classList.add('d-none');
            }
        }

        // Proteksi jika mengedit akun sendiri
        const selfNote = document.getElementById('self_role_note');
        const statusContainer = document.getElementById('edit_status_container');
        if (isSelf) {
            roleSelect.setAttribute('disabled', 'disabled');
            selfNote.classList.remove('d-none');
            statusContainer.classList.add('d-none');
        } else {
            roleSelect.removeAttribute('disabled');
            selfNote.classList.add('d-none');
            statusContainer.classList.remove('d-none');
        }
    }

    function previewAvatar(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById(previewId);
                if (preview) preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function populateResetPasswordModal(id, name) {
        document.getElementById('resetPasswordForm').action = '/users/' + id + '/password';
        document.getElementById('reset_user_display').innerText = name;
    }

    function confirmDeleteUser(id, name) {
        if (confirm('Apakah Anda yakin ingin menghapus akun pengguna "' + name + '"? Tindakan ini tidak dapat dibatalkan.')) {
            const form = document.getElementById('deleteUserForm');
            form.action = '/users/' + id;
            form.submit();
        }
    }
</script>
@endsection
