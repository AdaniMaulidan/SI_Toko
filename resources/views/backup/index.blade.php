@extends('layouts.app')

@section('title', 'Backup & Restore Database')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-0">
            <span class="text-muted fw-light">Sistem /</span> Backup & Restore Database
        </h4>
        <small class="text-muted">Cadangkan seluruh data transaksi, produk, dan pengaturan toko ke format file .SQL sekali klik atau pulihkan data sistem.</small>
    </div>
</div>

{{-- Alert Notifikasi --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
    <i class="bx bx-check-circle fs-4 me-2"></i>
    <div>{{ session('success') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
    <i class="bx bx-error-circle fs-4 me-2"></i>
    <div>{{ session('error') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <div class="d-flex align-items-center mb-1">
        <i class="bx bx-error-circle fs-4 me-2"></i>
        <strong>Periksa kembali kesalahan berikut:</strong>
    </div>
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Baris Statistik Database -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
        <div class="card card-border-shadow-primary h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                    <div class="avatar me-2">
                        <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-data fs-4"></i></span>
                    </div>
                    <h4 class="ms-1 mb-0">{{ $dbStats['total_tables'] }}</h4>
                </div>
                <p class="mb-1 text-muted">Jumlah Tabel Data</p>
                <small class="text-primary fw-medium">{{ $dbStats['database_name'] }} ({{ strtoupper($dbStats['driver']) }})</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
        <div class="card card-border-shadow-success h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                    <div class="avatar me-2">
                        <span class="avatar-initial rounded bg-label-success"><i class="bx bx-list-check fs-4"></i></span>
                    </div>
                    <h4 class="ms-1 mb-0">{{ number_format($dbStats['total_records']) }}</h4>
                </div>
                <p class="mb-1 text-muted">Total Baris Catatan</p>
                <small class="text-success fw-medium">Seluruh Entitas Toko</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
        <div class="card card-border-shadow-info h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                    <div class="avatar me-2">
                        <span class="avatar-initial rounded bg-label-info"><i class="bx bx-archive fs-4"></i></span>
                    </div>
                    <h4 class="ms-1 mb-0">{{ count($backups) }}</h4>
                </div>
                <p class="mb-1 text-muted">Berkas Cadangan</p>
                <small class="text-info fw-medium">Tersimpan di Server</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
        <div class="card card-border-shadow-warning h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                    <div class="avatar me-2">
                        <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-shield-quarter fs-4"></i></span>
                    </div>
                    <h4 class="ms-1 mb-0 text-truncate" style="font-size: 1.15rem;">Siap Cadang</h4>
                </div>
                <p class="mb-1 text-muted">Status Keamanan</p>
                <small class="text-muted">Enkripsi & Proteksi Admin</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Kolom Kiri: Pencadangan Database (Backup) -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header border-bottom bg-transparent d-flex justify-content-between align-items-center py-3">
                <h5 class="card-title mb-0 fw-semibold d-flex align-items-center text-primary">
                    <i class="bx bx-download me-2 fs-4"></i> Pencadangan Database (Backup)
                </h5>
                <span class="badge bg-label-primary">Sekali Klik</span>
            </div>
            <div class="card-body py-4">
                <p class="text-muted">
                    Lakukan pencadangan data toko secara berkala untuk menjaga keamanan data produk, stok, kas, dan seluruh faktur transaksi penjualan jika server mengalami kendala.
                </p>

                <!-- Tombol Aksi Backup -->
                <div class="d-flex flex-column gap-2 mb-4">
                    {{-- Unduh Langsung Sekali Klik --}}
                    <a href="{{ route('backup.download') }}" class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm">
                        <i class="bx bx-cloud-download fs-4"></i>
                        <span class="fw-bold">Unduh Backup .SQL (Sekali Klik)</span>
                    </a>

                    {{-- Simpan Snapshot ke Server --}}
                    <form action="{{ route('backup.store') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="bx bx-save"></i>
                            <span>Simpan Backup ke Penyimpanan Server</span>
                        </button>
                    </form>
                </div>

                <hr class="my-4">

                <!-- Daftar Riwayat Backup di Server -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Berkas Cadangan Tersimpan di Server</h6>
                    <small class="text-muted">storage/app/backups</small>
                </div>

                @if(count($backups) > 0)
                <div class="table-responsive border rounded">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Berkas</th>
                                <th>Waktu</th>
                                <th class="text-end">Ukuran</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($backups as $b)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bx bxs-file-archive text-primary fs-4 me-2"></i>
                                        <span class="fw-medium text-dark font-monospace" style="font-size: 0.85rem;">{{ $b['filename'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $b['created_at']->format('d M Y, H:i') }}</small>
                                </td>
                                <td class="text-end font-monospace small">
                                    {{ $b['size_human'] }}
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Unduh -->
                                        <a href="{{ route('backup.download-file', $b['filename']) }}" 
                                           class="btn btn-icon btn-outline-primary" 
                                           title="Unduh Berkas">
                                            <i class="bx bx-download"></i>
                                        </a>

                                        <!-- Restore dari file ini -->
                                        <button type="button" 
                                                class="btn btn-icon btn-outline-warning" 
                                                title="Pulihkan dari Berkas Ini"
                                                onclick="openRestoreFromServerModal('{{ $b['filename'] }}')">
                                            <i class="bx bx-history"></i>
                                        </button>

                                        <!-- Hapus -->
                                        <button type="button" 
                                                class="btn btn-icon btn-outline-danger" 
                                                title="Hapus Berkas"
                                                onclick="confirmDeleteBackup('{{ $b['filename'] }}')">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 bg-light rounded border border-dashed">
                    <i class="bx bx-archive text-muted fs-1 mb-2 d-block"></i>
                    <p class="text-muted mb-0 small">Belum ada file backup yang tersimpan di server.<br>Klik tombol di atas untuk membuat cadangan baru.</p>
                </div>
                @endif

            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Pemulihan Database (Restore) -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header border-bottom bg-transparent d-flex justify-content-between align-items-center py-3">
                <h5 class="card-title mb-0 fw-semibold d-flex align-items-center text-danger">
                    <i class="bx bx-upload me-2 fs-4"></i> Pemulihan Database (Restore)
                </h5>
                <span class="badge bg-label-danger">Operasi Kritis</span>
            </div>
            <div class="card-body py-4">
                <p class="text-muted">
                    Fitur pemulihan (restore) digunakan untuk mengembalikan seluruh kondisi database toko dari file cadangan SQL (.sql) yang sebelumnya telah Anda simpan.
                </p>

                <!-- Peringatan Kritis -->
                <div class="alert alert-danger d-flex mb-4" role="alert">
                    <i class="bx bx-error-alt fs-3 me-2 flex-shrink-0"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1 text-danger">Perhatian Sangat Penting!</h6>
                        <small class="d-block">
                            Proses pemulihan akan <strong>menimpa dan menggantikan</strong> seluruh data yang ada saat ini dengan data yang ada pada file backup. Pastikan Anda telah membuat cadangan terbaru sebelum melakukan pemulihan.
                        </small>
                    </div>
                </div>

                <!-- Formulir Upload File .SQL untuk Restore -->
                <form id="form-restore-upload" action="{{ route('backup.restore') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label for="file_restore" class="form-label fw-bold">Pilih Berkas Backup (.sql)</label>
                        <div class="input-group">
                            <input class="form-control" type="file" id="file_restore" name="file_restore" accept=".sql" required>
                            <span class="input-group-text"><i class="bx bx-file"></i></span>
                        </div>
                        <small class="text-muted mt-1 d-block">Format wajib .sql dengan ukuran berkas maksimal 50MB.</small>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-danger btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm" 
                                type="button" 
                                onclick="validateAndOpenRestoreUploadModal()">
                            <i class="bx bx-cloud-upload fs-4"></i>
                            <span class="fw-bold">Pulihkan Database dari File</span>
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <!-- Panduan Langkah Aman -->
                <div class="bg-light p-3 rounded">
                    <h6 class="fw-bold mb-2 small text-uppercase text-muted">Langkah Aman Pemulihan:</h6>
                    <ol class="mb-0 ps-3 small text-muted">
                        <li>Unduh file backup cadangan saat ini terlebih dahulu sebagai antisipasi.</li>
                        <li>Pastikan tidak ada kasir yang sedang aktif melakukan transaksi penjualan.</li>
                        <li>Pilih file .sql yang valid lalu konfirmasi pemulihan.</li>
                        <li>Tunggu sampai sistem menampilkan notifikasi pemulihan berhasil.</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Restore dari File Upload -->
<div class="modal fade" id="modalConfirmRestoreUpload" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center">
                    <i class="bx bx-shield-x me-2"></i> Konfirmasi Pemulihan Database
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <i class="bx bx-error-circle text-danger mb-3" style="font-size: 5rem;"></i>
                <h4 class="fw-bold mb-2">Apakah Anda Benar-Benar Yakin?</h4>
                <p class="text-muted">
                    Semua tabel data saat ini akan ditimpa oleh skrip SQL yang Anda unggah. Tindakan ini <strong>bersifat permanen dan tidak dapat dibatalkan</strong>!
                </p>
                <div class="alert alert-warning py-2 mb-0 small">
                    <i class="bx bx-info-circle me-1"></i> Jangan menutup peramban web saat proses sedang berjalan.
                </div>
            </div>
            <div class="modal-footer border-top pt-3 d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batalkan</button>
                <button type="button" class="btn btn-danger px-4" onclick="executeRestoreUpload()">
                    <i class="bx bx-check me-1"></i> Ya, Pulihkan Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Restore dari File Tersimpan di Server -->
<div class="modal fade" id="modalConfirmRestoreServer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('backup.restore') }}" method="POST">
                @csrf
                <input type="hidden" name="backup_filename" id="restore_server_filename" value="">

                <div class="modal-header bg-danger">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center">
                        <i class="bx bx-history me-2"></i> Konfirmasi Restore dari Server
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <i class="bx bx-error-circle text-danger mb-3" style="font-size: 5rem;"></i>
                    <h4 class="fw-bold mb-2">Pulihkan dari Berkas Server?</h4>
                    <p class="text-muted mb-2">
                        Database toko akan dikembalikan ke kondisi saat file ini dicadangkan:
                    </p>
                    <div class="p-2 bg-light rounded font-monospace fw-bold text-primary mb-3" id="display_restore_filename">
                        -
                    </div>
                    <div class="alert alert-warning py-2 mb-0 small">
                        Aksi ini akan menimpa seluruh transaksi dan data produk terkini.
                    </div>
                </div>
                <div class="modal-footer border-top pt-3 d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batalkan</button>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bx bx-check me-1"></i> Ya, Mulai Pemulihan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form Tersembunyi untuk Hapus File Backup -->
<form id="form-delete-backup" action="" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

@endsection

@section('page-script')
<script>
    // Validasi file sebelum buka modal
    function validateAndOpenRestoreUploadModal() {
        const fileInput = document.getElementById('file_restore');
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            alert('Silakan pilih berkas .sql terlebih dahulu!');
            return;
        }

        const modal = new bootstrap.Modal(document.getElementById('modalConfirmRestoreUpload'));
        modal.show();
    }

    // Submit form restore upload
    function executeRestoreUpload() {
        const form = document.getElementById('form-restore-upload');
        if (form) {
            form.submit();
        }
    }

    // Modal restore dari file server
    function openRestoreFromServerModal(filename) {
        document.getElementById('restore_server_filename').value = filename;
        document.getElementById('display_restore_filename').textContent = filename;

        const modal = new bootstrap.Modal(document.getElementById('modalConfirmRestoreServer'));
        modal.show();
    }

    // Konfirmasi hapus file backup
    function confirmDeleteBackup(filename) {
        if (confirm(`Apakah Anda yakin ingin menghapus file backup "${filename}" dari server?`)) {
            const form = document.getElementById('form-delete-backup');
            form.action = `/backup/${encodeURIComponent(filename)}`;
            form.submit();
        }
    }
</script>
@endsection
