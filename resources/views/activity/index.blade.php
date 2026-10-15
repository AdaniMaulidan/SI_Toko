@extends('layouts.app')

@section('title', 'Riwayat Aktivitas & Audit Trail')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-1">
            <span class="text-muted fw-light">Sistem /</span> Riwayat Aktivitas & Audit Trail
        </h4>
        <p class="text-muted mb-0 small">Log jejak digital aktivitas pengguna, sesi login, modifikasi data, dan pemeliharaan sistem.</p>
    </div>
</div>

{{-- Top Statistics Metric Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-primary rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-history fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Aktivitas Hari Ini</span>
                    <h4 class="card-title mb-0 fw-bold">{{ number_format($stats['total_today']) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-info rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-log-in-circle fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Sesi Login Hari Ini</span>
                    <h4 class="card-title mb-0 fw-bold text-info">{{ number_format($stats['logins_today']) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-warning rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-edit fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Modifikasi & Opname</span>
                    <h4 class="card-title mb-0 fw-bold text-warning">{{ number_format($stats['adjustments_today']) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-danger rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-trash fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Penghapusan Hari Ini</span>
                    <h4 class="card-title mb-0 fw-bold text-danger">{{ number_format($stats['deletions_today']) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Card --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('activity.index') }}" class="row gx-3 gy-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label small fw-semibold">Filter Tanggal</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label small fw-semibold">Pengguna (User)</label>
                <select name="user_id" class="form-select">
                    <option value="all">Semua Pengguna</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ ucfirst($u->role) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label small fw-semibold">Jenis Aktivitas</label>
                <select name="action" class="form-select">
                    <option value="all">Semua Jenis Aksi</option>
                    <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Sesi Login</option>
                    <option value="logout" {{ request('action') === 'logout' ? 'selected' : '' }}>Sesi Logout</option>
                    <option value="adjustment" {{ request('action') === 'adjustment' ? 'selected' : '' }}>Penyesuaian Stok (Opname)</option>
                    <option value="settings" {{ request('action') === 'settings' ? 'selected' : '' }}>Pengaturan Toko</option>
                    <option value="backup" {{ request('action') === 'backup' ? 'selected' : '' }}>Backup Database</option>
                    <option value="restore" {{ request('action') === 'restore' ? 'selected' : '' }}>Restore Database</option>
                    <option value="import" {{ request('action') === 'import' ? 'selected' : '' }}>Import Data</option>
                    <option value="export" {{ request('action') === 'export' ? 'selected' : '' }}>Export Data</option>
                    <option value="delete" {{ request('action') === 'delete' ? 'selected' : '' }}>Penghapusan Data</option>
                    <option value="update" {{ request('action') === 'update' ? 'selected' : '' }}>Pembaruan Data</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bx bx-filter-alt me-1"></i> Filter
                </button>
                @if(request()->hasAny(['date', 'user_id', 'action', 'search']))
                <a href="{{ route('activity.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                    <i class="bx bx-reset"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Audit Trail Timeline Card --}}
<div class="card shadow-sm">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0">Linimasa Aktivitas (Timeline)</h5>
            <small class="text-muted">Menampilkan riwayat pencatatan otomatis sistem secara kronologis</small>
        </div>
        <span class="badge bg-label-primary">Total: {{ $activities->total() }} Log</span>
    </div>
    <div class="card-body mt-4">
        @if($activities->count() > 0)
        <ul class="timeline mt-2 mb-0">
            @foreach($activities as $index => $act)
            @php
                $pointClass = match(strtolower($act->action)) {
                    'delete'     => 'timeline-point-danger',
                    'login'      => 'timeline-point-info',
                    'logout'     => 'timeline-point-secondary',
                    'adjustment' => 'timeline-point-warning',
                    'settings'   => 'timeline-point-success',
                    'backup'     => 'timeline-point-primary',
                    'restore'    => 'timeline-point-danger',
                    'import'     => 'timeline-point-success',
                    'export'     => 'timeline-point-info',
                    'create'     => 'timeline-point-success',
                    'update'     => 'timeline-point-primary',
                    default      => 'timeline-point-secondary',
                };

                $badgeClass = match(strtolower($act->action)) {
                    'delete'     => 'bg-label-danger',
                    'login'      => 'bg-label-info',
                    'logout'     => 'bg-label-secondary',
                    'adjustment' => 'bg-label-warning',
                    'settings'   => 'bg-label-success',
                    'backup'     => 'bg-label-primary',
                    'restore'    => 'bg-label-danger',
                    'import'     => 'bg-label-success',
                    'export'     => 'bg-label-info',
                    'create'     => 'bg-label-success',
                    'update'     => 'bg-label-primary',
                    default      => 'bg-label-secondary',
                };

                $isLast = $loop->last;
            @endphp
            <li class="timeline-item timeline-item-transparent ps-4 {{ $isLast ? 'border-0' : '' }}">
                <span class="timeline-point {{ $pointClass }}"></span>
                <div class="timeline-event pb-3">
                    <div class="timeline-header mb-1 d-flex flex-wrap justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $badgeClass }} text-uppercase fw-bold font-monospace">
                                {{ $act->action }}
                            </span>
                            <h6 class="mb-0 fw-bold text-heading">
                                {{ ucfirst($act->action) }}
                            </h6>
                        </div>
                        <div class="text-muted small">
                            <i class="bx bx-time me-1"></i>
                            <strong>{{ $act->created_at->format('H:i') }} WIB</strong> 
                            ({{ $act->created_at->diffForHumans() }})
                            <span class="badge bg-light text-muted ms-1">{{ $act->created_at->translatedFormat('d M Y') }}</span>
                        </div>
                    </div>
                    
                    <p class="mb-2 text-body">
                        {{ $act->description }}
                    </p>

                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-1 border-top border-light">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-xs me-2">
                                <span class="avatar-initial rounded-circle bg-label-primary font-monospace small">
                                    {{ strtoupper(substr($act->user->name ?? 'S', 0, 1)) }}
                                </span>
                            </div>
                            <small class="text-muted">
                                Oleh: <strong class="text-heading">{{ $act->user->name ?? 'Sistem' }}</strong> 
                                @if($act->user)
                                    <span class="badge bg-label-secondary ms-1">{{ ucfirst($act->user->role) }}</span>
                                @endif
                            </small>
                        </div>
                        @if($act->ip_address)
                        <small class="text-muted font-monospace">
                            <i class="bx bx-laptop me-1"></i>IP: {{ $act->ip_address }}
                        </small>
                        @endif
                    </div>
                </div>
            </li>
            @endforeach
        </ul>

        @if($activities->hasPages())
        <div class="border-top pt-4 mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted">
                Menampilkan log ke {{ $activities->firstItem() }} sampai {{ $activities->lastItem() }} dari {{ $activities->total() }} entri
            </small>
            <div>
                {{ $activities->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif

        @else
        <div class="text-center py-5">
            <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3">
                <i class="bx bx-history fs-1"></i>
            </div>
            <h6 class="fw-bold mb-1">Belum Ada Aktivitas Tercatat</h6>
            <p class="text-muted small mb-3">
                @if(request()->hasAny(['date', 'user_id', 'action', 'search']))
                    Tidak ada aktivitas yang cocok dengan kriteria filter saat ini.
                @else
                    Aktivitas sistem, sesi login, dan perubahan data otomatis tercatat di sini saat pengguna berinteraksi dengan aplikasi.
                @endif
            </p>
            @if(request()->hasAny(['date', 'user_id', 'action', 'search']))
            <a href="{{ route('activity.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-reset me-1"></i> Reset Filter
            </a>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
