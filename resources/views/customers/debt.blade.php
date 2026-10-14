@extends('layouts.app')

@section('title', 'Daftar Hutang Pelanggan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold py-1 mb-1">
            <span class="text-muted fw-light">Pelanggan /</span> Daftar Hutang Pelanggan
        </h4>
        <p class="text-muted mb-0 small">Pantau status piutang toko, tanggal jatuh tempo, dan kelola pembayaran cicilan/pelunasan hutang.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-user-circle me-1"></i> Data Pelanggan
        </a>
        <a href="{{ route('kasir.index') }}" class="btn btn-primary">
            <i class="bx bx-cart-alt me-1"></i> Buka Kasir
        </a>
    </div>
</div>

{{-- Flash Messages --}}
@if (session('success'))
<div class="alert alert-success alert-dismissible fade show d-flex flex-wrap align-items-center justify-content-between shadow-sm gap-2" role="alert">
    <div class="d-flex align-items-center">
        <i class="bx bx-check-circle fs-4 me-2"></i>
        <div>{{ session('success') }}</div>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if(session('last_payment_id'))
            <a href="{{ route('customers.debt.receipt', session('last_payment_id')) }}" target="_blank" class="btn btn-sm btn-success shadow-xs">
                <i class="bx bx-printer me-1"></i> Cetak Bukti Pembayaran
            </a>
            <a href="{{ route('customers.debt.receipt.pdf', session('last_payment_id')) }}" class="btn btn-sm btn-outline-success">
                <i class="bx bxs-file-pdf me-1"></i> Download PDF
            </a>
        @endif
        <button type="button" class="btn-close position-static" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
@endif

@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
    <i class="bx bx-error-circle fs-4 me-2"></i>
    <div>{{ session('error') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <div class="d-flex align-items-center mb-1">
        <i class="bx bx-error-circle fs-4 me-2"></i>
        <strong>Terdapat kesalahan:</strong>
    </div>
    <ul class="mb-0 ps-4">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- ==================== METRIK KPI HUTANG PELANGGAN ==================== --}}
<div class="row g-3 mb-4">
    {{-- Total Piutang Aktif --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 shadow-sm border-0 border-start border-danger border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted fw-semibold small text-uppercase">Total Piutang Aktif</span>
                    <div class="avatar avatar-sm flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-wallet"></i></span>
                    </div>
                </div>
                <h4 class="card-title fw-bold text-danger mb-1">
                    Rp {{ number_format($stats['total_receivable'] ?? 0, 0, ',', '.') }}
                </h4>
                <div class="d-flex align-items-center text-muted small">
                    <span class="badge bg-label-danger me-1">{{ $stats['count_active'] ?? 0 }} Tagihan</span>
                    <span>perlu ditagih</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Lewat Jatuh Tempo (Overdue Alert) --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 shadow-sm border-0 border-start border-danger border-4 {{ ($stats['count_overdue'] ?? 0) > 0 ? 'bg-danger-subtle' : '' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-danger fw-bold small text-uppercase d-flex align-items-center">
                        <i class="bx bx-error-circle me-1"></i> Lewat Jatuh Tempo
                    </span>
                    <div class="avatar avatar-sm flex-shrink-0">
                        <span class="avatar-initial rounded bg-danger text-white shadow-xs"><i class="bx bx-alarm-exclamation"></i></span>
                    </div>
                </div>
                <h4 class="card-title fw-bold text-danger mb-1">
                    Rp {{ number_format($stats['total_overdue'] ?? 0, 0, ',', '.') }}
                </h4>
                <div class="d-flex align-items-center small">
                    @if(($stats['count_overdue'] ?? 0) > 0)
                        <span class="badge bg-danger text-white me-1 animate-pulse">{{ $stats['count_overdue'] }} Tagihan</span>
                        <span class="text-danger fw-semibold">Wajib diprioritaskan!</span>
                    @else
                        <span class="badge bg-label-success me-1">Aman</span>
                        <span class="text-muted">Tidak ada yang terlambat</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Hutang Dicicil Sebagian --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 shadow-sm border-0 border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted fw-semibold small text-uppercase">Dicicil Sebagian</span>
                    <div class="avatar avatar-sm flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time-five"></i></span>
                    </div>
                </div>
                <h4 class="card-title fw-bold text-warning mb-1">
                    Rp {{ number_format($stats['total_partial'] ?? 0, 0, ',', '.') }}
                </h4>
                <div class="d-flex align-items-center text-muted small">
                    <span class="badge bg-label-warning me-1">{{ $stats['count_partial'] ?? 0 }} Tagihan</span>
                    <span>sudah ada cicilan masuk</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Piutang Sudah Lunas --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 shadow-sm border-0 border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted fw-semibold small text-uppercase">Total Piutang Lunas</span>
                    <div class="avatar avatar-sm flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-double"></i></span>
                    </div>
                </div>
                <h4 class="card-title fw-bold text-success mb-1">
                    Rp {{ number_format($stats['total_paid'] ?? 0, 0, ',', '.') }}
                </h4>
                <div class="d-flex align-items-center text-muted small">
                    <span class="badge bg-label-success me-1">{{ $stats['count_paid'] ?? 0 }} Tagihan</span>
                    <span>berhasil diselesaikan</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ==================== NAVIGASI TAB STATUS & FILTER ==================== --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body pb-3">
        {{-- Quick Status Filter Tabs --}}
        <div class="d-flex flex-wrap gap-2 mb-3 border-bottom pb-3">
            <a href="{{ route('customers.debt', array_merge(request()->except(['status', 'page']), ['status' => 'active'])) }}" 
               class="btn btn-sm {{ ($status ?? 'active') === 'active' ? 'btn-primary' : 'btn-outline-secondary' }}">
                <i class="bx bx-list-ul me-1"></i> Tagihan Aktif
                <span class="badge bg-white text-primary ms-1">{{ $stats['count_active'] ?? 0 }}</span>
            </a>

            <a href="{{ route('customers.debt', array_merge(request()->except(['status', 'page']), ['status' => 'overdue'])) }}" 
               class="btn btn-sm {{ ($status ?? '') === 'overdue' ? 'btn-danger' : 'btn-outline-danger' }}">
                <i class="bx bx-error-circle me-1"></i> Lewat Jatuh Tempo
                <span class="badge {{ ($status ?? '') === 'overdue' ? 'bg-white text-danger' : 'bg-danger text-white' }} ms-1">
                    {{ $stats['count_overdue'] ?? 0 }}
                </span>
            </a>

            <a href="{{ route('customers.debt', array_merge(request()->except(['status', 'page']), ['status' => 'unpaid'])) }}" 
               class="btn btn-sm {{ ($status ?? '') === 'unpaid' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                <i class="bx bx-x-circle me-1"></i> Belum Lunas
                <span class="badge bg-label-secondary ms-1">{{ $stats['count_unpaid'] ?? 0 }}</span>
            </a>

            <a href="{{ route('customers.debt', array_merge(request()->except(['status', 'page']), ['status' => 'partial'])) }}" 
               class="btn btn-sm {{ ($status ?? '') === 'partial' ? 'btn-warning' : 'btn-outline-warning' }}">
                <i class="bx bx-time me-1"></i> Sebagian (Dicicil)
                <span class="badge bg-label-warning ms-1">{{ $stats['count_partial'] ?? 0 }}</span>
            </a>

            <a href="{{ route('customers.debt', array_merge(request()->except(['status', 'page']), ['status' => 'paid'])) }}" 
               class="btn btn-sm {{ ($status ?? '') === 'paid' ? 'btn-success' : 'btn-outline-success' }}">
                <i class="bx bx-check me-1"></i> Sudah Lunas
                <span class="badge bg-label-success ms-1">{{ $stats['count_paid'] ?? 0 }}</span>
            </a>

            <a href="{{ route('customers.debt', array_merge(request()->except(['status', 'page']), ['status' => 'all'])) }}" 
               class="btn btn-sm {{ ($status ?? '') === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">
                <i class="bx bx-grid-alt me-1"></i> Semua Riwayat
            </a>
        </div>

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('customers.debt') }}">
            <input type="hidden" name="status" value="{{ $status ?? 'active' }}">
            <div class="row g-2 align-items-end">
                {{-- Search Keyword --}}
                <div class="col-md-3">
                    <label class="form-label small fw-semibold" for="filter-search">Pencarian</label>
                    <div class="input-group input-group-merge input-group-sm">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" 
                               id="filter-search" 
                               name="search" 
                               class="form-control" 
                               placeholder="Nama / no HP / no faktur..." 
                               value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Filter Pelanggan --}}
                <div class="col-md-3">
                    <label class="form-label small fw-semibold" for="filter-customer">Pelanggan</label>
                    <select id="filter-customer" name="customer_id" class="form-select form-select-sm">
                        <option value="">Semua Pelanggan</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} {{ $c->total_debt > 0 ? '(Rp ' . number_format($c->total_debt, 0, ',', '.') . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Peringatan Jatuh Tempo --}}
                <div class="col-md-2">
                    <label class="form-label small fw-semibold" for="filter-due-status">Peringatan Jatuh Tempo</label>
                    <select id="filter-due-status" name="due_status" class="form-select form-select-sm">
                        <option value="">Semua Tanggal</option>
                        <option value="overdue" {{ request('due_status') === 'overdue' ? 'selected' : '' }}>🚨 Lewat Jatuh Tempo</option>
                        <option value="today" {{ request('due_status') === 'today' ? 'selected' : '' }}>⏰ Jatuh Tempo Hari Ini</option>
                        <option value="upcoming_3" {{ request('due_status') === 'upcoming_3' ? 'selected' : '' }}>⚠️ Tenggat 3 Hari</option>
                        <option value="upcoming_7" {{ request('due_status') === 'upcoming_7' ? 'selected' : '' }}>📅 Tenggat 7 Hari</option>
                    </select>
                </div>

                {{-- Filter Urutan --}}
                <div class="col-md-2">
                    <label class="form-label small fw-semibold" for="filter-sort">Urutkan</label>
                    <select id="filter-sort" name="sort" class="form-select form-select-sm">
                        <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Paling Baru Dibuat</option>
                        <option value="due_date_asc" {{ request('sort') === 'due_date_asc' ? 'selected' : '' }}>Jatuh Tempo Terdekat</option>
                        <option value="due_date_desc" {{ request('sort') === 'due_date_desc' ? 'selected' : '' }}>Jatuh Tempo Terjauh</option>
                        <option value="remaining_desc" {{ request('sort') === 'remaining_desc' ? 'selected' : '' }}>Sisa Hutang Terbesar</option>
                        <option value="remaining_asc" {{ request('sort') === 'remaining_asc' ? 'selected' : '' }}>Sisa Hutang Terkecil</option>
                    </select>
                </div>

                {{-- Tombol Aksi Filter --}}
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bx bx-filter-alt me-1"></i> Terapkan
                    </button>
                    @if(request()->hasAny(['search', 'customer_id', 'due_status', 'sort', 'start_date', 'end_date']))
                        <a href="{{ route('customers.debt', ['status' => $status ?? 'active']) }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                            <i class="bx bx-refresh"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ==================== TABEL DAFTAR HUTANG & JATUH TEMPO ==================== --}}
