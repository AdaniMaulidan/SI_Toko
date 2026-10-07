@extends('layouts.app')

@section('title', 'Keuangan & Kas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Keuangan /</span> Buku Kas (Cash Flow)
    </h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#mutasiKasModal">
        <i class="bx bx-transfer-alt me-1"></i> Mutasi Kas / Setor Bank
    </button>
</div>

<!-- Ringkasan Saldo -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body text-center">
                <i class="bx bx-wallet fs-1 mb-2"></i>
                <h6 class="text-white mb-2">Total Saldo Kas (Tunai)</h6>
                <h3 class="text-white fw-bold mb-0">Rp 12.500.000</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body text-center">
                <i class="bx bx-trending-up fs-1 mb-2"></i>
                <h6 class="text-white mb-2">Pemasukan Bulan Ini</h6>
                <h3 class="text-white fw-bold mb-0">Rp 28.450.000</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white h-100">
            <div class="card-body text-center">
                <i class="bx bx-trending-down fs-1 mb-2"></i>
                <h6 class="text-white mb-2">Pengeluaran Bulan Ini</h6>
                <h3 class="text-white fw-bold mb-0">Rp 18.250.000</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Riwayat Arus Kas (Buku Kas)</h5>
        <button class="btn btn-sm btn-outline-secondary">
            <i class="bx bx-export me-1"></i> Export Excel
        </button>
    </div>
    <div class="card-body mt-4">
        <div class="row gx-3 gy-2 align-items-end mb-3">
            <div class="col-md-3">
                <label class="form-label" for="filter-tanggal-mulai">Tanggal Mulai</label>
                <input type="date" id="filter-tanggal-mulai" class="form-control" value="{{ date('Y-m-01') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="filter-tanggal-akhir">Tanggal Akhir</label>
                <input type="date" id="filter-tanggal-akhir" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="filter-jenis">Jenis Arus</label>
                <select id="filter-jenis" class="form-select">
                    <option value="">Semua Arus Kas</option>
                    <option value="Masuk">Kas Masuk (In)</option>
                    <option value="Keluar">Kas Keluar (Out)</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-primary w-100 mt-4">Terapkan Filter</button>
            </div>
        </div>

        <div class="table-responsive text-nowrap border rounded mt-3">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Keterangan Referensi</th>
                        <th>Jenis</th>
                        <th>Nominal</th>
                        <th>Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $cashflows = [
                        ['date' => '07 Okt 2026, 14:00', 'ref' => 'Penjualan (TRX-10029)', 'type' => 'Masuk', 'amount' => 145000, 'balance' => 12500000],
                        ['date' => '07 Okt 2026, 10:30', 'ref' => 'Penjualan (TRX-10028)', 'type' => 'Masuk', 'amount' => 35000, 'balance' => 12355000],
                        ['date' => '06 Okt 2026, 09:00', 'ref' => 'Pembelian Barang (PO-202610-005)', 'type' => 'Keluar', 'amount' => 2550000, 'balance' => 12320000],
                        ['date' => '05 Okt 2026, 16:00', 'ref' => 'Pengeluaran (Bayar Listrik)', 'type' => 'Keluar', 'amount' => 850000, 'balance' => 14870000],
                        ['date' => '05 Okt 2026, 13:00', 'ref' => 'Pelunasan Hutang (Budi Santoso)', 'type' => 'Masuk', 'amount' => 250000, 'balance' => 15720000],
                        ['date' => '01 Okt 2026, 08:00', 'ref' => 'Setor ke Rekening BCA', 'type' => 'Keluar', 'amount' => 10000000, 'balance' => 15470000],
                    ];
                    @endphp

                    @foreach($cashflows as $cf)
                    <tr>
                        <td>{{ $cf['date'] }}</td>
                        <td><span class="fw-medium">{{ $cf['ref'] }}</span></td>
                        <td>
                            @if($cf['type'] == 'Masuk')
                                <span class="badge bg-label-success"><i class="bx bx-down-arrow-alt me-1"></i> Masuk</span>
                            @else
                                <span class="badge bg-label-danger"><i class="bx bx-up-arrow-alt me-1"></i> Keluar</span>
                            @endif
                        </td>
                        <td>
                            @if($cf['type'] == 'Masuk')
                                <span class="text-success fw-bold">+ Rp {{ number_format($cf['amount'], 0, ',', '.') }}</span>
                            @else
                                <span class="text-danger fw-bold">- Rp {{ number_format($cf['amount'], 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="fw-bold">Rp {{ number_format($cf['balance'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Mutasi Kas -->
<div class="modal fade" id="mutasiKasModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold">Mutasi Kas / Setor Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="alert alert-info d-flex" role="alert">
                        <span class="alert-icon rounded-circle bg-info text-white me-3 p-2">
                            <i class="bx bx-info-circle"></i>
                        </span>
                        <div>Gunakan fitur ini untuk mencatat perpindahan uang fisik, misalnya menyetorkan uang tunai kasir ke rekening bank toko.</div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-12">
                            <label class="form-label d-flex justify-content-between">
                                Saldo Tunai Saat Ini
                                <span class="fw-bold text-primary">Rp 12.500.000</span>
                            </label>
                        </div>
                        <div class="col-12">
                            <label for="tanggal_mutasi" class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal_mutasi" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label for="nominal_mutasi" class="form-label">Nominal Dipindahkan (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="nominal_mutasi" name="nominal" class="form-control" placeholder="0" max="12500000" min="1" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="tujuan_mutasi" class="form-label">Tujuan / Rekening <span class="text-danger">*</span></label>
                            <select id="tujuan_mutasi" name="tujuan" class="form-select" required>
                                <option value="" selected disabled>Pilih Tujuan</option>
                                <option value="BCA">Rekening BCA (Operasional)</option>
                                <option value="Mandiri">Rekening Mandiri (Owner)</option>
                                <option value="Lainnya">Lainnya...</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="keterangan_mutasi" class="form-label">Keterangan / Catatan Tambahan</label>
                            <textarea id="keterangan_mutasi" name="keterangan" class="form-control" rows="2" placeholder="Contoh: Setoran uang hasil penjualan minggu pertama"></textarea>
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
@endsection
