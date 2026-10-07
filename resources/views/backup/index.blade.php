@extends('layouts.app')

@section('title', 'Backup & Restore Database')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Sistem /</span> Backup & Restore
    </h4>
</div>

<div class="row">
    <!-- Kolom Backup -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <h5 class="card-header border-bottom bg-label-primary text-primary"><i class="bx bx-download me-2"></i> Backup Database</h5>
            <div class="card-body mt-4">
                <p class="text-muted">
                    Lakukan pencadangan (backup) data secara rutin untuk menghindari kehilangan data jika terjadi kerusakan sistem atau server mati.
                </p>
                <div class="alert alert-warning mb-4">
                    <h6 class="alert-heading fw-bold mb-1"><i class="bx bx-error me-1"></i> Peringatan</h6>
                    <span>Proses backup mungkin memakan waktu beberapa saat tergantung pada ukuran database Anda. Jangan tutup halaman saat proses berjalan.</span>
                </div>
                
                <div class="d-grid gap-2">
                    <button class="btn btn-primary btn-lg" type="button">
                        <i class="bx bx-cloud-download me-2"></i> Buat Backup Sekarang
                    </button>
                </div>
                
                <hr class="my-4">
                
                <h6 class="fw-bold mb-3">Riwayat Backup Terakhir</h6>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <span class="d-block fw-medium">backup_2026-10-06.sql</span>
                            <small class="text-muted">06 Okt 2026, 23:59 (4.2 MB)</small>
                        </div>
                        <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-outline-primary" title="Unduh"><i class="bx bx-download"></i></a>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <span class="d-block fw-medium">backup_2026-10-05.sql</span>
                            <small class="text-muted">05 Okt 2026, 23:59 (4.1 MB)</small>
                        </div>
                        <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-outline-primary" title="Unduh"><i class="bx bx-download"></i></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Kolom Restore -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <h5 class="card-header border-bottom bg-label-danger text-danger"><i class="bx bx-upload me-2"></i> Restore Database</h5>
            <div class="card-body mt-4">
                <p class="text-muted">
                    Gunakan fitur ini untuk memulihkan (restore) data dari file backup (.sql).
                </p>
                <div class="alert alert-danger mb-4">
                    <h6 class="alert-heading fw-bold mb-1"><i class="bx bx-error-alt me-1"></i> Perhatian Sangat Penting!</h6>
                    <span>Proses restore akan <strong>MENGHAPUS & MENIMPA</strong> seluruh data yang ada saat ini dengan data dari file backup. Pastikan file yang Anda unggah sudah benar.</span>
                </div>
                
                <form action="#" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label for="file_restore" class="form-label fw-bold">Pilih File Backup (.sql)</label>
                        <input class="form-control form-control-lg" type="file" id="file_restore" accept=".sql" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-danger btn-lg" type="button" data-bs-toggle="modal" data-bs-target="#confirmRestoreModal">
                            <i class="bx bx-cloud-upload me-2"></i> Pulihkan Database
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Restore -->
<div class="modal fade" id="confirmRestoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title fw-bold text-white">Konfirmasi Restore Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <i class="bx bx-error-circle text-danger mb-3" style="font-size: 5rem;"></i>
                <h4 class="fw-bold mb-2">Apakah Anda Yakin?</h4>
                <p class="text-muted">Semua data transaksi dan produk saat ini akan digantikan secara permanen oleh data dari file backup. Aksi ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer border-top pt-3 d-flex justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batalkan</button>
                <button type="button" class="btn btn-danger px-4">Ya, Saya Mengerti & Restore Data</button>
            </div>
        </div>
    </div>
</div>
@endsection
