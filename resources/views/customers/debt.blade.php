@extends('layouts.app')

@section('title', 'Data Hutang Pelanggan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-1">
            <span class="text-muted fw-light">Pelanggan /</span> Data Hutang Pelanggan
        </h4>
        <p class="text-muted mb-0 small">Pantau tagihan piutang belum lunas dan kelola pencatatan pelunasan/cicilan.</p>
    </div>
    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar Pelanggan
    </a>
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

<div class="row mb-4">
    <div class="col-md-6 col-lg-4">
        <div class="card bg-danger text-white border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white mb-1">Total Piutang Belum Terbayar</h6>
                        <h3 class="text-white fw-bold mb-0">Rp {{ number_format($totalReceivable, 0, ',', '.') }}</h3>
                    </div>
                    <div class="avatar avatar-md">
                        <span class="avatar-initial rounded bg-white text-danger shadow-sm"><i class="bx bx-wallet fs-3"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="card-title mb-0">
            <i class="bx bx-receipt me-1 text-danger"></i> Rincian Tagihan Hutang Belum Lunas
        </h5>
        
        <form method="GET" action="{{ route('customers.debt') }}" class="d-flex gap-2">
            <div class="input-group input-group-sm" style="min-width: 260px;">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Cari pelanggan / no transaksi..." 
                       value="{{ request('search') }}">
                <button class="btn btn-outline-primary" type="submit">
                    <i class="bx bx-search"></i>
                </button>
                @if(request('search'))
                    <a href="{{ route('customers.debt') }}" class="btn btn-outline-secondary" title="Reset">
                        <i class="bx bx-refresh"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
    
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Pelanggan</th>
                    <th>No. Transaksi</th>
                    <th>Tgl Transaksi</th>
                    <th>Jatuh Tempo</th>
                    <th>Total Hutang</th>
                    <th>Sisa Hutang</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($debts as $index => $debt)
                <tr>
                    <td>{{ $debts->firstItem() + $index }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3 bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                                <span class="fw-bold">{{ strtoupper(substr($debt->customer->name ?? 'P', 0, 1)) }}</span>
                            </div>
                            <div>
                                <span class="fw-bold text-heading d-block">{{ $debt->customer->name ?? 'Pelanggan Umum' }}</span>
                                <small class="text-muted">{{ $debt->customer->phone ?: 'Tanpa no. telepon' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($debt->sale)
                            <a href="{{ route('transactions.show', $debt->sale_id) }}" class="fw-semibold font-monospace">
                                {{ $debt->sale->invoice_number }}
                            </a>
                        @else
                            <span class="text-muted font-monospace">TRX-MANUAL-{{ $debt->id }}</span>
                        @endif
                    </td>
                    <td>
                        {{ $debt->created_at ? $debt->created_at->format('d M Y') : '-' }}
                    </td>
                    <td>
                        @if($debt->due_date)
                            @php
                                $isOverdue = \Carbon\Carbon::parse($debt->due_date)->isPast() && $debt->remaining_debt > 0;
                            @endphp
                            <span class="{{ $isOverdue ? 'text-danger fw-bold' : 'text-body' }}">
                                {{ \Carbon\Carbon::parse($debt->due_date)->format('d M Y') }}
                                @if($isOverdue)
                                    <span class="badge bg-label-danger ms-1 small">Jatuh Tempo</span>
                                @endif
                            </span>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td>Rp {{ number_format($debt->total_debt, 0, ',', '.') }}</td>
                    <td>
                        <span class="fw-bold text-danger fs-6">
                            Rp {{ number_format($debt->remaining_debt, 0, ',', '.') }}
                        </span>
                    </td>
                    <td>
                        @if($debt->status === 'partial')
                            <span class="badge bg-label-warning">Dicicil</span>
                        @else
                            <span class="badge bg-label-danger">Belum Bayar</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <button type="button" 
                                class="btn btn-sm btn-success d-inline-flex align-items-center" 
                                data-bs-toggle="modal" 
                                data-bs-target="#bayarHutangModal" 
                                onclick="setupPaymentModal('{{ $debt->id }}', '{{ addslashes($debt->customer->name ?? 'Pelanggan') }}', '{{ addslashes($debt->sale ? $debt->sale->invoice_number : ('TRX-' . $debt->id)) }}', {{ $debt->remaining_debt }})">
                            <i class="bx bx-money me-1"></i> Bayar
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bx bx-check-circle fs-1 d-block mb-2 text-success"></i>
                            <h6 class="fw-bold mb-1">Semua Piutang Lunas!</h6>
                            <p class="small mb-3">Tidak ada data hutang pelanggan yang belum lunas saat ini.</p>
                            <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-arrow-back me-1"></i> Kembali ke Pelanggan
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($debts->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-3">
        <small class="text-muted">
            Menampilkan {{ $debts->firstItem() }} sampai {{ $debts->lastItem() }} dari {{ $debts->total() }} tagihan
        </small>
        <div>
            {{ $debts->links() }}
        </div>
    </div>
    @endif
</div>

<!-- Modal Pembayaran Hutang -->
<div class="modal fade" id="bayarHutangModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">
                    <i class="bx bx-wallet me-1 text-success"></i> Terima Pembayaran Hutang
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formBayarHutang" action="" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="bg-light p-3 rounded mb-3">
                        <div class="row mb-2">
                            <div class="col-sm-5 text-muted small">Nama Pelanggan:</div>
                            <div class="col-sm-7 fw-bold" id="modal-cust-name">-</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-5 text-muted small">No. Transaksi:</div>
                            <div class="col-sm-7 fw-semibold font-monospace" id="modal-trx">-</div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 text-muted small">Sisa Hutang:</div>
                            <div class="col-sm-7 fw-bold text-danger fs-6" id="modal-debt-text">-</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bayar_amount" class="form-label fw-bold">Jumlah Pembayaran <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" 
                                   id="bayar_amount" 
                                   name="amount" 
                                   class="form-control form-control-lg fw-bold text-end" 
                                   min="1" 
                                   step="any" 
                                   required />
                        </div>
                        <div class="form-text">Bisa dibayar lunas atau dicicil sebagian.</div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="cash" selected>Tunai (Cash)</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label for="note" class="form-label">Catatan (Opsional)</label>
                        <input type="text" id="note" name="note" class="form-control" placeholder="Contoh: Titipan cicilan pertama" />
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4">
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
    function setupPaymentModal(debtId, customer, trx, debtAmount) {
        const form = document.getElementById('formBayarHutang');
        form.action = `{{ url('customers/debt') }}/${debtId}/pay`;
        document.getElementById('modal-cust-name').innerText = customer;
        document.getElementById('modal-trx').innerText = trx;
        document.getElementById('modal-debt-text').innerText = 'Rp ' + Number(debtAmount).toLocaleString('id-ID');
        
        const amountInput = document.getElementById('bayar_amount');
        amountInput.value = debtAmount;
        amountInput.max = debtAmount;
    }
</script>
@endpush
