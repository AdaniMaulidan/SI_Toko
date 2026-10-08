@extends('layouts.app')

@section('title', 'Pengeluaran Toko')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Keuangan /</span> Pengeluaran Toko
    </h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahPengeluaranModal">
        <i class="bx bx-plus me-1"></i> Catat Pengeluaran
    </button>
</div>

<!-- Ringkasan Pengeluaran -->
<div class="row mb-4">
    <div class="col-md-6 col-xl-4">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="content-left">
                        <span class="fw-medium d-block mb-1">Pengeluaran Bulan Ini</span>
                        <h4 class="card-title text-white mb-0">Rp 4.250.000</h4>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-white text-danger">
                            <i class="bx bx-trending-down fs-4"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="content-left">
                        <span class="text-muted fw-medium d-block mb-1">Kategori Tertinggi</span>
                        <h5 class="card-title mb-0">Operasional (Listrik & Air)</h5>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class="bx bx-bolt-circle fs-4"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Riwayat Pengeluaran Operasional</h5>
    </div>
    <div class="card-body mt-4">
        <div class="row gx-3 gy-2 align-items-end mb-3">
            <div class="col-md-3">
                <label class="form-label" for="filter-bulan">Bulan</label>
                <input type="month" id="filter-bulan" class="form-control" value="{{ date('Y-m') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="filter-kategori">Kategori Pengeluaran</label>
                <select id="filter-kategori" class="form-select">
                    <option value="">Semua Kategori</option>
                    <option value="Operasional">Operasional (Listrik/Air/Internet)</option>
                    <option value="Gaji">Gaji Karyawan</option>
                    <option value="Perlengkapan">Perlengkapan Toko</option>
                    <option value="Lainnya">Lain-lain</option>
                </select>
            </div>
            <div class="col-md-4 offset-md-2">
                <label class="form-label" for="search-pengeluaran">Cari Keterangan</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" id="search-pengeluaran" class="form-control" placeholder="Cari catatan pengeluaran...">
                </div>
            </div>
        </div>

        <div class="table-responsive text-nowrap border rounded">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th>Nominal</th>
                        <th>Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $expenses = [
                        ['date' => '05 Okt 2026', 'category' => 'Operasional', 'note' => 'Bayar Listrik Toko Bulan September', 'amount' => 850000, 'user' => 'Admin Utama'],
                        ['date' => '02 Okt 2026', 'category' => 'Perlengkapan', 'note' => 'Beli plastik kresek & lakban', 'amount' => 125000, 'user' => 'Admin Utama'],
                        ['date' => '01 Okt 2026', 'category' => 'Gaji', 'note' => 'Gaji Kasir 1', 'amount' => 2500000, 'user' => 'Admin Utama'],
                        ['date' => '28 Sep 2026', 'category' => 'Lainnya', 'note' => 'Iuran kebersihan lingkungan', 'amount' => 50000, 'user' => 'Admin Utama'],
                    ];
                    @endphp

                    @foreach($expenses as $exp)
                    <tr>
                        <td>{{ $exp['date'] }}</td>
                        <td>
                            @if($exp['category'] == 'Operasional')
                                <span class="badge bg-label-warning">{{ $exp['category'] }}</span>
                            @elseif($exp['category'] == 'Gaji')
                                <span class="badge bg-label-info">{{ $exp['category'] }}</span>
                            @elseif($exp['category'] == 'Perlengkapan')
                                <span class="badge bg-label-primary">{{ $exp['category'] }}</span>
                            @else
                                <span class="badge bg-label-secondary">{{ $exp['category'] }}</span>
                            @endif
                        </td>
                        <td><span class="d-inline-block text-truncate" style="max-width: 250px;" title="{{ $exp['note'] }}">{{ $exp['note'] }}</span></td>
                        <td class="fw-bold text-danger">Rp {{ number_format($exp['amount'], 0, ',', '.') }}</td>
                        <td><small class="text-muted">{{ $exp['user'] }}</small></td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editPengeluaranModal" onclick="populateEditModal('{{ $exp['category'] }}', '{{ $exp['amount'] }}', '{{ $exp['note'] }}')"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                                    <a class="dropdown-item text-danger" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#hapusPengeluaranModal"><i class="icon-base bx bx-trash me-1"></i> Hapus</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengeluaran -->
<div class="modal fade" id="tambahPengeluaranModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">Catat Pengeluaran Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="tanggal" class="form-label">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select id="kategori" name="kategori" class="form-select" required>
                                <option value="" selected disabled>Pilih Kategori</option>
                                <option value="Operasional">Operasional (Listrik/Air/Internet)</option>
                                <option value="Gaji">Gaji Karyawan</option>
                                <option value="Perlengkapan">Perlengkapan Toko</option>
                                <option value="Lainnya">Lain-lain</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="nominal" class="form-label">Nominal (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="nominal" name="nominal" class="form-control" placeholder="0" min="1" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="keterangan" class="form-label">Keterangan Lengkap <span class="text-danger">*</span></label>
                            <textarea id="keterangan" name="keterangan" class="form-control" rows="3" placeholder="Contoh: Bayar listrik bulan ini" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Pengeluaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pengeluaran -->
<div class="modal fade" id="editPengeluaranModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">Edit Pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <!-- <input type="hidden" name="_method" value="PUT"> -->
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="edit_kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select id="edit_kategori" name="kategori" class="form-select" required>
                                <option value="Operasional">Operasional (Listrik/Air/Internet)</option>
                                <option value="Gaji">Gaji Karyawan</option>
                                <option value="Perlengkapan">Perlengkapan Toko</option>
                                <option value="Lainnya">Lain-lain</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="edit_nominal" class="form-label">Nominal (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="edit_nominal" name="nominal" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="edit_keterangan" class="form-label">Keterangan Lengkap <span class="text-danger">*</span></label>
                            <textarea id="edit_keterangan" name="keterangan" class="form-control" rows="3" required></textarea>
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

<!-- Modal Hapus Pengeluaran -->
<div class="modal fade" id="hapusPengeluaranModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="avatar avatar-xl bg-label-danger mx-auto mb-4">
                    <span class="avatar-initial rounded-circle"><i class="bx bx-trash fs-2"></i></span>
                </div>
                <h5 class="fw-bold">Hapus Data?</h5>
                <p class="text-muted mb-4">Apakah Anda yakin ingin menghapus data pengeluaran ini?</p>
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
    function populateEditModal(kategori, nominal, keterangan) {
        document.getElementById('edit_kategori').value = kategori;
        document.getElementById('edit_nominal').value = nominal;
        document.getElementById('edit_keterangan').value = keterangan;
    }
</script>
@endsection
