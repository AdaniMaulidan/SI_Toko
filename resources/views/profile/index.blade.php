@extends('layouts.app')

@section('title', 'Profil Pengguna')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-0">
            <span class="text-muted fw-light">Akun /</span> Profil Pengguna
        </h4>
        <small class="text-muted">Kelola identitas akun pribadi, foto profil, dan pengaturan kata sandi Anda</small>
    </div>
</div>

{{-- Alert Notifikasi Flash --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
    <i class="bx bx-check-circle fs-4 me-2"></i>
    <div>{{ session('success') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
    <i class="bx bx-error fs-4 me-2"></i>
    <div>{{ session('error') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <div class="d-flex align-items-center mb-1">
        <i class="bx bx-error-circle fs-4 me-2"></i>
        <strong>Terdapat kesalahan pada input Anda:</strong>
    </div>
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
    <!-- Kolom Kiri: Form Profil & Foto -->
    <div class="col-lg-8 mb-4">
        {{-- Card 1: Foto Profil & Informasi Akun --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-bottom py-3">
                <h5 class="card-title mb-0 d-flex align-items-center">
                    <i class="bx bx-user me-2 text-primary"></i> Foto Profil & Data Diri
                </h5>
            </div>
            <div class="card-body pt-4">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="form-profile">
                    @csrf
                    @method('PUT')

                    {{-- Upload Foto Profil --}}
                    <div class="d-flex align-items-start align-items-sm-center gap-4 pb-4 border-bottom mb-4 flex-column flex-sm-row">
                        <div class="position-relative">
                            <img src="{{ $user->image_url }}" alt="{{ $user->name }}" 
                                 class="rounded-circle object-fit-cover border shadow-sm" 
                                 id="preview-avatar" 
                                 style="width: 100px; height: 100px;">
                        </div>
                        <div class="button-wrapper">
                            <label for="upload-profile-image" class="btn btn-primary me-2 mb-2" tabindex="0">
                                <i class="bx bx-upload me-1"></i> Pilih Foto Baru
                                <input type="file" id="upload-profile-image" name="image" class="d-none" accept="image/png, image/jpeg, image/jpg, image/webp" onchange="previewProfileImage(this)">
                            </label>
                            
                            @if($user->hasCustomImage())
                            <button type="button" class="btn btn-outline-danger mb-2" onclick="confirmRemovePhoto()">
                                <i class="bx bx-trash me-1"></i> Hapus Foto
                            </button>
                            @endif

                            <div class="text-muted small mt-1">
                                Format yang didukung: JPG, PNG, atau WEBP. Maksimal ukuran file: <strong>2MB</strong>.
                            </div>
                        </div>
                    </div>

                    {{-- Input Data Diri --}}
                    <div class="row g-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required value="{{ old('name', $user->name) }}" placeholder="Nama pengguna">
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Email Login (Akun)</label>
                            <input type="email" class="form-control bg-light" value="{{ $user->email }}" readonly disabled>
                            <small class="text-muted">Email tidak dapat diubah secara langsung demi keamanan akun.</small>
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">No. Telepon / WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="cth. 081234567890">
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Role Hak Akses</label>
                            <div>
                                @if($user->isAdmin())
                                    <span class="badge bg-label-danger fs-6 px-3 py-2"><i class="bx bx-shield-quarter me-1"></i> Administrator (Akses Penuh)</span>
                                @elseif($user->isCashier())
                                    <span class="badge bg-label-info fs-6 px-3 py-2"><i class="bx bx-desktop me-1"></i> Kasir (POS Penjualan)</span>
                                @else
                                    <span class="badge bg-label-warning fs-6 px-3 py-2"><i class="bx bx-box me-1"></i> Gudang / Staf</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Card 2: Keamanan & Ganti Password --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom py-3">
                <h5 class="card-title mb-0 d-flex align-items-center">
                    <i class="bx bx-lock-alt me-2 text-warning"></i> Keamanan & Kata Sandi
                </h5>
            </div>
            <div class="card-body pt-4">
                <form action="{{ route('profile.update-password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Password Saat Ini <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" class="form-control" required placeholder="Masukkan password saat ini">
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Password Baru <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control" required placeholder="Minimal 6 karakter">
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <input type="password" name="new_password_confirmation" class="form-control" required placeholder="Ulangi password baru">
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top text-end">
                        <button type="submit" class="btn btn-warning">
                            <i class="bx bx-shield-check me-1"></i> Perbarui Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Ringkasan Kartu Identitas Akun -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm text-center mb-4">
            <div class="card-body pt-5 pb-4">
                <div class="mx-auto mb-3 position-relative" style="width: 110px; height: 110px;">
                    <img src="{{ $user->image_url }}" alt="{{ $user->name }}" 
                         class="rounded-circle object-fit-cover border shadow" 
                         style="width: 110px; height: 110px;" id="sidebar-preview-avatar">
                </div>
                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <p class="text-muted small mb-2">{{ $user->email }}</p>
                <div>
                    @if($user->isAdmin())
                        <span class="badge bg-danger"><i class="bx bx-shield-quarter me-1"></i> Administrator</span>
                    @elseif($user->isCashier())
                        <span class="badge bg-info"><i class="bx bx-desktop me-1"></i> Kasir</span>
                    @else
                        <span class="badge bg-warning text-dark"><i class="bx bx-box me-1"></i> Staf / Gudang</span>
                    @endif
                </div>

                <hr class="my-4">

                <div class="text-start">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Status Akun:</span>
                        @if($user->is_active)
                            <span class="badge bg-label-success small"><i class="bx bx-check-circle me-1"></i> Aktif</span>
                        @else
                            <span class="badge bg-label-danger small"><i class="bx bx-x-circle me-1"></i> Non-Aktif</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">No. WhatsApp:</span>
                        <span class="small fw-semibold">{{ $user->phone ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Bergabung Sejak:</span>
                        <span class="small text-muted">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <h6 class="fw-bold mb-2"><i class="bx bx-info-circle text-primary me-1"></i> Tips Foto Profil</h6>
                <p class="small text-muted mb-0">
                    Foto profil akan tampil di sudut kanan atas navbar sistem dan di header layar POS Kasir. Gunakan foto persegi beresolusi jelas untuk hasil terbaik.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Form Hapus Foto Profil Tersembunyi --}}
<form id="form-remove-photo" action="{{ route('profile.remove-image') }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('page-script')
<script>
    function previewProfileImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('preview-avatar');
                const sidebarPreview = document.getElementById('sidebar-preview-avatar');
                if (preview) preview.src = e.target.result;
                if (sidebarPreview) sidebarPreview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function confirmRemovePhoto() {
        if (confirm('Apakah Anda yakin ingin menghapus foto profil akun Anda?')) {
            document.getElementById('form-remove-photo').submit();
        }
    }
</script>
@endsection
