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

{{-- Flash messages --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Ringkasan Pengeluaran -->
<div class="row mb-4">
    <div class="col-md-6 col-xl-4">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="content-left">
                        <span class="fw-medium d-block mb-1">Pengeluaran Bulan Ini</span>
                        <h4 class="card-title text-white mb-0">Rp {{ number_format($stats['total_this_month'], 0, ',', '.') }}</h4>
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
                        <h5 class="card-title mb-0">{{ $stats['top_category'] }}</h5>
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
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="content-left">
                        <span class="text-muted fw-medium d-block mb-1">Total Transaksi (Filter)</span>
                        <h5 class="card-title mb-0">{{ $stats['count_filtered'] }} transaksi</h5>
                        <small class="text-muted">Rp {{ number_format($stats['total_filtered'], 0, ',', '.') }}</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class="bx bx-receipt fs-4"></i>
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
        <form method="GET" action="{{ route('expenses.index') }}">
        <div class="row gx-3 gy-2 align-items-end mb-3">
            <div class="col-md-3">
                <label class="form-label" for="filter-bulan">Bulan</label>
                <input type="month" id="filter-bulan" name="month" class="form-control" value="{{ request('month', date('Y-m')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="filter-kategori">Kategori Pengeluaran</label>
                <select id="filter-kategori" name="category" class="form-select">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="search-pengeluaran">Cari Keterangan</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" id="search-pengeluaran" name="q" class="form-control" placeholder="Cari catatan pengeluaran..." value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="bx bx-filter-alt me-1"></i>Filter</button>
            </div>
        </div>
        </form>

        <div class="table-responsive text-nowrap border rounded">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th>Nominal</th>
                        <th>Bukti</th>
                        <th>Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $exp)
                    <tr>
                        <td><code>{{ $exp->code }}</code></td>
                        <td>{{ $exp->date->translatedFormat('d M Y') }}</td>
                        <td>
                            @php
                                $badgeMap = [
                                    'Operasional'  => 'bg-label-warning',
                                    'Gaji'         => 'bg-label-info',
                                    'Perlengkapan' => 'bg-label-primary',
                                    'Sewa'         => 'bg-label-success',
                                ];
                                $badge = $badgeMap[$exp->expense_category] ?? 'bg-label-secondary';
                            @endphp
                            <span class="badge {{ $badge }}">{{ $exp->expense_category }}</span>
                        </td>
                        <td><span class="d-inline-block text-truncate" style="max-width: 220px;" title="{{ $exp->description }}">{{ $exp->description }}</span></td>
                        <td class="fw-bold text-danger">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                        <td>
                            @if($exp->receipt_image)
                                <a href="{{ asset('storage/' . $exp->receipt_image) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Lihat Bukti">
                                    <i class="bx bx-image"></i>
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ $exp->user?->name ?? '-' }}</small></td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="openEditModal({{ $exp->id }})"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                                    <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="openDeleteModal({{ $exp->id }}, '{{ $exp->code }}')"><i class="icon-base bx bx-trash me-1"></i> Hapus</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bx bx-info-circle fs-4 d-block mb-2"></i>
                            Belum ada data pengeluaran. Klik <strong>"Catat Pengeluaran"</strong> untuk menambah.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($expenses->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $expenses->links() }}
        </div>
        @endif
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
            <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="tanggal" class="form-label">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-12">
                            <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select id="kategori" name="expense_category" class="form-select" required>
                                <option value="" selected disabled>Pilih Kategori</option>
                                <option value="Operasional" {{ old('expense_category') == 'Operasional' ? 'selected' : '' }}>Operasional (Listrik/Air/Internet)</option>
                                <option value="Gaji" {{ old('expense_category') == 'Gaji' ? 'selected' : '' }}>Gaji Karyawan</option>
                                <option value="Sewa" {{ old('expense_category') == 'Sewa' ? 'selected' : '' }}>Sewa Toko / Ruko</option>
                                <option value="Perlengkapan" {{ old('expense_category') == 'Perlengkapan' ? 'selected' : '' }}>Perlengkapan Toko</option>
                                <option value="Transportasi" {{ old('expense_category') == 'Transportasi' ? 'selected' : '' }}>Transportasi / Ongkir</option>
                                <option value="Lainnya" {{ old('expense_category') == 'Lainnya' ? 'selected' : '' }}>Lain-lain</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="nominal" class="form-label">Nominal (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="nominal" name="amount" class="form-control" placeholder="0" min="1" value="{{ old('amount') }}" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="keterangan" class="form-label">Keterangan Lengkap <span class="text-danger">*</span></label>
                            <textarea id="keterangan" name="description" class="form-control" rows="3" placeholder="Contoh: Bayar listrik bulan ini" required>{{ old('description') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="bukti" class="form-label">Upload Bukti Struk/Nota <small class="text-muted">(Opsional, Maks 2MB)</small></label>
                            <input type="file" id="bukti" name="receipt_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Format: JPG, PNG, WebP. Maksimal 2MB.</div>
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
            <form id="editExpenseForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="edit_tanggal" class="form-label">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                            <input type="date" id="edit_tanggal" name="date" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label for="edit_kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select id="edit_kategori" name="expense_category" class="form-select" required>
                                <option value="Operasional">Operasional (Listrik/Air/Internet)</option>
                                <option value="Gaji">Gaji Karyawan</option>
                                <option value="Sewa">Sewa Toko / Ruko</option>
                                <option value="Perlengkapan">Perlengkapan Toko</option>
                                <option value="Transportasi">Transportasi / Ongkir</option>
                                <option value="Lainnya">Lain-lain</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="edit_nominal" class="form-label">Nominal (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="edit_nominal" name="amount" class="form-control" min="1" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="edit_keterangan" class="form-label">Keterangan Lengkap <span class="text-danger">*</span></label>
                            <textarea id="edit_keterangan" name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="col-12">
                            <div id="edit_bukti_preview" class="mb-2 d-none">
                                <small class="text-muted d-block mb-1">Bukti saat ini:</small>
                                <a href="#" id="edit_bukti_link" target="_blank" class="btn btn-sm btn-outline-info"><i class="bx bx-image me-1"></i>Lihat Bukti</a>
                            </div>
                            <label for="edit_bukti" class="form-label">Ganti Bukti Struk/Nota <small class="text-muted">(Opsional)</small></label>
                            <input type="file" id="edit_bukti" name="receipt_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Kosongkan jika tidak ingin mengganti bukti yang sudah ada.</div>
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
                <p class="text-muted mb-1">Apakah Anda yakin ingin menghapus pengeluaran:</p>
                <p class="fw-bold mb-4" id="delete_expense_code"></p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="deleteExpenseForm" action="" method="POST">
                        @csrf
                        @method('DELETE')
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
    /**
     * Buka modal edit — ambil data via AJAX lalu isi form
     */
    function openEditModal(id) {
        fetch(`/expenses/${id}/detail`)
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                const e = data.expense;
                document.getElementById('editExpenseForm').action = `/expenses/${id}`;
                document.getElementById('edit_tanggal').value    = e.date;
                document.getElementById('edit_kategori').value   = e.expense_category;
                document.getElementById('edit_nominal').value    = e.amount;
                document.getElementById('edit_keterangan').value = e.description;

                // Preview bukti
                const previewEl = document.getElementById('edit_bukti_preview');
                if (e.receipt_image) {
                    previewEl.classList.remove('d-none');
                    document.getElementById('edit_bukti_link').href = e.receipt_image;
                } else {
                    previewEl.classList.add('d-none');
                }

                new bootstrap.Modal(document.getElementById('editPengeluaranModal')).show();
            })
            .catch(err => {
                console.error(err);
                alert('Gagal memuat data pengeluaran.');
            });
    }

    /**
     * Buka modal konfirmasi hapus
     */
    function openDeleteModal(id, code) {
        document.getElementById('deleteExpenseForm').action  = `/expenses/${id}`;
        document.getElementById('delete_expense_code').textContent = code;
        new bootstrap.Modal(document.getElementById('hapusPengeluaranModal')).show();
    }
</script>
@endsection
