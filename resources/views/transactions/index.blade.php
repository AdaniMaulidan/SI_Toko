@extends('layouts.app')

@section('title', 'Riwayat Transaksi Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Transaksi /</span> Riwayat Penjualan
    </h4>
    <a href="{{ route('kasir.index') }}" class="btn btn-primary">
        <i class="bx bx-cart me-1"></i> Buka Kasir
    </a>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row gx-3 gy-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label" for="filter-tanggal-mulai">Tanggal Mulai</label>
                <input type="date" id="filter-tanggal-mulai" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="filter-tanggal-akhir">Tanggal Akhir</label>
                <input type="date" id="filter-tanggal-akhir" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="filter-kasir">Kasir</label>
                <select id="filter-kasir" class="form-select">
                    <option value="">Semua Kasir</option>
                    <option value="admin">Admin</option>
                    <option value="kasir1">Kasir 1</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="search-trx">Cari No Transaksi</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" id="search-trx" class="form-control" placeholder="Contoh: TRX-...">
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary">Reset</button>
                <button type="button" class="btn btn-primary">Terapkan Filter</button>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Daftar Transaksi</h5>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bx bx-export me-1"></i> Export
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-file me-1"></i> PDF</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-table me-1"></i> Excel</a></li>
            </ul>
        </div>
    </div>
    
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>No. Transaksi</th>
                    <th>Tanggal & Waktu</th>
                    <th>Kasir</th>
                    <th>Total Item</th>
                    <th>Total Belanja</th>
                    <th>Metode</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @php
                $transactions = [
                    ['no' => 'TRX-10029', 'date' => '07 Okt 2026, 10:30', 'kasir' => 'Admin', 'items' => 5, 'total' => 145000, 'method' => 'Tunai'],
                    ['no' => 'TRX-10028', 'date' => '07 Okt 2026, 10:15', 'kasir' => 'Admin', 'items' => 2, 'total' => 35000, 'method' => 'QRIS'],
                    ['no' => 'TRX-10027', 'date' => '07 Okt 2026, 09:45', 'kasir' => 'Kasir 1', 'items' => 8, 'total' => 312000, 'method' => 'Transfer'],
                    ['no' => 'TRX-10026', 'date' => '07 Okt 2026, 09:10', 'kasir' => 'Kasir 1', 'items' => 1, 'total' => 15000, 'method' => 'Tunai'],
                    ['no' => 'TRX-10025', 'date' => '07 Okt 2026, 08:30', 'kasir' => 'Admin', 'items' => 3, 'total' => 42000, 'method' => 'E-Wallet'],
                ];
                @endphp

                @foreach($transactions as $trx)
                <tr>
                    <td><span class="fw-medium text-primary">{{ $trx['no'] }}</span></td>
                    <td>{{ $trx['date'] }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-xs me-2">
                                <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle">
                            </div>
                            <span>{{ $trx['kasir'] }}</span>
                        </div>
                    </td>
                    <td>{{ $trx['items'] }} pcs</td>
                    <td class="fw-medium">Rp {{ number_format($trx['total'], 0, ',', '.') }}</td>
                    <td>
                        @if($trx['method'] == 'Tunai')
                            <span class="badge bg-label-success"><i class="bx bx-money me-1"></i> Tunai</span>
                        @elseif($trx['method'] == 'QRIS')
                            <span class="badge bg-label-info"><i class="bx bx-qr-scan me-1"></i> QRIS</span>
                        @elseif($trx['method'] == 'Transfer')
                            <span class="badge bg-label-primary"><i class="bx bx-transfer me-1"></i> Transfer</span>
                        @else
                            <span class="badge bg-label-warning"><i class="bx bx-mobile me-1"></i> E-Wallet</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('transactions.show', 1) }}" class="btn btn-sm btn-icon btn-text-secondary" title="Detail">
                            <i class="icon-base bx bx-show text-primary"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary" title="Cetak Struk" onclick="window.open('{{ route('transactions.show', 1) }}?print=true', '_blank')">
                            <i class="icon-base bx bx-printer text-info"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="card-footer d-flex justify-content-between align-items-center">
        <span class="text-muted small">Menampilkan 1 sampai 5 dari 5 entri</span>
        <nav aria-label="Page navigation">
            <ul class="pagination mb-0">
                <li class="page-item prev disabled"><a class="page-link" href="javascript:void(0);"><i class="tf-icon bx bx-chevron-left"></i></a></li>
                <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                <li class="page-item next disabled"><a class="page-link" href="javascript:void(0);"><i class="tf-icon bx bx-chevron-right"></i></a></li>
            </ul>
        </nav>
    </div>
</div>
@endsection
