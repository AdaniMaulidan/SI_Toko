@extends('layouts.app')

@section('title', 'Data Hutang Pelanggan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Pelanggan /</span> Data Hutang
    </h4>
    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar Pelanggan
    </a>
</div>

<div class="row mb-4">
    <div class="col-md-6 col-lg-4">
        <div class="card bg-danger text-white mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white mb-1">Total Piutang (Hutang Pelanggan)</h6>
                        <h4 class="text-white fw-bold mb-0">Rp 1.450.000</h4>
                    </div>
                    <div class="avatar avatar-md">
                        <span class="avatar-initial rounded bg-white text-danger"><i class="bx bx-wallet fs-4"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Rincian Hutang Belum Lunas</h5>
    </div>
    
    <div class="table-responsive text-nowrap">
        <table class="table table-striped">
            <thead class="table-light">
                <tr>
                    <th>Pelanggan</th>
                    <th>No. Transaksi</th>
                    <th>Tgl Transaksi</th>
                    <th>Total Belanja</th>
                    <th>Sisa Hutang</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @php
                $debts = [
                    ['customer' => 'Budi Santoso', 'trx' => 'TRX-10022', 'date' => '05 Okt 2026', 'total' => 250000, 'debt' => 250000],
                    ['customer' => 'Ahmad Junaidi', 'trx' => 'TRX-10015', 'date' => '01 Okt 2026', 'total' => 500000, 'debt' => 300000],
                    ['customer' => 'Siti Aminah', 'trx' => 'TRX-0955', 'date' => '25 Sep 2026', 'total' => 1200000, 'debt' => 900000],
                ];
                @endphp

                @foreach($debts as $debt)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($debt['customer'], 0, 1) }}</span>
                            </div>
                            <span class="fw-medium text-heading">{{ $debt['customer'] }}</span>
                        </div>
                    </td>
                    <td><a href="{{ route('transactions.show', 1) }}">{{ $debt['trx'] }}</a></td>
                    <td>{{ $debt['date'] }}</td>
                    <td>Rp {{ number_format($debt['total'], 0, ',', '.') }}</td>
                    <td><span class="fw-bold text-danger">Rp {{ number_format($debt['debt'], 0, ',', '.') }}</span></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#bayarHutangModal" 
                            onclick="setupPaymentModal('{{ $debt['customer'] }}', '{{ $debt['trx'] }}', {{ $debt['debt'] }})">
                            <i class="bx bx-money me-1"></i> Bayar
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Pembayaran Hutang -->
<div class="modal fade" id="bayarHutangModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">Terima Pembayaran Hutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Pelanggan</div>
                        <div class="col-sm-8 fw-bold" id="modal-cust-name">-</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">No. Transaksi</div>
                        <div class="col-sm-8 fw-bold" id="modal-trx">-</div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-sm-4 text-muted">Sisa Hutang</div>
                        <div class="col-sm-8 fw-bold text-danger" id="modal-debt-text">-</div>
                    </div>
                    
                    <hr>
                    
                    <div class="row mb-3">
                        <label for="bayar" class="col-sm-4 col-form-label fw-bold">Jumlah Dibayar (Rp)</label>
                        <div class="col-sm-8">
                            <input type="number" id="bayar" name="bayar" class="form-control form-control-lg text-end" min="1" required>
                        </div>
                    </div>
                    <div class="row">
                        <label for="metode" class="col-sm-4 col-form-label">Metode Pembayaran</label>
                        <div class="col-sm-8">
                            <select class="form-select" id="metode" name="metode" required>
                                <option value="Tunai">Tunai</option>
                                <option value="Transfer">Transfer Bank</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Proses Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    function setupPaymentModal(customer, trx, debtAmount) {
        document.getElementById('modal-cust-name').innerText = customer;
        document.getElementById('modal-trx').innerText = trx;
        document.getElementById('modal-debt-text').innerText = 'Rp ' + debtAmount.toLocaleString('id-ID');
        document.getElementById('bayar').value = debtAmount;
        document.getElementById('bayar').max = debtAmount;
    }
</script>
@endsection
