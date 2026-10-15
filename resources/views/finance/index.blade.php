@extends('layouts.app')

@section('title', 'Keuangan & Buku Kas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Keuangan /</span> Buku Kas & Arus Kas (Cash Flow)
    </h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#mutasiKasModal">
        <i class="bx bx-transfer-alt me-1"></i> Catat Mutasi Kas / Setor Bank
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

<!-- Ringkasan Saldo Riil & Arus Kas -->
<div class="row mb-4">
    <div class="col-md-6 col-xl-3 mb-3 mb-xl-0">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="content-left">
                        <span class="fw-medium d-block mb-1">Saldo Kas Riil Saat Ini</span>
                        <h4 class="card-title text-white mb-1">Rp {{ number_format($stats['current_real_balance'], 0, ',', '.') }}</h4>
                        <small class="text-white-50">Total Kas Tunai di Toko</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-white text-primary">
                            <i class="bx bx-wallet fs-4"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3 mb-xl-0">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="content-left">
                        <span class="fw-medium d-block mb-1">Pemasukan Bulan Ini</span>
                        <h4 class="card-title text-white mb-1">Rp {{ number_format($stats['this_month_inflow'], 0, ',', '.') }}</h4>
                        <small class="text-white-50">Penjualan, Hutang, Modal</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-white text-success">
                            <i class="bx bx-trending-up fs-4"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3 mb-xl-0">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="content-left">
                        <span class="fw-medium d-block mb-1">Pengeluaran Bulan Ini</span>
                        <h4 class="card-title text-white mb-1">Rp {{ number_format($stats['this_month_outflow'], 0, ',', '.') }}</h4>
                        <small class="text-white-50">Kulakan, Operasional, Biaya</small>
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
    <div class="col-md-6 col-xl-3 mb-3 mb-xl-0">
        <div class="card {{ $stats['this_month_net'] >= 0 ? 'border-success' : 'border-danger' }} h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="content-left">
                        <span class="text-muted fw-medium d-block mb-1">Arus Kas Bersih (Bulan Ini)</span>
                        <h4 class="card-title {{ $stats['this_month_net'] >= 0 ? 'text-success' : 'text-danger' }} mb-1">
                            {{ $stats['this_month_net'] >= 0 ? '+' : '' }}Rp {{ number_format($stats['this_month_net'], 0, ',', '.') }}
                        </h4>
                        <small class="text-muted">{{ $stats['this_month_net'] >= 0 ? 'Surplus Kas' : 'Defisit Kas' }}</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded {{ $stats['this_month_net'] >= 0 ? 'bg-label-success text-success' : 'bg-label-danger text-danger' }}">
                            <i class="bx {{ $stats['this_month_net'] >= 0 ? 'bx-line-chart' : 'bx-line-chart-down' }} fs-4"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Riwayat Mutasi Arus Kas (Buku Kas)</h5>
            @if(request()->anyFilled(['start_date', 'end_date', 'type', 'category', 'q']))
            <span class="badge bg-label-info">
                Filter Aktif: {{ $stats['filtered_count'] }} Transaksi | Net: Rp {{ number_format($stats['filtered_net'], 0, ',', '.') }}
            </span>
            @endif
        </div>
    </div>
    <div class="card-body mt-4">
        {{-- Form Filter --}}
        <form method="GET" action="{{ route('finance.index') }}">
            <div class="row gx-3 gy-2 align-items-end mb-4">
                <div class="col-md-2 col-sm-6">
                    <label class="form-label" for="filter-tanggal-mulai">Tanggal Mulai</label>
                    <input type="date" id="filter-tanggal-mulai" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label" for="filter-tanggal-akhir">Tanggal Akhir</label>
                    <input type="date" id="filter-tanggal-akhir" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label" for="filter-jenis">Jenis Arus</label>
                    <select id="filter-jenis" name="type" class="form-select">
                        <option value="">Semua Arus Kas</option>
                        <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Kas Masuk (+)</option>
                        <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Kas Keluar (-)</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label" for="filter-kategori">Kategori</label>
                    <select id="filter-kategori" name="category" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-8">
                    <label class="form-label" for="search-mutasi">Cari Catatan / Referensi</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" id="search-mutasi" name="q" class="form-control" placeholder="Cari transaksi..." value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-md-1 col-sm-4 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100" title="Terapkan Filter"><i class="bx bx-filter-alt"></i></button>
                    @if(request()->anyFilled(['start_date', 'end_date', 'type', 'category', 'q']))
                    <a href="{{ route('finance.index') }}" class="btn btn-outline-secondary" title="Reset Filter"><i class="bx bx-reset"></i></a>
                    @endif
                </div>
            </div>
        </form>

        <div class="table-responsive text-nowrap border rounded">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori & Referensi</th>
                        <th>Keterangan</th>
                        <th>Jenis Arus</th>
                        <th class="text-end">Nominal</th>
                        <th class="text-end">Saldo Berjalan</th>
                        <th>Dicatat Oleh</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cashflows as $cf)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $cf->date->translatedFormat('d M Y') }}</div>
                            <small class="text-muted">{{ $cf->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            @php
                                $categoryBadge = match($cf->category) {
                                    'Penjualan'         => 'bg-label-success',
                                    'Pelunasan Hutang'  => 'bg-label-info',
                                    'Pembelian'         => 'bg-label-warning',
                                    'Pengeluaran'       => 'bg-label-danger',
                                    'Setor Bank'        => 'bg-label-primary',
                                    'Tarik Bank'        => 'bg-label-secondary',
                                    'Modal Awal'        => 'bg-label-dark',
                                    default             => 'bg-label-secondary',
                                };

                                $refIcon = match($cf->reference_type) {
                                    'sale'         => 'bx-cart',
                                    'debt_payment' => 'bx-credit-card',
                                    'purchase'     => 'bx-package',
                                    'expense'      => 'bx-wallet-alt',
                                    'manual'       => 'bx-transfer-alt',
                                    default        => 'bx-receipt',
                                };
                            @endphp
                            <span class="badge {{ $categoryBadge }} mb-1">
                                <i class="bx {{ $refIcon }} me-1"></i>{{ $cf->category }}
                            </span>
                            <div>
                                <small class="text-muted text-capitalize">
                                    Sumber: {{ $cf->reference_type ?: 'manual' }}
                                </small>
                            </div>
                        </td>
                        <td>
                            <span class="d-inline-block text-truncate" style="max-width: 250px;" title="{{ $cf->notes }}">
                                {{ $cf->notes ?: '-' }}
                            </span>
                        </td>
                        <td>
                            @if($cf->type === 'in')
                                <span class="badge bg-label-success">
                                    <i class="bx bx-down-arrow-alt me-1"></i>Kas Masuk
                                </span>
                            @else
                                <span class="badge bg-label-danger">
                                    <i class="bx bx-up-arrow-alt me-1"></i>Kas Keluar
                                </span>
                            @endif
                        </td>
                        <td class="text-end fw-bold {{ $cf->type === 'in' ? 'text-success' : 'text-danger' }}">
                            {{ $cf->type === 'in' ? '+' : '-' }} Rp {{ number_format($cf->amount, 0, ',', '.') }}
                        </td>
                        <td class="text-end fw-bold">
                            Rp {{ number_format($cf->running_balance, 0, ',', '.') }}
                        </td>
                        <td>
                            <small class="text-muted">{{ $cf->user?->name ?? 'Sistem' }}</small>
                        </td>
                        <td class="text-center">
                            @if($cf->reference_type === 'manual')
                                <button type="button" class="btn btn-sm btn-icon btn-outline-danger" 
                                        onclick="openDeleteModal({{ $cf->id }}, '{{ $cf->category }}', '{{ number_format($cf->amount, 0, ',', '.') }}')" 
                                        title="Hapus Catatan Manual">
                                    <i class="bx bx-trash"></i>
                                </button>
                            @else
                                <span class="badge bg-label-secondary" data-bs-toggle="tooltip" title="Otomatis dicatat oleh modul {{ $cf->reference_type }}">
                                    <i class="bx bx-lock-alt"></i> Otomatis
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bx bx-info-circle fs-4 d-block mb-2"></i>
                            Belum ada riwayat mutasi kas yang sesuai dengan filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($cashflows->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $cashflows->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Mutasi Kas / Setor Bank -->
<div class="modal fade" id="mutasiKasModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">Catat Mutasi Kas / Setor Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('finance.store') }}" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="alert alert-info d-flex align-items-center mb-3" role="alert">
                        <i class="bx bx-info-circle fs-4 me-2"></i>
                        <div>
                            Saldo Kas Tunai Saat Ini: <strong class="text-primary">Rp {{ number_format($stats['current_real_balance'], 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="tanggal_mutasi" class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal_mutasi" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Jenis Mutasi Kas <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="form-check custom-option custom-option-basic">
                                        <label class="form-check-label custom-option-content border rounded p-2 d-flex align-items-center cursor-pointer" for="jenis_keluar">
                                            <input name="type" class="form-check-input me-2" type="radio" value="out" id="jenis_keluar" checked onchange="handleTypeChange('out')">
                                            <span class="custom-option-header">
                                                <span class="fw-semibold text-danger"><i class="bx bx-up-arrow-alt me-1"></i>Kas Keluar</span>
                                                <small class="text-muted d-block">Setor bank, prive, dll</small>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check custom-option custom-option-basic">
                                        <label class="form-check-label custom-option-content border rounded p-2 d-flex align-items-center cursor-pointer" for="jenis_masuk">
                                            <input name="type" class="form-check-input me-2" type="radio" value="in" id="jenis_masuk" onchange="handleTypeChange('in')">
                                            <span class="custom-option-header">
                                                <span class="fw-semibold text-success"><i class="bx bx-down-arrow-alt me-1"></i>Kas Masuk</span>
                                                <small class="text-muted d-block">Modal awal, setor modal</small>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="kategori_mutasi" class="form-label">Kategori / Tujuan <span class="text-danger">*</span></label>
                            <select id="kategori_mutasi" name="category" class="form-select" required>
                                <option value="Setor Bank" selected>Setor ke Rekening Bank (BCA / Mandiri / BRI / dll)</option>
                                <option value="Prive">Prive / Penarikan Pribadi Owner</option>
                                <option value="Penyesuaian Kas Fisik">Penyesuaian Kas Fisik Toko</option>
                                <option value="Lainnya">Lainnya...</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="nominal_mutasi" class="form-label">Nominal (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="nominal_mutasi" name="amount" class="form-control" placeholder="0" min="1" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="keterangan_mutasi" class="form-label">Keterangan / Catatan Tambahan</label>
                            <textarea id="keterangan_mutasi" name="notes" class="form-control" rows="2" placeholder="Contoh: Setoran uang tunai penjualan ke rekening BCA toko"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Mutasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Mutasi Kas Manual -->
<div class="modal fade" id="hapusMutasiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="avatar avatar-xl bg-label-danger mx-auto mb-4">
                    <span class="avatar-initial rounded-circle"><i class="bx bx-trash fs-2"></i></span>
                </div>
                <h5 class="fw-bold">Hapus Mutasi Kas?</h5>
                <p class="text-muted mb-1">Apakah Anda yakin ingin menghapus catatan mutasi kas:</p>
                <p class="fw-bold text-danger mb-4" id="delete_mutasi_desc"></p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="deleteMutasiForm" action="" method="POST">
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
    function handleTypeChange(type) {
        const catSelect = document.getElementById('kategori_mutasi');
        catSelect.innerHTML = '';

        if (type === 'out') {
            catSelect.innerHTML = `
                <option value="Setor Bank" selected>Setor ke Rekening Bank (BCA / Mandiri / BRI / dll)</option>
                <option value="Prive">Prive / Penarikan Pribadi Owner</option>
                <option value="Penyesuaian Kas Fisik">Penyesuaian Kas Fisik Toko</option>
                <option value="Lainnya">Lainnya...</option>
            `;
            document.getElementById('keterangan_mutasi').placeholder = 'Contoh: Setoran uang tunai penjualan ke rekening BCA toko';
        } else {
            catSelect.innerHTML = `
                <option value="Modal Awal" selected>Modal Awal Kasir / Kas Toko</option>
                <option value="Setor Modal">Setoran Tambahan Modal Owner</option>
                <option value="Tarik Bank">Penarikan Tunai dari Bank</option>
                <option value="Pendapatan Lain-lain">Pendapatan Lain-lain Non-Penjualan</option>
                <option value="Lainnya">Lainnya...</option>
            `;
            document.getElementById('keterangan_mutasi').placeholder = 'Contoh: Tambahan modal uang kembalian kasir dari owner';
        }
    }

    function openDeleteModal(id, category, amount) {
        document.getElementById('deleteMutasiForm').action = `/finance/${id}`;
        document.getElementById('delete_mutasi_desc').textContent = `${category} - Rp ${amount}`;
        new bootstrap.Modal(document.getElementById('hapusMutasiModal')).show();
    }
</script>
@endsection
