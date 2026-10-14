@extends('layouts.app')

@section('title', 'Manajemen Pelanggan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-1">
            <span class="text-muted fw-light">Master Data /</span> Pelanggan
        </h4>
        <p class="text-muted mb-0 small">Kelola data pelanggan member, kontak WhatsApp, dan pantau saldo piutang/hutang.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('customers.debt') }}" class="btn btn-outline-danger position-relative">
            <i class="bx bx-wallet me-1"></i> Data Hutang Pelanggan
            @if($stats['with_debt'] > 0)
                <span class="badge bg-danger ms-1">{{ $stats['with_debt'] }}</span>
            @endif
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahPelangganModal">
            <i class="bx bx-plus me-1"></i> Tambah Pelanggan
        </button>
    </div>
</div>

{{-- Flash Messages --}}
@if (session('success'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
    <i class="bx bx-check-circle fs-4 me-2"></i>
    <div>{{ session('success') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
    <i class="bx bx-error-circle fs-4 me-2"></i>
    <div>{{ session('error') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center mb-1">
        <i class="bx bx-error-circle fs-4 me-2"></i>
        <strong>Terdapat kesalahan pengisian data:</strong>
    </div>
    <ul class="mb-0 ps-4">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-primary rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-user fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Total Pelanggan</span>
                    <h4 class="card-title mb-0 fw-bold">{{ number_format($stats['total']) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-warning rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-user-x fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Memiliki Hutang</span>
                    <h4 class="card-title mb-0 fw-bold text-warning">{{ number_format($stats['with_debt']) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-danger rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-wallet fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Total Piutang Toko</span>
                    <h4 class="card-title mb-0 fw-bold text-danger">Rp {{ number_format($stats['total_debt_sum'], 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-success rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-cart fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Total Belanja Member</span>
                    <h4 class="card-title mb-0 fw-bold text-success">{{ number_format($stats['total_sales']) }} trx</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('customers.index') }}" class="row gx-3 gy-2 align-items-end">
            <div class="col-md-4 col-sm-6">
                <label class="form-label" for="debt_status">Status Hutang / Piutang</label>
                <select name="debt_status" id="debt_status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Pelanggan</option>
                    <option value="has_debt" {{ request('debt_status') == 'has_debt' ? 'selected' : '' }}>Ada Saldo Hutang</option>
                    <option value="no_debt" {{ request('debt_status') == 'no_debt' ? 'selected' : '' }}>Lunas / Tanpa Hutang</option>
                </select>
            </div>
            <div class="col-md-5 col-sm-6">
                <label class="form-label" for="search">Cari Pelanggan</label>
                <div class="input-group">
                    <input type="text" 
                           name="search" 
                           id="search" 
                           class="form-control" 
                           placeholder="Cari nama, nomor HP/WA, alamat..." 
                           value="{{ request('search') }}">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bx bx-search"></i>
                    </button>
                    @if(request()->hasAny(['search', 'debt_status']))
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary" title="Reset Pencarian">
                            <i class="bx bx-refresh"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card shadow-sm border-0">
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Nama Pelanggan</th>
                    <th>Kontak / WA</th>
                    <th>Alamat</th>
                    <th>Transaksi</th>
                    <th>Saldo Hutang</th>
                    <th class="text-center" style="width: 80px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $index => $customer)
                <tr>
                    <td>{{ $customers->firstItem() + $index }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3 bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                                <span class="fw-bold">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                            </div>
                            <div>
                                <span class="fw-bold text-heading d-block">{{ $customer->name }}</span>
                                @if($customer->email)
                                    <small class="text-muted"><i class="bx bx-envelope me-1"></i>{{ $customer->email }}</small>
                                @else
                                    <small class="text-muted">ID: #CUST-{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($customer->phone)
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', $customer->phone);
                                if (str_starts_with($cleanPhone, '0')) {
                                    $waNumber = '62' . substr($cleanPhone, 1);
                                } else {
                                    $waNumber = $cleanPhone;
                                }
                            @endphp
                            <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="text-success fw-semibold small text-decoration-none" title="Chat via WhatsApp">
                                <i class="bx bxl-whatsapp fs-6 me-1"></i>{{ $customer->phone }}
                            </a>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="d-inline-block text-truncate" style="max-width: 200px;" title="{{ $customer->address }}">
                            {{ $customer->address ?: '-' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-label-info">{{ $customer->sales_count }} kali</span>
                    </td>
                    <td>
                        @if($customer->total_debt > 0)
                            <a href="{{ route('customers.debt', ['search' => $customer->name]) }}" class="badge bg-label-danger text-danger fw-bold d-inline-flex align-items-center text-decoration-none" title="Klik untuk lihat rincian invoice hutang">
                                <i class="bx bx-error-circle me-1"></i> Rp {{ number_format($customer->total_debt, 0, ',', '.') }}
                            </a>
                        @else
                            <span class="badge bg-label-success">
                                <i class="bx bx-check me-1"></i> Rp 0 (Lunas)
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-dots-vertical-rounded fs-5 text-muted"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <button type="button" 
                                        class="dropdown-item" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editPelangganModal" 
                                        onclick="populateEditCustomerModal('{{ $customer->id }}', '{{ addslashes($customer->name) }}', '{{ addslashes($customer->phone ?? '') }}', '{{ addslashes($customer->email ?? '') }}', '{{ addslashes($customer->address ?? '') }}')">
                                    <i class="bx bx-edit-alt me-2 text-primary"></i> Edit Data
                                </button>
                                
                                @if($customer->total_debt > 0)
                                    <a class="dropdown-item" href="{{ route('customers.debt', ['search' => $customer->name]) }}">
                                        <i class="bx bx-wallet me-2 text-warning"></i> Rincian Hutang
                                    </a>
                                @endif

                                <div class="dropdown-divider"></div>

                                <button type="button" 
                                        class="dropdown-item text-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#hapusPelangganModal{{ $customer->id }}">
                                    <i class="bx bx-trash me-2"></i> Hapus
                                </button>
                            </div>
                        </div>

                        <!-- Modal Konfirmasi Hapus Pelanggan -->
                        <div class="modal fade" id="hapusPelangganModal{{ $customer->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header border-0 pb-0">
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center pt-0">
                                        <div class="avatar avatar-xl bg-label-danger mx-auto mb-3">
                                            <i class="bx bx-trash fs-2"></i>
                                        </div>
                                        <h5 class="modal-title mb-2">Hapus Pelanggan?</h5>
                                        <p class="text-muted small mb-3">
                                            Apakah Anda yakin ingin menghapus pelanggan <strong>{{ $customer->name }}</strong>?
                                            @if($customer->total_debt > 0)
                                                <div class="alert alert-danger p-2 small mt-2">
                                                    <i class="bx bx-error me-1"></i> Pelanggan masih memiliki saldo hutang Rp {{ number_format($customer->total_debt, 0, ',', '.') }}.
                                                </div>
                                            @endif
                                        </p>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" {{ $customer->total_debt > 0 ? 'disabled' : '' }}>Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bx bx-user fs-1 d-block mb-2 text-secondary"></i>
                            <h6 class="fw-bold mb-1">Belum ada data pelanggan</h6>
                            <p class="small mb-3">
                                @if(request()->hasAny(['search', 'debt_status']))
                                    Tidak ada pelanggan yang sesuai dengan kriteria filter Anda.
                                @else
                                    Mulai tambahkan pelanggan member toko Anda untuk pencatatan transaksi & hutang.
                                @endif
                            </p>
                            @if(request()->hasAny(['search', 'debt_status']))
                                <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary me-2">Reset Filter</a>
                            @endif
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#tambahPelangganModal">
                                <i class="bx bx-plus me-1"></i> Tambah Pelanggan
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-3">
        <small class="text-muted">
            Menampilkan {{ $customers->firstItem() }} sampai {{ $customers->lastItem() }} dari {{ $customers->total() }} pelanggan
        </small>
        <div>
            {{ $customers->links() }}
        </div>
    </div>
    @endif
</div>

<!-- Modal Tambah Pelanggan -->
<div class="modal fade" id="tambahPelangganModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">
                    <i class="bx bx-user-plus me-1 text-primary"></i> Tambah Pelanggan Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label">Nama Pelanggan <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Contoh: Budi Santoso" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-12">
                            <label for="phone" class="form-label">Nomor WhatsApp / HP</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bxl-whatsapp text-success"></i></span>
                                <input type="text" id="phone" name="phone" class="form-control" placeholder="Contoh: 081234567890" value="{{ old('phone') }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="email" class="form-label">Alamat Email (Opsional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                <input type="email" id="email" name="email" class="form-control" placeholder="budi@example.com" value="{{ old('email') }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Alamat Lengkap</label>
                            <textarea id="address" name="address" class="form-control" rows="3" placeholder="Alamat tempat tinggal / toko pelanggan...">{{ old('address') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pelanggan -->
<div class="modal fade" id="editPelangganModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">
                    <i class="bx bx-edit me-1 text-primary"></i> Edit Data Pelanggan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditPelanggan" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="edit_name" class="form-label">Nama Pelanggan <span class="text-danger">*</span></label>
                            <input type="text" id="edit_name" name="name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label for="edit_phone" class="form-label">Nomor WhatsApp / HP</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bxl-whatsapp text-success"></i></span>
                                <input type="text" id="edit_phone" name="phone" class="form-control">
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="edit_email" class="form-label">Alamat Email (Opsional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                <input type="email" id="edit_email" name="email" class="form-control">
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="edit_address" class="form-label">Alamat Lengkap</label>
                            <textarea id="edit_address" name="address" class="form-control" rows="3"></textarea>
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
@endsection

@push('scripts')
<script>
    function populateEditCustomerModal(id, name, phone, email, address) {
        const form = document.getElementById('formEditPelanggan');
        form.action = `{{ url('customers') }}/${id}`;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_phone').value = phone;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_address').value = address;
    }
</script>
@endpush
