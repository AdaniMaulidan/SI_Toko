@extends('layouts.app')

@section('title', 'Riwayat Pembelian Produk (Kulakan)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Pembelian /</span> Riwayat Pembelian
    </h4>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary shadow-sm">
        <i class="bx bx-plus me-1"></i> Catat Pembelian Baru
    </a>
</div>

{{-- Flash Alert Messages --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- 1. Top Statistics Metric Cards -->
<div class="row g-3 mb-4">
    <!-- Total Pembelian -->
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Total Kulakan</span>
                    <div class="avatar avatar-sm bg-label-primary rounded">
                        <i class="bx bx-store-alt fs-4 text-primary"></i>
                    </div>
                </div>
                <h4 class="fw-bolder mb-1 text-primary">Rp {{ number_format($stats['total_purchases'], 0, ',', '.') }}</h4>
                <small class="text-muted">Total nilai faktur periode filter</small>
            </div>
        </div>
    </div>

    <!-- Jumlah Faktur PO -->
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Jumlah Faktur</span>
                    <div class="avatar avatar-sm bg-label-info rounded">
                        <i class="bx bx-receipt fs-4 text-info"></i>
                    </div>
                </div>
                <h4 class="fw-bolder mb-1">{{ number_format($stats['total_invoices'], 0, ',', '.') }}</h4>
                <small class="text-muted">Faktur pembelian supplier</small>
            </div>
        </div>
    </div>

    <!-- Total Lunas / Dibayar -->
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Uang Dibayarkan</span>
                    <div class="avatar avatar-sm bg-label-success rounded">
                        <i class="bx bx-check-circle fs-4 text-success"></i>
                    </div>
                </div>
                <h4 class="fw-bolder mb-1 text-success">Rp {{ number_format($stats['total_paid'], 0, ',', '.') }}</h4>
                <small class="text-muted">Kas keluar / lunas / DP</small>
            </div>
        </div>
    </div>

    <!-- Sisa Hutang Kulakan -->
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Hutang ke Supplier</span>
                    <div class="avatar avatar-sm bg-label-danger rounded">
                        <i class="bx bx-time-five fs-4 text-danger"></i>
                    </div>
                </div>
                <h4 class="fw-bolder mb-1 text-danger">Rp {{ number_format($stats['total_debt'], 0, ',', '.') }}</h4>
                <small class="text-muted">Faktur belum lunas / tempo</small>
            </div>
        </div>
    </div>
</div>

<!-- 2. Filter Form Card -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 d-flex align-items-center gap-1">
            <i class="bx bx-filter-alt text-primary"></i> Filter Riwayat Pembelian
        </h6>
        @if(request()->hasAny(['supplier_id', 'payment_status', 'start_date', 'end_date', 'q']))
            <a href="{{ route('purchases.index') }}" class="btn btn-xs btn-outline-danger">
                <i class="bx bx-refresh me-1"></i>Reset Filter
            </a>
        @endif
    </div>
    <div class="card-body pt-3">
        <form method="GET" action="{{ route('purchases.index') }}">
            <div class="row g-3">
                <!-- Tanggal Mulai -->
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted" for="start_date">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                </div>

                <!-- Tanggal Akhir -->
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted" for="end_date">Tanggal Akhir</label>
                    <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                </div>

                <!-- Supplier -->
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted" for="supplier_id">Supplier</label>
                    <select id="supplier_id" name="supplier_id" class="form-select form-select-sm">
                        <option value="">Semua Supplier</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                                {{ $sup->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Bayar -->
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted" for="payment_status">Status Bayar</label>
                    <select id="payment_status" name="payment_status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                        <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Sebagian (DP)</option>
                        <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Hutang / Tempo</option>
                    </select>
                </div>

                <!-- Cari Faktur / Catatan -->
                <div class="col-md-9">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bx bx-search"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Cari nomor faktur / PO, supplier, atau catatan..." value="{{ request('q') }}">
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bx bx-filter-alt me-1"></i> Terapkan Filter
                    </button>
                    @if(request()->hasAny(['supplier_id', 'payment_status', 'start_date', 'end_date', 'q']))
                        <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                            <i class="bx bx-refresh"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 3. Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0">Daftar Pembelian (Barang Masuk)</h5>
            <small class="text-muted">Total {{ $purchases->total() }} faktur pembelian</small>
        </div>
    </div>
    
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Faktur / PO</th>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th>Dicatat Oleh</th>
                    <th class="text-center">Total Item</th>
                    <th class="text-end">Total Biaya</th>
                    <th class="text-center">Status Bayar</th>
                    <th class="text-center" style="width: 80px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $po)
                <tr>
                    <!-- No. Faktur -->
                    <td>
                        <a href="javascript:void(0);" onclick="openPurchaseModal({{ $po->id }})" class="fw-bold font-monospace text-primary text-decoration-none" title="Lihat Rincian Barang">
                            {{ $po->invoice_number }}
                        </a>
                    </td>

                    <!-- Tanggal -->
                    <td>
                        <span class="d-block text-dark fw-semibold">{{ $po->purchase_date->format('d M Y') }}</span>
                    </td>

                    <!-- Supplier -->
                    <td>
                        @if($po->supplier)
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-xs me-2 bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-buildings fs-6"></i>
                                </div>
                                <div>
                                    <span class="fw-semibold text-heading d-block">{{ $po->supplier->name }}</span>
                                    <small class="text-muted">{{ $po->supplier->phone ?: '-' }}</small>
                                </div>
                            </div>
                        @else
                            <span class="badge bg-label-secondary font-monospace">Supplier Umum / Tanpa Nama</span>
                        @endif
                    </td>

                    <!-- Dicatat Oleh -->
                    <td>
                        <span class="small text-muted">{{ $po->user?->name ?? 'Staff Gudang' }}</span>
                    </td>

                    <!-- Total Item -->
                    <td class="text-center">
                        <span class="badge bg-label-secondary font-monospace">{{ $po->items->sum('quantity') }} pcs</span>
                    </td>

                    <!-- Total Biaya -->
                    <td class="text-end">
                        <span class="fw-bolder text-primary">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</span>
                        @if($po->payment_status === 'partial')
                            <div class="small text-danger" style="font-size: 0.72rem;">Sisa: Rp {{ number_format($po->total_amount - $po->paid_amount, 0, ',', '.') }}</div>
                        @endif
                    </td>

                    <!-- Status Bayar -->
                    <td class="text-center">
                        @if($po->payment_status === 'paid')
                            <span class="badge bg-success"><i class="bx bx-check me-1"></i>Lunas</span>
                        @elseif($po->payment_status === 'partial')
                            <span class="badge bg-info text-white"><i class="bx bx-time me-1"></i>Sebagian (DP)</span>
                        @else
                            <span class="badge bg-warning text-dark"><i class="bx bx-time-five me-1"></i>Hutang / Tempo</span>
                        @endif
                    </td>

                    <!-- Aksi Dropdown -->
                    <td class="text-center">
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-dots-vertical-rounded fs-5 text-muted"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow-sm">
                                <!-- Modal Detail Cepat -->
                                <button type="button" class="dropdown-item" onclick="openPurchaseModal({{ $po->id }})">
                                    <i class="bx bx-show me-2 text-primary"></i> Detail Barang
                                </button>

                                <!-- Halaman Lengkap -->
                                <a class="dropdown-item" href="{{ route('purchases.show', $po->id) }}">
                                    <i class="bx bx-link-external me-2 text-info"></i> Halaman Faktur
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bx bx-store-alt fs-1 mb-2 text-secondary"></i>
                            <h6>Belum ada data pembelian tercatat.</h6>
                            <p class="small text-muted mb-0">Klik tombol "Catat Pembelian Baru" di atas untuk menambahkan barang masuk.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($purchases->hasPages())
    <div class="card-footer border-top d-flex justify-content-between align-items-center flex-wrap gap-2 py-2">
        <small class="text-muted">
            Menampilkan data ke {{ $purchases->firstItem() }} sampai {{ $purchases->lastItem() }} dari {{ $purchases->total() }} faktur
        </small>
        <div>
            {{ $purchases->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>

<!-- 4. Modal Detail Cepat Pembelian (FASE 5.2) -->
<div class="modal fade" id="detailPurchaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
            <!-- Modal Header -->
            <div class="modal-header bg-lighter border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-sm bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bx bx-store-alt fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-heading mb-0" id="modal-po-invoice">Detail Faktur Pembelian</h5>
                        <small class="text-muted" id="modal-po-datetime">-</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge" id="modal-po-status">-</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <!-- Meta Info Row -->
                <div class="row g-2 mb-3 bg-lighter p-3 rounded-3 border">
                    <div class="col-sm-4">
                        <small class="text-muted d-block">Supplier:</small>
                        <strong class="text-dark d-block" id="modal-po-supplier">-</strong>
                        <small class="text-muted" id="modal-po-supplier-phone">-</small>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted d-block">Dicatat Oleh:</small>
                        <strong class="text-dark" id="modal-po-creator">-</strong>
                    </div>
                    <div class="col-sm-4 text-sm-end">
                        <small class="text-muted d-block">Status Pembayaran:</small>
                        <span class="badge" id="modal-po-payment-badge">-</span>
                    </div>
                </div>

                <!-- Items Table -->
                <h6 class="fw-bold mb-2 d-flex align-items-center gap-1">
                    <i class="bx bx-package text-primary"></i> Rincian Barang yang Dibeli
                </h6>
                <div class="table-responsive border rounded mb-3">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th>Nama Produk</th>
                                <th class="text-end">Harga Beli Satuan</th>
                                <th class="text-center">Kuantitas</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="modal-po-items-body">
                            <!-- Injected via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Financial Calculation -->
                <div class="row justify-content-end">
                    <div class="col-md-6">
                        <div class="bg-lighter p-3 rounded border small">
                            <div class="d-flex justify-content-between py-1 border-bottom mb-2 fs-6">
                                <strong class="text-dark">TOTAL BIAYA:</strong>
                                <strong class="text-primary fw-bolder" id="modal-po-total">Rp 0</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Nominal Dibayarkan:</span>
                                <strong class="text-dark" id="modal-po-paid">Rp 0</strong>
                            </div>
                            <div class="d-flex justify-content-between text-danger fw-bold border-top pt-1 mt-1" id="modal-po-debt-row">
                                <span>Sisa Hutang Faktur:</span>
                                <span id="modal-po-debt">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catatan Transaksi -->
                <div class="mt-3 p-2 rounded bg-white border small d-none" id="modal-po-notes-box">
                    <span class="fw-bold text-muted">Catatan:</span>
                    <span id="modal-po-notes" class="text-dark"></span>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top py-2 d-flex justify-content-between">
                <a href="#" class="btn btn-sm btn-outline-primary" id="modal-po-link-show">
                    <i class="bx bx-link-external me-1"></i> Buka Halaman Faktur
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    const formatRp = (num) => 'Rp ' + Number(num).toLocaleString('id-ID');

    // Buka Modal Detail Cepat Pembelian via AJAX
    function openPurchaseModal(purchaseId) {
        const tbody = document.getElementById('modal-po-items-body');
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Memuat rincian pembelian...</td></tr>`;

        const modalEl = document.getElementById('detailPurchaseModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        fetch(`/purchases/${purchaseId}/detail`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.purchase) {
                    const p = data.purchase;

                    // Header Info
                    document.getElementById('modal-po-invoice').innerText = p.invoice_number;
                    document.getElementById('modal-po-datetime').innerText = `Tanggal: ${p.purchase_date}`;

                    const statusBadge = document.getElementById('modal-po-status');
                    const paymentBadge = document.getElementById('modal-po-payment-badge');

                    if (p.payment_status === 'paid') {
                        statusBadge.className = 'badge bg-success';
                        statusBadge.innerText = 'LUNAS';
                        paymentBadge.className = 'badge bg-label-success';
                        paymentBadge.innerText = 'Lunas (Kas Keluar)';
                    } else if (p.payment_status === 'partial') {
                        statusBadge.className = 'badge bg-info text-white';
                        statusBadge.innerText = 'SEBAGIAN';
                        paymentBadge.className = 'badge bg-label-info';
                        paymentBadge.innerText = 'Sebagian / Uang Muka (DP)';
                    } else {
                        statusBadge.className = 'badge bg-warning text-dark';
                        statusBadge.innerText = 'TEMPO / HUTANG';
                        paymentBadge.className = 'badge bg-label-warning';
                        paymentBadge.innerText = 'Hutang / Tempo';
                    }

                    // Supplier & Creator
                    document.getElementById('modal-po-supplier').innerText = p.supplier_name;
                    document.getElementById('modal-po-supplier-phone').innerText = p.supplier_phone ? `Telp: ${p.supplier_phone}` : '';
                    document.getElementById('modal-po-creator').innerText = p.creator_name;

                    // Items Table
                    tbody.innerHTML = '';
                    p.items.forEach((item, idx) => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td class="text-center">${idx + 1}</td>
                            <td>
                                <span class="fw-semibold text-dark d-block">${item.product_name}</span>
                                <small class="text-muted font-monospace">${item.barcode}</small>
                            </td>
                            <td class="text-end">${formatRp(item.cost_price)}</td>
                            <td class="text-center fw-bold">${item.quantity} ${item.unit}</td>
                            <td class="text-end fw-bold text-primary">${formatRp(item.subtotal)}</td>
                        `;
                        tbody.appendChild(tr);
                    });

                    // Ringkasan Keuangan
                    document.getElementById('modal-po-total').innerText = formatRp(p.total_amount);
                    document.getElementById('modal-po-paid').innerText = formatRp(p.paid_amount);

                    const debtRow = document.getElementById('modal-po-debt-row');
                    if (p.remaining_debt > 0) {
                        debtRow.style.display = 'flex';
                        document.getElementById('modal-po-debt').innerText = formatRp(p.remaining_debt);
                    } else {
                        debtRow.style.display = 'none';
                    }

                    // Catatan
                    const notesBox = document.getElementById('modal-po-notes-box');
                    if (p.notes) {
                        notesBox.classList.remove('d-none');
                        document.getElementById('modal-po-notes').innerText = p.notes;
                    } else {
                        notesBox.classList.add('d-none');
                    }

                    // Link Halaman Lengkap
                    document.getElementById('modal-po-link-show').href = `/purchases/${p.id}`;
                }
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-danger"><i class="bx bx-error-circle me-1"></i>Gagal memuat rincian pembelian.</td></tr>`;
                console.error(err);
            });
    }
</script>
@endsection
