@extends('layouts.app')

@section('title', 'Riwayat Aktivitas & Audit')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Sistem /</span> Riwayat Aktivitas
    </h4>
</div>

<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Audit Trail (Log Aktivitas Sistem)</h5>
    </div>
    <div class="card-body mt-4">
        
        <div class="row gx-3 gy-2 align-items-center mb-4">
            <div class="col-md-3">
                <label class="form-label">Tanggal</label>
                <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Pengguna (User)</label>
                <select class="form-select">
                    <option value="">Semua User</option>
                    <option value="admin">Admin Utama</option>
                    <option value="kasir1">Kasir 1</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Jenis Aktivitas</label>
                <select class="form-select">
                    <option value="">Semua Aktivitas</option>
                    <option value="Login">Login / Logout</option>
                    <option value="Delete">Penghapusan Data</option>
                    <option value="Update">Perubahan Stok/Data</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-primary w-100 mt-4">Filter Log</button>
            </div>
        </div>

        <ul class="timeline mt-4 mb-0">
            <!-- Timeline Item 1 -->
            <li class="timeline-item timeline-item-transparent ps-4">
                <span class="timeline-point timeline-point-danger"></span>
                <div class="timeline-event pb-2">
                    <div class="timeline-header mb-1">
                        <h6 class="mb-0 fw-bold">Penghapusan Data Transaksi</h6>
                        <small class="text-muted">Hari ini, 10:45 WIB</small>
                    </div>
                    <p class="mb-2">Admin Utama menghapus riwayat transaksi dengan ID <span class="fw-medium">TRX-10020</span>.</p>
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-xs me-2">
                            <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle">
                        </div>
                        <small class="text-muted">Oleh: Admin Utama</small>
                    </div>
                </div>
            </li>
            
            <!-- Timeline Item 2 -->
            <li class="timeline-item timeline-item-transparent ps-4">
                <span class="timeline-point timeline-point-info"></span>
                <div class="timeline-event pb-2">
                    <div class="timeline-header mb-1">
                        <h6 class="mb-0 fw-bold">Login Sistem</h6>
                        <small class="text-muted">Hari ini, 08:30 WIB</small>
                    </div>
                    <p class="mb-2">Kasir 1 (Budi) berhasil login ke dalam sistem via IP 192.168.1.15.</p>
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-xs me-2">
                            <img src="{{ asset('assets/img/avatars/2.png') }}" alt="Avatar" class="rounded-circle">
                        </div>
                        <small class="text-muted">Oleh: Kasir 1</small>
                    </div>
                </div>
            </li>
            
            <!-- Timeline Item 3 -->
            <li class="timeline-item timeline-item-transparent ps-4">
                <span class="timeline-point timeline-point-warning"></span>
                <div class="timeline-event pb-2">
                    <div class="timeline-header mb-1">
                        <h6 class="mb-0 fw-bold">Penyesuaian Stok Manual (Opname)</h6>
                        <small class="text-muted">Kemarin, 16:20 WIB</small>
                    </div>
                    <p class="mb-2">Stok <span class="fw-medium">Beras Sania 5kg (BRG-003)</span> dikurangi sebanyak 1 Pcs. Alasan: Kemasan sobek dimakan tikus.</p>
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-xs me-2">
                            <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle">
                        </div>
                        <small class="text-muted">Oleh: Admin Utama</small>
                    </div>
                </div>
            </li>
            
            <!-- Timeline Item 4 -->
            <li class="timeline-item timeline-item-transparent ps-4 border-0">
                <span class="timeline-point timeline-point-success"></span>
                <div class="timeline-event pb-0">
                    <div class="timeline-header mb-1">
                        <h6 class="mb-0 fw-bold">Perubahan Pengaturan Toko</h6>
                        <small class="text-muted">Kemarin, 10:15 WIB</small>
                    </div>
                    <p class="mb-2">Admin Utama mengubah pengaturan <span class="fw-medium">Footer Struk Kasir</span>.</p>
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-xs me-2">
                            <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle">
                        </div>
                        <small class="text-muted">Oleh: Admin Utama</small>
                    </div>
                </div>
            </li>
        </ul>
        
        <div class="mt-4 text-center">
            <button class="btn btn-outline-primary btn-sm">Muat Lebih Banyak...</button>
        </div>
    </div>
</div>
@endsection
