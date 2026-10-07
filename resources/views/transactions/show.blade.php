@extends('layouts.app')

@section('title', 'Detail Transaksi')

@section('page-style')
<style>
    /* Print specific styles */
    @media print {
        body * {
            visibility: hidden;
        }
        .layout-menu, .layout-navbar, .footer, .btn, .breadcrumb-area {
            display: none !important;
        }
        #receipt-print-area, #receipt-print-area * {
            visibility: visible;
        }
        #receipt-print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            max-width: 300px; /* Thermal printer width */
            padding: 0;
            margin: 0;
        }
        .card { border: none !important; box-shadow: none !important; }
    }
    
    .border-dashed {
        border-bottom: 1px dashed #d9dee3;
    }
</style>
@endsection

@section('content')
<div class="breadcrumb-area d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Transaksi / <a href="{{ route('transactions.index') }}" class="text-muted">Riwayat</a> /</span> Detail
    </h4>
    <div>
        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary me-2">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="bx bx-printer me-1"></i> Cetak Struk
        </button>
    </div>
</div>

<div class="row">
    <!-- Transaction Details (Web View) -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Informasi Transaksi</h5>
                <span class="badge bg-label-success">Selesai</span>
            </div>
            <div class="card-body py-4">
                <div class="row mb-4">
                    <div class="col-sm-6 mb-3 mb-sm-0">
                        <small class="text-muted text-uppercase">No. Transaksi</small>
                        <h6 class="mb-0 text-primary fw-bold">TRX-10029</h6>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted text-uppercase">Tanggal & Waktu</small>
                        <h6 class="mb-0">07 Okt 2026, 10:30 WIB</h6>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-sm-6 mb-3 mb-sm-0">
                        <small class="text-muted text-uppercase">Kasir</small>
                        <div class="d-flex align-items-center mt-1">
                            <div class="avatar avatar-xs me-2">
                                <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle">
                            </div>
                            <h6 class="mb-0">Admin Utama</h6>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted text-uppercase">Metode Pembayaran</small>
                        <h6 class="mb-0 mt-1"><i class="bx bx-money text-success me-1"></i> Tunai</h6>
                    </div>
                </div>

                <hr class="mx-n4">
                <h6 class="mb-3">Item Pembelian</h6>
                
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>
                                    <span class="fw-medium">Indomie Goreng Spesial</span><br>
                                    <small class="text-muted">BRG-001</small>
                                </td>
                                <td>Rp 3.000</td>
                                <td>5</td>
                                <td class="text-end">Rp 15.000</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>
                                    <span class="fw-medium">Beras Sania 5kg</span><br>
                                    <small class="text-muted">BRG-003</small>
                                </td>
                                <td>Rp 72.000</td>
                                <td>1</td>
                                <td class="text-end">Rp 72.000</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>
                                    <span class="fw-medium">Minyak Bimoli 2L</span><br>
                                    <small class="text-muted">BRG-008</small>
                                </td>
                                <td>Rp 38.000</td>
                                <td>1</td>
                                <td class="text-end">Rp 38.000</td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>
                                    <span class="fw-medium">Aqua Botol 600ml</span><br>
                                    <small class="text-muted">BRG-002</small>
                                </td>
                                <td>Rp 3.500</td>
                                <td>2</td>
                                <td class="text-end">Rp 7.000</td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td>
                                    <span class="fw-medium">Teh Pucuk Harum</span><br>
                                    <small class="text-muted">BRG-007</small>
                                </td>
                                <td>Rp 4.000</td>
                                <td>3</td>
                                <td class="text-end">Rp 12.000</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end fw-bold text-muted">Subtotal</td>
                                <td class="text-end fw-bold">Rp 144.000</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end fw-bold text-muted">Kantong Plastik</td>
                                <td class="text-end fw-bold">Rp 1.000</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end fw-bold text-primary fs-5">TOTAL</td>
                                <td class="text-end fw-bold text-primary fs-5">Rp 145.000</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6 offset-md-6">
                        <div class="bg-lighter rounded p-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Bayar</span>
                                <span>Rp 145.000</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tunai Diterima</span>
                                <span>Rp 150.000</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold border-top pt-2">
                                <span class="text-success">Kembalian</span>
                                <span class="text-success">Rp 5.000</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Preview (Printable Area) -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100 bg-lighter border-0">
            <div class="card-header bg-transparent text-center border-bottom pb-2">
                <h6 class="mb-0 text-muted"><i class="bx bx-receipt me-1"></i> Preview Struk (Thermal)</h6>
            </div>
            <div class="card-body p-4 d-flex justify-content-center bg-secondary" style="background-color: #f0f2f5 !important;">
                
                <!-- Actual Thermal Receipt Container -->
                <div id="receipt-print-area" class="bg-white p-3 shadow-sm" style="width: 300px; min-height: 400px; font-family: 'Courier New', Courier, monospace; font-size: 13px; color: #000;">
                    
                    <!-- Header -->
                    <div class="text-center mb-3">
                        <h4 class="fw-bold mb-1" style="font-size: 18px; color: #000;">SI TOKO</h4>
                        <div style="font-size: 12px; line-height: 1.2;">
                            Jl. Contoh Alamat No. 123<br>
                            Kecamatan, Kota<br>
                            Telp: 0812-3456-7890
                        </div>
                    </div>

                    <!-- Meta -->
                    <div class="border-dashed pb-2 mb-2" style="font-size: 12px;">
                        <div class="d-flex justify-content-between"><span>No:</span> <span>TRX-10029</span></div>
                        <div class="d-flex justify-content-between"><span>Tgl:</span> <span>07/10/2026 10:30</span></div>
                        <div class="d-flex justify-content-between"><span>Ksr:</span> <span>Admin Utama</span></div>
                    </div>

                    <!-- Items -->
                    <div class="mb-2" style="font-size: 12px;">
                        <div class="mb-2">
                            <div>Indomie Goreng Spsl</div>
                            <div class="d-flex justify-content-between">
                                <span>5 x 3.000</span>
                                <span>15.000</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div>Beras Sania 5kg</div>
                            <div class="d-flex justify-content-between">
                                <span>1 x 72.000</span>
                                <span>72.000</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div>Minyak Bimoli 2L</div>
                            <div class="d-flex justify-content-between">
                                <span>1 x 38.000</span>
                                <span>38.000</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div>Aqua Botol 600ml</div>
                            <div class="d-flex justify-content-between">
                                <span>2 x 3.500</span>
                                <span>7.000</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div>Teh Pucuk Harum</div>
                            <div class="d-flex justify-content-between">
                                <span>3 x 4.000</span>
                                <span>12.000</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div>Kantong Plastik</div>
                            <div class="d-flex justify-content-between">
                                <span>1 x 1.000</span>
                                <span>1.000</span>
                            </div>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="border-dashed pb-2 mb-2 pt-2 border-top" style="border-top: 1px dashed #d9dee3;">
                        <div class="d-flex justify-content-between fw-bold"><span>TOTAL</span> <span>145.000</span></div>
                        <div class="d-flex justify-content-between mt-1"><span>Tunai</span> <span>150.000</span></div>
                        <div class="d-flex justify-content-between"><span>Kembali</span> <span>5.000</span></div>
                    </div>

                    <!-- Footer -->
                    <div class="text-center mt-3" style="font-size: 12px;">
                        <p class="mb-1">Terima kasih atas kunjungan Anda!</p>
                        <p class="mb-0">Barang yang sudah dibeli<br>tidak dapat ditukar/dikembalikan.</p>
                    </div>

                </div>
                <!-- / Thermal Receipt Container -->

            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    // Print logic check: if URL has ?print=true, trigger print dialog automatically
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print') === 'true') {
            setTimeout(() => {
                window.print();
            }, 500);
        }
    };
</script>
@endsection
