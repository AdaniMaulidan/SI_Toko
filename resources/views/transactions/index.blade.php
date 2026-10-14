@extends('layouts.app')

@section('title', 'Riwayat Transaksi Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Transaksi /</span> Riwayat Penjualan
    </h4>
    <div class="d-flex gap-2">
        <a href="{{ route('kasir.index') }}" class="btn btn-primary shadow-sm">
            <i class="bx bx-cart me-1"></i> Buka Kasir (POS)
        </a>
    </div>
</div>

<!-- 1. Top Statistics Metric Cards -->
<div class="row g-3 mb-4">
    <!-- Total Penjualan -->
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Total Penjualan</span>
                    <div class="avatar avatar-sm bg-label-primary rounded">
                        <i class="bx bx-wallet fs-4 text-primary"></i>
                    </div>
                </div>
                <h4 class="fw-bolder mb-1 text-primary">Rp {{ number_format($stats['total_sales'], 0, ',', '.') }}</h4>
                <small class="text-muted">Total transaksi periode filter</small>
            </div>
        </div>
    </div>

    <!-- Jumlah Transaksi -->
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Jumlah Transaksi</span>
                    <div class="avatar avatar-sm bg-label-info rounded">
                        <i class="bx bx-receipt fs-4 text-info"></i>
                    </div>
                </div>
                <h4 class="fw-bolder mb-1">{{ number_format($stats['total_transactions'], 0, ',', '.') }}</h4>
                <small class="text-muted">Nota / faktur penjualan</small>
            </div>
        </div>
    </div>

    <!-- Total Lunas / Diterima -->
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Uang Diterima</span>
                    <div class="avatar avatar-sm bg-label-success rounded">
                        <i class="bx bx-check-circle fs-4 text-success"></i>
                    </div>
                </div>
                <h4 class="fw-bolder mb-1 text-success">Rp {{ number_format($stats['total_paid'], 0, ',', '.') }}</h4>
                <small class="text-muted">Kas masuk / lunas / DP</small>
            </div>
        </div>
    </div>

    <!-- Sisa Piutang / Hutang -->
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Sisa Piutang</span>
                    <div class="avatar avatar-sm bg-label-warning rounded">
                        <i class="bx bx-time-five fs-4 text-warning"></i>
                    </div>
                </div>
                <h4 class="fw-bolder mb-1 text-danger">Rp {{ number_format($stats['total_debt'], 0, ',', '.') }}</h4>
                <small class="text-muted">Transaksi belum lunas (Bon)</small>
            </div>
        </div>
    </div>
</div>

<!-- 2. Filter Form Card -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 d-flex align-items-center gap-1">
            <i class="bx bx-filter-alt text-primary"></i> Filter Riwayat Transaksi
        </h6>
        @if(request()->hasAny(['start_date', 'end_date', 'payment_status', 'payment_method', 'user_id', 'q']))
            <a href="{{ route('transactions.index') }}" class="btn btn-xs btn-outline-danger">
                <i class="bx bx-refresh me-1"></i>Reset Filter
            </a>
        @endif
    </div>
    <div class="card-body pt-3">
        <form method="GET" action="{{ route('transactions.index') }}">
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

                <!-- Status Pembayaran -->
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold text-muted" for="payment_status">Status Bayar</label>
                    <select name="payment_status" id="payment_status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                        <option value="debt" {{ request('payment_status') === 'debt' ? 'selected' : '' }}>Hutang (Bon)</option>
                    </select>
                </div>

                <!-- Metode Pembayaran -->
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold text-muted" for="payment_method">Metode</label>
                    <select name="payment_method" id="payment_method" class="form-select form-select-sm">
                        <option value="">Semua Metode</option>
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Tunai</option>
                        <option value="qris" {{ request('payment_method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                        <option value="transfer" {{ request('payment_method') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                        <option value="debt" {{ request('payment_method') === 'debt' ? 'selected' : '' }}>Hutang</option>
                    </select>
                </div>

                <!-- Kasir -->
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-semibold text-muted" for="user_id">Kasir</label>
                    <select name="user_id" id="user_id" class="form-select form-select-sm">
                        <option value="">Semua Kasir</option>
                        @foreach($cashiers as $c)
                            <option value="{{ $c->id }}" {{ request('user_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Cari Faktur / Pelanggan -->
                <div class="col-md-9">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bx bx-search"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Cari nomor faktur (INV-...), nama pelanggan, atau catatan..." value="{{ request('q') }}">
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bx bx-filter-alt me-1"></i> Terapkan Filter
                    </button>
                    @if(request()->hasAny(['start_date', 'end_date', 'payment_status', 'payment_method', 'user_id', 'q']))
                        <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                            <i class="bx bx-refresh"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 3. Transactions Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0">Daftar Transaksi Penjualan</h5>
            <small class="text-muted">Menampilkan {{ $sales->total() }} total data transaksi</small>
        </div>
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Faktur</th>
                    <th>Tanggal & Waktu</th>
                    <th>Pelanggan</th>
                    <th>Kasir</th>
                    <th class="text-center">Total Item</th>
                    <th class="text-end">Total Belanja</th>
                    <th class="text-center">Metode</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width: 80px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <!-- No. Faktur -->
                    <td>
                        <a href="javascript:void(0);" onclick="openTransactionModal({{ $sale->id }})" class="fw-bold text-primary font-monospace text-decoration-none" title="Lihat Detail Transaksi">
                            {{ $sale->invoice_number }}
                        </a>
                    </td>

                    <!-- Tanggal & Waktu -->
                    <td class="small">
                        <span class="d-block text-dark fw-semibold">{{ $sale->created_at->format('d M Y') }}</span>
                        <span class="text-muted" style="font-size: 0.75rem;">{{ $sale->created_at->format('H:i') }} WIB</span>
                    </td>

                    <!-- Pelanggan -->
                    <td>
                        @if($sale->customer)
                            <span class="fw-semibold text-heading d-block">{{ $sale->customer->name }}</span>
                            <small class="text-muted">{{ $sale->customer->phone ?: '-' }}</small>
                        @else
                            <span class="badge bg-label-secondary font-monospace">Pelanggan Umum</span>
                        @endif
                    </td>

                    <!-- Kasir -->
                    <td>
                        <span class="small text-muted d-block">{{ $sale->user?->name ?? 'Kasir' }}</span>
                    </td>

                    <!-- Total Item -->
                    <td class="text-center">
                        <span class="badge bg-label-secondary font-monospace">{{ $sale->items->sum('quantity') }} pcs</span>
                    </td>

                    <!-- Total Belanja -->
                    <td class="text-end">
                        <span class="fw-bolder text-primary">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                        @if($sale->discount > 0)
                            <div class="small text-danger" style="font-size: 0.72rem;">Disc: -{{ number_format($sale->discount, 0, ',', '.') }}</div>
                        @endif
                    </td>

                    <!-- Metode Bayar -->
                    <td class="text-center">
                        @if($sale->payment_method === 'cash')
                            <span class="badge bg-label-success"><i class="bx bx-money me-1"></i> Tunai</span>
                        @elseif($sale->payment_method === 'qris')
                            <span class="badge bg-label-info"><i class="bx bx-qr-scan me-1"></i> QRIS</span>
                        @elseif($sale->payment_method === 'transfer')
                            <span class="badge bg-label-primary"><i class="bx bx-credit-card me-1"></i> Transfer</span>
                        @elseif($sale->payment_method === 'debt')
                            <span class="badge bg-label-warning"><i class="bx bx-calendar-event me-1"></i> Hutang</span>
                        @else
                            <span class="badge bg-label-secondary">{{ strtoupper($sale->payment_method) }}</span>
                        @endif
                    </td>

                    <!-- Status Pembayaran -->
                    <td class="text-center">
                        @if($sale->payment_status === 'paid')
                            <span class="badge bg-success"><i class="bx bx-check me-1"></i>Lunas</span>
                        @else
                            <span class="badge bg-warning text-dark"><i class="bx bx-time me-1"></i>Hutang</span>
                        @endif
                    </td>

                    <!-- Kebab Menu Aksi -->
                    <td class="text-center">
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-dots-vertical-rounded fs-5 text-muted"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow-sm">
                                <!-- Modal Detail -->
                                <button type="button" class="dropdown-item" onclick="openTransactionModal({{ $sale->id }})">
                                    <i class="bx bx-show me-2 text-primary"></i> Detail Transaksi
                                </button>

                                <!-- Halaman Lengkap -->
                                <a class="dropdown-item" href="{{ route('transactions.show', $sale->id) }}">
                                    <i class="bx bx-link-external me-2 text-info"></i> Halaman Lengkap
                                </a>

                                <div class="dropdown-divider"></div>

                                <!-- Cetak Ulang Struk Thermal -->
                                <button type="button" class="dropdown-item" onclick="printReceiptPopup({{ $sale->id }}, '58')">
                                    <i class="bx bx-printer me-2 text-success"></i> Cetak Struk (58mm)
                                </button>
                                <button type="button" class="dropdown-item" onclick="printReceiptPopup({{ $sale->id }}, '80')">
                                    <i class="bx bx-printer me-2 text-success"></i> Cetak Struk (80mm)
                                </button>

                                <!-- Download PDF -->
                                <a class="dropdown-item" href="{{ route('kasir.receipt.pdf', $sale->id) }}" target="_blank">
                                    <i class="bx bxs-file-pdf me-2 text-danger"></i> Download Struk PDF
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bx bx-receipt fs-1 mb-2 text-secondary"></i>
                            <h6>Tidak ada data transaksi ditemukan.</h6>
                            <p class="small text-muted mb-0">Coba sesuaikan filter pencarian atau tanggal transaksi.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($sales->hasPages())
    <div class="card-footer border-top d-flex justify-content-between align-items-center flex-wrap gap-2 py-2">
        <small class="text-muted">
            Menampilkan data ke {{ $sales->firstItem() }} sampai {{ $sales->lastItem() }} dari {{ $sales->total() }} transaksi
        </small>
        <div>
            {{ $sales->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>

<!-- 4. Modal Detail Transaksi (FASE 4.5) -->
<div class="modal fade" id="detailTransactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
            <!-- Modal Header -->
            <div class="modal-header bg-lighter border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-sm bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bx bx-receipt fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-heading mb-0" id="modal-dt-invoice">Detail Transaksi</h5>
                        <small class="text-muted" id="modal-dt-datetime">-</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge" id="modal-dt-status">-</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <!-- Meta Info Row -->
                <div class="row g-2 mb-3 bg-lighter p-3 rounded-3 border">
                    <div class="col-sm-4">
                        <small class="text-muted d-block">Kasir:</small>
                        <strong class="text-dark" id="modal-dt-cashier">-</strong>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted d-block">Pelanggan:</small>
                        <strong class="text-dark" id="modal-dt-customer">-</strong>
                        <div class="small text-muted" id="modal-dt-customer-phone">-</div>
                    </div>
                    <div class="col-sm-4 text-sm-end">
                        <small class="text-muted d-block">Metode Pembayaran:</small>
                        <span class="badge bg-label-primary font-monospace" id="modal-dt-method">-</span>
                    </div>
                </div>

                <!-- Items Table -->
                <h6 class="fw-bold mb-2 d-flex align-items-center gap-1">
                    <i class="bx bx-package text-primary"></i> Rincian Produk Belanjaan
                </h6>
                <div class="table-responsive border rounded mb-3">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nama Produk</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Diskon</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="modal-dt-items-body">
                            <!-- Injected via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Ringkasan Keuangan -->
                <div class="row justify-content-end">
                    <div class="col-md-6">
                        <div class="bg-lighter p-3 rounded border small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Subtotal Produk:</span>
                                <strong id="modal-dt-subtotal">Rp 0</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1" id="modal-dt-disc-row">
                                <span class="text-muted">Total Diskon:</span>
                                <strong class="text-danger" id="modal-dt-discount">- Rp 0</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-top border-bottom my-1 fs-6">
                                <strong class="text-dark">TOTAL TAGIHAN:</strong>
                                <strong class="text-primary fw-bolder" id="modal-dt-total">Rp 0</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted" id="modal-dt-paid-label">Uang Diterima:</span>
                                <strong class="text-dark" id="modal-dt-paid">Rp 0</strong>
                            </div>
                            <div class="d-flex justify-content-between" id="modal-dt-change-row">
                                <span class="text-muted" id="modal-dt-change-label">Kembalian:</span>
                                <strong class="text-success" id="modal-dt-change">Rp 0</strong>
                            </div>

                            <!-- Detail Hutang Jika Ada -->
                            <div id="modal-dt-debt-box" class="mt-2 pt-2 border-top border-warning d-none">
                                <div class="d-flex justify-content-between text-danger mb-1">
                                    <span class="fw-semibold">Sisa Hutang:</span>
                                    <strong id="modal-dt-debt-amount">Rp 0</strong>
                                </div>
                                <div class="d-flex justify-content-between text-muted">
                                    <span>Jatuh Tempo:</span>
                                    <span id="modal-dt-debt-duedate">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catatan Transaksi -->
                <div class="mt-3 p-2 rounded bg-white border small d-none" id="modal-dt-notes-box">
                    <span class="fw-bold text-muted">Catatan Transaksi:</span>
                    <span id="modal-dt-notes" class="text-dark"></span>
                </div>
            </div>

            <!-- Modal Footer & Reprint Actions -->
            <div class="modal-footer border-top py-2 d-flex justify-content-between flex-wrap gap-2">
                <div class="d-flex gap-1 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="modal-btn-print-58">
                        <i class="bx bx-printer me-1"></i> Cetak 58mm
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="modal-btn-print-80">
                        <i class="bx bx-printer me-1"></i> Cetak 80mm
                    </button>
                    <a href="#" class="btn btn-sm btn-outline-danger" id="modal-btn-pdf" target="_blank">
                        <i class="bx bxs-file-pdf me-1"></i> PDF
                    </a>
                </div>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    const formatRp = (num) => 'Rp ' + Number(num).toLocaleString('id-ID');

    // Buka Modal Detail Transaksi via AJAX
    function openTransactionModal(saleId) {
        // Tampilkan indikator loading pada tabel modal
        const tbody = document.getElementById('modal-dt-items-body');
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-3"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Memuat rincian transaksi...</td></tr>`;

        const modalEl = document.getElementById('detailTransactionModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        // Fetch detail data transaksi dari endpoint
        fetch(`/transactions/${saleId}/detail`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.sale) {
                    const s = data.sale;

                    // Header Info
                    document.getElementById('modal-dt-invoice').innerText = s.invoice_number;
                    document.getElementById('modal-dt-datetime').innerText = `${s.created_at} WIB`;

                    const statusBadge = document.getElementById('modal-dt-status');
                    if (s.payment_status === 'paid') {
                        statusBadge.className = 'badge bg-success';
                        statusBadge.innerText = 'LUNAS';
                    } else {
                        statusBadge.className = 'badge bg-warning text-dark';
                        statusBadge.innerText = 'HUTANG (BON)';
                    }

                    // Kasir & Pelanggan
                    document.getElementById('modal-dt-cashier').innerText = s.cashier_name;
                    document.getElementById('modal-dt-customer').innerText = s.customer_name;
                    document.getElementById('modal-dt-customer-phone').innerText = s.customer_phone ? `Telp: ${s.customer_phone}` : '';

                    let methodLabel = s.payment_method.toUpperCase();
                    if (s.payment_method === 'cash') methodLabel = 'TUNAI';
                    else if (s.payment_method === 'debt') methodLabel = 'HUTANG (BON)';
                    else if (s.payment_method === 'transfer') methodLabel = 'TRANSFER BANK';
                    document.getElementById('modal-dt-method').innerText = methodLabel;

                    // Items Table
                    tbody.innerHTML = '';
                    s.items.forEach((item, idx) => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${idx + 1}</td>
                            <td>
                                <span class="fw-semibold text-dark d-block">${item.product_name}</span>
                                <small class="text-muted font-monospace">${item.barcode}</small>
                            </td>
                            <td class="text-end">${formatRp(item.selling_price)}</td>
                            <td class="text-center fw-bold">${item.quantity} ${item.unit}</td>
                            <td class="text-end text-danger">${item.item_discount > 0 ? `-${formatRp(item.item_discount)}` : '-'}</td>
                            <td class="text-end fw-bold text-primary">${formatRp(item.subtotal)}</td>
                        `;
                        tbody.appendChild(tr);
                    });

                    // Ringkasan Keuangan
                    document.getElementById('modal-dt-subtotal').innerText = formatRp(s.subtotal);
                    const discRow = document.getElementById('modal-dt-disc-row');
                    if (s.discount > 0) {
                        discRow.style.display = 'flex';
                        document.getElementById('modal-dt-discount').innerText = `- ${formatRp(s.discount)}`;
                    } else {
                        discRow.style.display = 'none';
                    }

                    document.getElementById('modal-dt-total').innerText = formatRp(s.total_amount);

                    const paidLabel = document.getElementById('modal-dt-paid-label');
                    const paidVal = document.getElementById('modal-dt-paid');
                    const changeRow = document.getElementById('modal-dt-change-row');
                    const changeLabel = document.getElementById('modal-dt-change-label');
                    const changeVal = document.getElementById('modal-dt-change');
                    const debtBox = document.getElementById('modal-dt-debt-box');

                    if (s.payment_method === 'cash') {
                        paidLabel.innerText = 'Tunai Diterima:';
                        paidVal.innerText = formatRp(s.paid_amount);
                        changeRow.style.display = 'flex';
                        changeLabel.innerText = 'Kembalian:';
                        changeVal.innerText = formatRp(s.change_amount);
                        debtBox.classList.add('d-none');
                    } else if (s.payment_method === 'debt') {
                        paidLabel.innerText = 'Uang Muka (DP):';
                        paidVal.innerText = formatRp(s.paid_amount);
                        changeRow.style.display = 'none';
                        debtBox.classList.remove('d-none');

                        if (s.debt) {
                            document.getElementById('modal-dt-debt-amount').innerText = formatRp(s.debt.remaining_debt);
                            document.getElementById('modal-dt-debt-duedate').innerText = s.debt.due_date;
                        } else {
                            const rem = Math.max(0, s.total_amount - s.paid_amount);
                            document.getElementById('modal-dt-debt-amount').innerText = formatRp(rem);
                            document.getElementById('modal-dt-debt-duedate').innerText = '-';
                        }
                    } else {
                        paidLabel.innerText = 'Dibayar (Lunas):';
                        paidVal.innerText = formatRp(s.paid_amount);
                        changeRow.style.display = 'none';
                        debtBox.classList.add('d-none');
                    }

                    // Catatan
                    const notesBox = document.getElementById('modal-dt-notes-box');
                    if (s.notes) {
                        notesBox.classList.remove('d-none');
                        document.getElementById('modal-dt-notes').innerText = s.notes;
                    } else {
                        notesBox.classList.add('d-none');
                    }

                    // Aksi Cetak di Modal
                    document.getElementById('modal-btn-print-58').onclick = () => printReceiptPopup(s.id, '58');
                    document.getElementById('modal-btn-print-80').onclick = () => printReceiptPopup(s.id, '80');
                    document.getElementById('modal-btn-pdf').href = `/kasir/receipt/${s.id}/pdf`;
                }
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-3 text-danger"><i class="bx bx-error-circle me-1"></i>Gagal memuat rincian transaksi.</td></tr>`;
                console.error(err);
            });
    }

    // Buka Popup Jendela Cetak Struk Thermal
    function printReceiptPopup(saleId, paper = '58') {
        const url = `/kasir/receipt/${saleId}?paper=${paper}&autoprint=1`;
        const width = paper === '80' ? 440 : 380;
        const height = 650;
        const left = (window.screen.width / 2) - (width / 2);
        const top = (window.screen.height / 2) - (height / 2);

        window.open(url, `Struk_${saleId}`, `width=${width},height=${height},top=${top},left=${left},scrollbars=yes,status=no`);
    }
</script>
@endsection