<div class="card shadow-sm border-0">
    <div class="card-header border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="card-title mb-0 d-flex align-items-center">
            <i class="bx bx-list-check me-2 text-primary fs-4"></i> Rincian Tagihan Hutang & Jatuh Tempo
        </h5>
        <div class="text-muted small">
            Menampilkan <strong>{{ $debts->total() }}</strong> transaksi piutang
        </div>
    </div>
    
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Pelanggan</th>
                    <th>No. Transaksi</th>
                    <th>Tgl Transaksi</th>
                    <th>Tanggal Jatuh Tempo & Peringatan</th>
                    <th>Total Hutang</th>
                    <th>Sudah Dibayar</th>
                    <th>Sisa Tagihan</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($debts as $index => $debt)
                @php
                    $isOverdue = $debt->is_overdue;
                    $isDueToday = $debt->is_due_today;
                    $isDueSoon = $debt->is_due_soon;
                    $isPaid = $debt->status === 'paid';
                    $paymentsCount = $debt->payments->count();
                @endphp
                <tr class="{{ $isOverdue ? 'table-danger-subtle' : '' }}">
                    <td>{{ $debts->firstItem() + $index }}</td>
                    
                    {{-- Pelanggan --}}
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3 bg-label-primary rounded-circle d-flex align-items-center justify-content-center shadow-xs">
                                <span class="fw-bold">{{ strtoupper(substr($debt->customer->name ?? 'P', 0, 1)) }}</span>
                            </div>
                            <div>
                                <span class="fw-bold text-heading d-block">{{ $debt->customer->name ?? 'Pelanggan Umum' }}</span>
                                <small class="text-muted">
                                    <i class="bx bx-phone me-1"></i>{{ $debt->customer->phone ?: '-' }}
                                </small>
                            </div>
                        </div>
                    </td>

                    {{-- No Transaksi --}}
                    <td>
                        @if($debt->sale)
                            <a href="{{ route('transactions.show', $debt->sale_id) }}" class="fw-semibold font-monospace text-primary text-decoration-none">
                                {{ $debt->sale->invoice_number }}
                            </a>
                        @else
                            <span class="text-muted font-monospace">TRX-MANUAL-{{ $debt->id }}</span>
                        @endif
                    </td>

                    {{-- Tanggal Transaksi --}}
                    <td>
                        <span class="text-muted">
                            {{ $debt->created_at ? $debt->created_at->format('d/m/Y') : '-' }}
                        </span>
                    </td>

                    {{-- Tanggal Jatuh Tempo & Indikator Peringatan Keterlambatan --}}
                    <td>
                        @if($isPaid)
                            <span class="badge bg-label-success">
                                <i class="bx bx-check-circle me-1"></i> Lunas
                            </span>
                        @elseif($debt->due_date)
                            @if($isOverdue)
                                <div class="d-inline-flex flex-column align-items-start">
                                    <span class="fw-bold text-danger">
                                        <i class="bx bx-calendar-x me-1"></i>{{ $debt->due_date->format('d M Y') }}
                                    </span>
                                    <span class="badge bg-danger text-white mt-1 shadow-xs">
                                        <i class="bx bx-error-circle me-1"></i> Terlambat {{ $debt->days_overdue }} hari
                                    </span>
                                </div>
                            @elseif($isDueToday)
                                <div class="d-inline-flex flex-column align-items-start">
                                    <span class="fw-bold text-warning">
                                        <i class="bx bx-alarm-exclamation me-1"></i>{{ $debt->due_date->format('d M Y') }}
                                    </span>
                                    <span class="badge bg-warning text-dark mt-1">
                                        <i class="bx bx-bell me-1"></i> Jatuh Tempo Hari Ini!
                                    </span>
                                </div>
                            @elseif($isDueSoon)
                                <div class="d-inline-flex flex-column align-items-start">
                                    <span class="fw-semibold text-info">
                                        <i class="bx bx-calendar-event me-1"></i>{{ $debt->due_date->format('d M Y') }}
                                    </span>
                                    <span class="badge bg-label-info mt-1">
                                        <i class="bx bx-time me-1"></i> Sisa {{ $debt->days_remaining }} hari
                                    </span>
                                </div>
                            @else
                                <div class="d-inline-flex flex-column align-items-start">
                                    <span class="text-body fw-medium">
                                        <i class="bx bx-calendar me-1 text-muted"></i>{{ $debt->due_date->format('d M Y') }}
                                    </span>
                                    <small class="text-muted mt-1">
                                        Sisa {{ $debt->days_remaining }} hari
                                    </small>
                                </div>
                            @endif
                        @else
                            <span class="text-muted small">Tanpa Jatuh Tempo</span>
                        @endif
                    </td>

                    {{-- Total Hutang --}}
                    <td>
                        <span class="fw-semibold">Rp {{ number_format($debt->total_debt, 0, ',', '.') }}</span>
                    </td>

                    {{-- Sudah Dibayar & Progress --}}
                    <td>
                        <div style="min-width: 120px;">
                            <span class="small fw-semibold text-success">
                                Rp {{ number_format($debt->paid_amount, 0, ',', '.') }}
                            </span>
                            <div class="progress mt-1" style="height: 5px;">
                                <div class="progress-bar {{ $isPaid ? 'bg-success' : 'bg-primary' }}" 
                                     role="progressbar" 
                                     style="width: {{ $debt->payment_percentage }}%;" 
                                     aria-valuenow="{{ $debt->payment_percentage }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted" style="font-size: 11px;">{{ $debt->payment_percentage }}% terbayar</small>
                        </div>
                    </td>

                    {{-- Sisa Tagihan --}}
                    <td>
                        @if($isPaid)
                            <span class="badge bg-label-success">Rp 0 (Lunas)</span>
                        @else
                            <span class="fw-bold fs-6 {{ $isOverdue ? 'text-danger' : 'text-primary' }}">
                                Rp {{ number_format($debt->remaining_debt, 0, ',', '.') }}
                            </span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td>
                        @if($isPaid)
                            <span class="badge bg-label-success">
                                <i class="bx bx-check me-1"></i> Lunas
                            </span>
                        @elseif($debt->status === 'partial')
                            <span class="badge bg-label-warning">
                                <i class="bx bx-time-five me-1"></i> Dicicil
                            </span>
                        @else
                            <span class="badge bg-label-danger">
                                <i class="bx bx-x-circle me-1"></i> Belum Lunas
                            </span>
                        @endif
                    </td>

                    {{-- Aksi --}}
                    <td class="text-center">
                        <div class="d-flex justify-content-center align-items-center gap-1">
                            @if(!$isPaid)
                                <button type="button" 
                                        class="btn btn-sm btn-success d-inline-flex align-items-center shadow-xs" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#bayarHutangModal" 
                                        onclick="setupPaymentModal('{{ $debt->id }}', '{{ addslashes($debt->customer->name ?? 'Pelanggan') }}', '{{ addslashes($debt->sale ? $debt->sale->invoice_number : ('TRX-' . $debt->id)) }}', {{ $debt->total_debt }}, {{ $debt->paid_amount }}, {{ $debt->remaining_debt }})">
                                    <i class="bx bx-money me-1"></i> Bayar
                                </button>
                            @endif

                            {{-- Tombol Riwayat Pembayaran / Cetak Kuitansi Terakhir --}}
                            @if($paymentsCount > 0)
                                <button type="button" 
                                        class="btn btn-sm btn-outline-info shadow-xs" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalRiwayat-{{ $debt->id }}" 
                                        title="Lihat riwayat pembayaran cicilan ({{ $paymentsCount }} kali)">
                                    <i class="bx bx-history"></i>
                                </button>
                            @elseif($isPaid)
                                <span class="badge bg-label-success"><i class="bx bx-check"></i> Selesai</span>
                            @endif
                        </div>
                    </td>
                </tr>

                {{-- Modal Riwayat Cicilan / Pembayaran per Tagihan --}}
                @if($paymentsCount > 0)
                <div class="modal fade" id="modalRiwayat-{{ $debt->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-light border-bottom pb-3">
                                <h5 class="modal-title fw-bold d-flex align-items-center">
                                    <i class="bx bx-history text-info me-2 fs-4"></i> Riwayat Pembayaran Tagihan: {{ $debt->sale ? $debt->sale->invoice_number : ('TRX-' . $debt->id) }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row g-3 mb-3 bg-light p-3 rounded border">
                                    <div class="col-sm-4">
                                        <small class="text-muted d-block">Pelanggan:</small>
                                        <span class="fw-bold">{{ $debt->customer->name ?? 'Pelanggan Umum' }}</span>
                                    </div>
                                    <div class="col-sm-4">
                                        <small class="text-muted d-block">Total Hutang Awal:</small>
                                        <span class="fw-bold">Rp {{ number_format($debt->total_debt, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="col-sm-4">
                                        <small class="text-muted d-block">Sisa Hutang Saat Ini:</small>
                                        <span class="fw-bold {{ $debt->remaining_debt <= 0 ? 'text-success' : 'text-danger' }}">
                                            Rp {{ number_format($debt->remaining_debt, 0, ',', '.') }}
                                            ({{ $debt->remaining_debt <= 0 ? 'LUNAS' : 'Belum Lunas' }})
                                        </span>
                                    </div>
                                </div>

                                <div class="table-responsive text-nowrap border rounded">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 40px;">#</th>
                                                <th>No. Kuitansi</th>
                                                <th>Tanggal & Waktu</th>
                                                <th>Kasir</th>
                                                <th>Metode</th>
                                                <th class="text-end">Nominal Bayar</th>
                                                <th>Catatan</th>
                                                <th class="text-center" style="width: 100px;">Cetak Bukti</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($debt->payments->sortByDesc('id') as $pIdx => $pay)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <span class="font-monospace fw-bold text-primary">KW-{{ str_pad($pay->id, 5, '0', STR_PAD_LEFT) }}</span>
                                                </td>
                                                <td>{{ $pay->payment_date->format('d/m/Y') }} <small class="text-muted">{{ $pay->created_at ? $pay->created_at->format('H:i') : '' }}</small></td>
                                                <td>{{ $pay->user?->name ?? 'Kasir' }}</td>
                                                <td>
                                                    <span class="badge bg-label-primary text-uppercase">{{ $pay->payment_method }}</span>
                                                </td>
                                                <td class="text-end fw-bold text-success">
                                                    Rp {{ number_format($pay->amount, 0, ',', '.') }}
                                                </td>
                                                <td><small class="text-muted">{{ $pay->note ?: '-' }}</small></td>
                                                <td class="text-center">
                                                    <a href="{{ route('customers.debt.receipt', $pay->id) }}" target="_blank" class="btn btn-xs btn-outline-primary" title="Cetak Kuitansi Thermal">
                                                        <i class="bx bx-printer"></i> Struk
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer border-top pt-3">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @empty
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bx bx-check-circle fs-1 d-block mb-2 text-success"></i>
                            <h6 class="fw-bold mb-1">Tidak Ada Data Hutang Ditemukan</h6>
                            <p class="small mb-3">Tidak ada catatan hutang yang cocok dengan kriteria filter saat ini.</p>
                            @if(request()->hasAny(['search', 'customer_id', 'due_status', 'sort', 'status']))
                                <a href="{{ route('customers.debt') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-refresh me-1"></i> Reset Filter
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($debts->hasPages())
    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center py-3 border-top">
        <small class="text-muted">
            Menampilkan {{ $debts->firstItem() }} sampai {{ $debts->lastItem() }} dari {{ $debts->total() }} tagihan
        </small>
        <div>
            {{ $debts->links() }}
        </div>
    </div>
    @endif
</div>

{{-- ==================== MODAL PEMBAYARAN HUTANG ==================== --}}
<div class="modal fade" id="bayarHutangModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom pb-3">
                <h5 class="modal-title fw-bold">
                    <i class="bx bx-wallet me-1 text-success"></i> Terima Pembayaran Hutang
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formBayarHutang" action="" method="POST" onsubmit="return validatePaymentForm()">
                @csrf
                <div class="modal-body py-4">
                    {{-- Detail Tagihan --}}
                    <div class="bg-light p-3 rounded mb-3 border">
                        <div class="row mb-2">
                            <div class="col-sm-5 text-muted small">Nama Pelanggan:</div>
                            <div class="col-sm-7 fw-bold" id="modal-cust-name">-</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-5 text-muted small">No. Transaksi:</div>
                            <div class="col-sm-7 fw-semibold font-monospace" id="modal-trx">-</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-5 text-muted small">Total Hutang Awal:</div>
                            <div class="col-sm-7 fw-semibold text-secondary" id="modal-total-debt">-</div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 text-muted small">Sisa Hutang Saat Ini:</div>
                            <div class="col-sm-7 fw-bold text-danger fs-6" id="modal-debt-text">-</div>
                        </div>
                    </div>
                    
                    {{-- Input Nominal Pembayaran --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="bayar_amount" class="form-label fw-bold mb-0">Jumlah Pembayaran <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-xs btn-outline-success fw-bold" onclick="setFullPayment()">
                                <i class="bx bx-check-double me-1"></i> Bayar Lunas
                            </button>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text fw-bold">Rp</span>
                            <input type="number" 
                                   id="bayar_amount" 
                                   name="amount" 
                                   class="form-control form-control-lg fw-bold text-end" 
                                   min="1" 
                                   step="any" 
                                   oninput="calculateRemainingDebt()"
                                   required />
                        </div>
                        
                        {{-- Tombol Nominal Cepat --}}
                        <div class="d-flex flex-wrap gap-1 mt-2" id="quick-nominal-buttons">
                            <!-- Tombol nominal cepat diisi oleh JavaScript -->
                        </div>

                        {{-- Realtime Feedback Sisa Hutang Setelah Bayar --}}
                        <div class="mt-2 p-2 rounded bg-light border small d-flex justify-content-between align-items-center" id="box-remaining-preview">
                            <span class="text-muted">Sisa tagihan setelah pembayaran ini:</span>
                            <span class="fw-bold text-dark" id="text-remaining-after">-</span>
                        </div>
                    </div>

                    {{-- Tanggal Pembayaran --}}
                    <div class="mb-3">
                        <label for="payment_date" class="form-label fw-semibold">Tanggal Pembayaran <span class="text-danger">*</span></label>
                        <input type="date" 
                               id="payment_date" 
                               name="payment_date" 
                               class="form-control" 
                               value="{{ date('Y-m-d') }}" 
                               required />
                    </div>

                    {{-- Metode Pembayaran --}}
                    <div class="mb-3">
                        <label for="payment_method" class="form-label fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="cash" selected>Tunai (Cash)</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>

                    {{-- Catatan Opsional --}}
                    <div class="mb-0">
                        <label for="note" class="form-label">Catatan Pembayaran (Opsional)</label>
                        <input type="text" id="note" name="note" class="form-control" placeholder="Contoh: Cicilan ke-1 / Titipan tunai" />
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btn-submit-payment" class="btn btn-success px-4">
                        <i class="bx bx-check me-1"></i> Proses Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentDebtRemaining = 0;

    function setupPaymentModal(debtId, customer, trx, totalDebt, paidAmount, debtRemaining) {
        currentDebtRemaining = Number(debtRemaining);
        const form = document.getElementById('formBayarHutang');
        form.action = `{{ url('customers/debt') }}/${debtId}/pay`;
        
        document.getElementById('modal-cust-name').innerText = customer;
        document.getElementById('modal-trx').innerText = trx;
        document.getElementById('modal-total-debt').innerText = 'Rp ' + Number(totalDebt).toLocaleString('id-ID');
        document.getElementById('modal-debt-text').innerText = 'Rp ' + Number(debtRemaining).toLocaleString('id-ID');
        
        const amountInput = document.getElementById('bayar_amount');
        amountInput.value = debtRemaining;
        amountInput.max = debtRemaining;

        // Render tombol nominal cepat
        renderQuickButtons(currentDebtRemaining);
        calculateRemainingDebt();
    }

    function setFullPayment() {
        const amountInput = document.getElementById('bayar_amount');
        amountInput.value = currentDebtRemaining;
        calculateRemainingDebt();
    }

    function setQuickAmount(val) {
        const amountInput = document.getElementById('bayar_amount');
        amountInput.value = Math.min(val, currentDebtRemaining);
        calculateRemainingDebt();
    }

    function renderQuickButtons(maxRemaining) {
        const container = document.getElementById('quick-nominal-buttons');
        container.innerHTML = '';

        const nominalPresets = [20000, 50000, 100000, 200000, 500000];
        nominalPresets.forEach(nom => {
            if (nom < maxRemaining) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-xs btn-outline-secondary';
                btn.innerText = 'Rp ' + (nom / 1000).toLocaleString('id-ID') + 'k';
                btn.onclick = () => setQuickAmount(nom);
                container.appendChild(btn);
            }
        });
    }

    function calculateRemainingDebt() {
        const amountInput = document.getElementById('bayar_amount');
        const submitBtn = document.getElementById('btn-submit-payment');
        const textRemaining = document.getElementById('text-remaining-after');
        const val = parseFloat(amountInput.value) || 0;

        const remainingAfter = Math.max(0, currentDebtRemaining - val);

        if (val > currentDebtRemaining) {
            textRemaining.innerHTML = '<span class="text-danger fw-bold"><i class="bx bx-error-circle"></i> Melebihi sisa hutang!</span>';
            submitBtn.disabled = true;
        } else if (remainingAfter === 0 && val > 0) {
            textRemaining.innerHTML = '<span class="text-success fw-bold"><i class="bx bx-check-circle"></i> Tagihan akan LUNAS!</span>';
            submitBtn.disabled = false;
        } else {
            textRemaining.innerHTML = 'Rp ' + remainingAfter.toLocaleString('id-ID');
            submitBtn.disabled = val <= 0;
        }
    }

    function validatePaymentForm() {
        const amountInput = document.getElementById('bayar_amount');
        const val = parseFloat(amountInput.value) || 0;

        if (val <= 0) {
            alert('Nominal pembayaran minimal Rp 1.');
            return false;
        }

        if (val > currentDebtRemaining) {
            alert('Nominal pembayaran tidak boleh melebihi sisa hutang (Rp ' + currentDebtRemaining.toLocaleString('id-ID') + ').');
            return false;
        }

        return true;
    }
</script>
@endpush
