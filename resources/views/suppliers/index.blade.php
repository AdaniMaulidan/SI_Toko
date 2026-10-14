@extends('layouts.app')

@section('title', 'Manajemen Supplier')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-1">
            <span class="text-muted fw-light">Master Data /</span> Supplier
        </h4>
        <p class="text-muted mb-0 small">Kelola data vendor, distributor, dan kontak PIC pengadaan barang dagangan.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahSupplierModal">
        <i class="bx bx-plus me-1"></i> Tambah Supplier
    </button>
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
                    <i class="bx bx-buildings fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Total Supplier</span>
                    <h4 class="card-title mb-0 fw-bold">{{ number_format($stats['total']) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-success rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-check-shield fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Mitra Aktif Transaksi</span>
                    <h4 class="card-title mb-0 fw-bold text-success">{{ number_format($stats['active_partners']) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-info rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-receipt fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Total Faktur Pengadaan</span>
                    <h4 class="card-title mb-0 fw-bold text-info">{{ number_format($stats['total_purchases_num']) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar avatar-md bg-label-warning rounded p-2 me-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-wallet fs-3"></i>
                </div>
                <div>
                    <span class="d-block text-muted small fw-semibold">Total Nilai Pembelian</span>
                    <h4 class="card-title mb-0 fw-bold text-warning">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Card -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('suppliers.index') }}" class="row gx-3 gy-2 align-items-end">
            <div class="col-md-6 offset-md-6 col-sm-12">
                <label class="form-label" for="search">Cari Supplier</label>
                <div class="input-group">
                    <input type="text" 
                           name="search" 
                           id="search" 
                           class="form-control" 
                           placeholder="Cari nama perusahaan, PIC, telepon, alamat..." 
                           value="{{ request('search') }}">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bx bx-search"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary" title="Reset Pencarian">
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
                    <th>Nama Supplier / Vendor</th>
                    <th>Kontak Person (PIC)</th>
                    <th>Telepon / WhatsApp</th>
                    <th>Alamat</th>
                    <th>Faktur Pembelian</th>
                    <th class="text-center" style="width: 80px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $index => $supplier)
                <tr>
                    <td>{{ $suppliers->firstItem() + $index }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3 bg-label-primary rounded d-flex align-items-center justify-content-center">
                                <i class="bx bx-buildings fs-5"></i>
                            </div>
                            <div>
                                <span class="fw-bold text-heading d-block">{{ $supplier->name }}</span>
                                @if($supplier->email)
                                    <small class="text-muted"><i class="bx bx-envelope me-1"></i>{{ $supplier->email }}</small>
                                @else
                                    <small class="text-muted">ID: #SUP-{{ str_pad($supplier->id, 4, '0', STR_PAD_LEFT) }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($supplier->contact_person)
                            <span class="fw-semibold text-body"><i class="bx bx-user me-1 text-primary"></i>{{ $supplier->contact_person }}</span>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td>
                        @if($supplier->phone)
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', $supplier->phone);
                                if (str_starts_with($cleanPhone, '0')) {
                                    $waNumber = '62' . substr($cleanPhone, 1);
                                } else {
                                    $waNumber = $cleanPhone;
                                }
                            @endphp
                            <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="text-success fw-semibold small text-decoration-none" title="Chat via WhatsApp">
                                <i class="bx bxl-whatsapp fs-6 me-1"></i>{{ $supplier->phone }}
                            </a>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="d-inline-block text-truncate" style="max-width: 200px;" title="{{ $supplier->address }}">
                            {{ $supplier->address ?: '-' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-label-info">
                            <i class="bx bx-file me-1"></i>{{ $supplier->purchases_count }} faktur
                        </span>
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
                                        data-bs-target="#editSupplierModal" 
                                        onclick="populateEditSupplierModal('{{ $supplier->id }}', '{{ addslashes($supplier->name) }}', '{{ addslashes($supplier->contact_person ?? '') }}', '{{ addslashes($supplier->phone ?? '') }}', '{{ addslashes($supplier->email ?? '') }}', '{{ addslashes($supplier->address ?? '') }}')">
                                    <i class="bx bx-edit-alt me-2 text-primary"></i> Edit Data
                                </button>
                                
                                <div class="dropdown-divider"></div>

                                <button type="button" 
                                        class="dropdown-item text-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#hapusSupplierModal{{ $supplier->id }}">
                                    <i class="bx bx-trash me-2"></i> Hapus
                                </button>
                            </div>
                        </div>

                        <!-- Modal Konfirmasi Hapus Supplier -->
                        <div class="modal fade" id="hapusSupplierModal{{ $supplier->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header border-0 pb-0">
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center pt-0">
                                        <div class="avatar avatar-xl bg-label-danger mx-auto mb-3">
                                            <i class="bx bx-trash fs-2"></i>
                                        </div>
                                        <h5 class="modal-title mb-2">Hapus Supplier?</h5>
                                        <p class="text-muted small mb-3">
                                            Apakah Anda yakin ingin menghapus supplier <strong>{{ $supplier->name }}</strong>?
                                            @if($supplier->purchases_count > 0)
                                                <div class="alert alert-danger p-2 small mt-2">
                                                    <i class="bx bx-error me-1"></i> Supplier memiliki {{ $supplier->purchases_count }} riwayat faktur pembelian dan tidak dapat dihapus.
                                                </div>
                                            @endif
                                        </p>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" {{ $supplier->purchases_count > 0 ? 'disabled' : '' }}>Hapus</button>
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
                            <i class="bx bx-buildings fs-1 d-block mb-2 text-secondary"></i>
                            <h6 class="fw-bold mb-1">Belum ada data supplier</h6>
                            <p class="small mb-3">
                                @if(request('search'))
                                    Tidak ada supplier yang sesuai dengan kata kunci pencarian Anda.
                                @else
                                    Mulai tambahkan vendor atau distributor pemasok produk toko Anda.
                                @endif
                            </p>
                            @if(request('search'))
                                <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-outline-secondary me-2">Reset Pencarian</a>
                            @endif
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#tambahSupplierModal">
                                <i class="bx bx-plus me-1"></i> Tambah Supplier
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($suppliers->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-3">
        <small class="text-muted">
            Menampilkan {{ $suppliers->firstItem() }} sampai {{ $suppliers->lastItem() }} dari {{ $suppliers->total() }} supplier
        </small>
        <div>
            {{ $suppliers->links() }}
        </div>
    </div>
    @endif
</div>

<!-- Modal Tambah Supplier -->
<div class="modal fade" id="tambahSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">
                    <i class="bx bx-buildings me-1 text-primary"></i> Tambah Supplier Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label">Nama Supplier / Perusahaan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-buildings"></i></span>
                                <input type="text" id="name" name="name" class="form-control" placeholder="Contoh: PT. Indofood Sukses Makmur" value="{{ old('name') }}" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="contact_person" class="form-label">Nama Kontak Person (PIC)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" id="contact_person" name="contact_person" class="form-control" placeholder="Contoh: Bpk. Hendra Gunawan" value="{{ old('contact_person') }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="phone" class="form-label">Nomor Telepon / WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bxl-whatsapp text-success"></i></span>
                                <input type="text" id="phone" name="phone" class="form-control" placeholder="Contoh: 081234567890" value="{{ old('phone') }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="email" class="form-label">Alamat Email (Opsional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                <input type="email" id="email" name="email" class="form-control" placeholder="sales@indofood.co.id" value="{{ old('email') }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Alamat Lengkap</label>
                            <textarea id="address" name="address" class="form-control" rows="3" placeholder="Alamat kantor, gudang, atau distributor...">{{ old('address') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Supplier -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">
                    <i class="bx bx-edit me-1 text-primary"></i> Edit Data Supplier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditSupplier" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="edit_name" class="form-label">Nama Supplier / Perusahaan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-buildings"></i></span>
                                <input type="text" id="edit_name" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="edit_contact_person" class="form-label">Nama Kontak Person (PIC)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" id="edit_contact_person" name="contact_person" class="form-control">
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="edit_phone" class="form-label">Nomor Telepon / WhatsApp</label>
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
    function populateEditSupplierModal(id, name, contactPerson, phone, email, address) {
        const form = document.getElementById('formEditSupplier');
        form.action = `{{ url('suppliers') }}/${id}`;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_contact_person').value = contactPerson;
        document.getElementById('edit_phone').value = phone;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_address').value = address;
    }
</script>
@endpush
