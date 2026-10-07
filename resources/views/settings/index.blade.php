@extends('layouts.app')

@section('title', 'Pengaturan Toko')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Pengaturan /</span> Profil Toko
    </h4>
</div>

<div class="row">
    <!-- Kolom Kiri: Menu Pengaturan -->
    <div class="col-md-3 mb-4">
        <div class="list-group">
            <a href="#informasi-toko" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                <i class="bx bx-store me-2"></i> Informasi Toko
            </a>
            <a href="#pengaturan-struk" class="list-group-item list-group-item-action" data-bs-toggle="list">
                <i class="bx bx-receipt me-2"></i> Pengaturan Struk
            </a>
            <a href="#keuangan-pajak" class="list-group-item list-group-item-action" data-bs-toggle="list">
                <i class="bx bx-wallet me-2"></i> Pajak & Keuangan
            </a>
        </div>
    </div>

    <!-- Kolom Kanan: Konten Pengaturan -->
    <div class="col-md-9">
        <div class="tab-content p-0 border-0 bg-transparent">
            
            <!-- Tab: Informasi Toko -->
            <div class="tab-pane fade show active" id="informasi-toko">
                <div class="card mb-4">
                    <h5 class="card-header border-bottom">Profil & Identitas Toko</h5>
                    <div class="card-body mt-4">
                        <form action="#" method="POST">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-12 text-center mb-4">
                                    <div class="position-relative d-inline-block">
                                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Logo Toko" class="rounded-circle border p-1" width="120" height="120">
                                        <label for="upload-logo" class="btn btn-sm btn-icon btn-primary position-absolute bottom-0 end-0 rounded-circle">
                                            <i class="bx bx-upload"></i>
                                            <input type="file" id="upload-logo" class="d-none" accept="image/png, image/jpeg">
                                        </label>
                                    </div>
                                    <p class="text-muted mt-2 mb-0"><small>Format JPG/PNG maksimal 2MB.</small></p>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="nama_toko">Nama Toko / Bisnis</label>
                                    <input type="text" id="nama_toko" name="nama_toko" class="form-control" value="SI TOKO MAJU">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="pemilik">Nama Pemilik (Owner)</label>
                                    <input type="text" id="pemilik" name="pemilik" class="form-control" value="Adani Maulidan">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="telepon">Nomor Telepon / WA</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                        <input type="text" id="telepon" name="telepon" class="form-control" value="0812-3456-7890">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="email">Email Bisnis</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                        <input type="email" id="email" name="email" class="form-control" value="info@sikasir.com">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="alamat">Alamat Lengkap Toko</label>
                                    <textarea id="alamat" name="alamat" class="form-control" rows="3">Jl. Contoh Alamat No. 123, Kecamatan, Kota</textarea>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary">Simpan Profil Toko</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tab: Pengaturan Struk -->
            <div class="tab-pane fade" id="pengaturan-struk">
                <div class="card mb-4">
                    <h5 class="card-header border-bottom">Pengaturan Cetak Struk (Thermal)</h5>
                    <div class="card-body mt-4">
                        <form action="#" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="ukuran_kertas">Ukuran Kertas Printer</label>
                                    <select id="ukuran_kertas" class="form-select">
                                        <option value="58mm" selected>58mm (Printer Kecil)</option>
                                        <option value="80mm">80mm (Printer Standar)</option>
                                        <option value="a4">A4 (Printer Biasa)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block">Cetak Otomatis Setelah Bayar?</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="auto_print" checked>
                                        <label class="form-check-label" for="auto_print">Ya, cetak otomatis</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="header_struk">Teks Header Struk (Opsional)</label>
                                    <textarea id="header_struk" class="form-control" rows="2" placeholder="Contoh: Toko Cabang Pusat"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="footer_struk">Pesan Footer Struk (Bawah)</label>
                                    <textarea id="footer_struk" class="form-control" rows="3">Terima kasih atas kunjungan Anda!
Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</textarea>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-end">
                                <button type="button" class="btn btn-outline-secondary me-2"><i class="bx bx-printer me-1"></i> Test Print</button>
                                <button type="submit" class="btn btn-primary">Simpan Pengaturan Struk</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tab: Pajak & Keuangan -->
            <div class="tab-pane fade" id="keuangan-pajak">
                <div class="card mb-4">
                    <h5 class="card-header border-bottom">Pajak & Pengaturan Kasir</h5>
                    <div class="card-body mt-4">
                        <form action="#" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label d-block">Aktifkan Pajak (PPN)?</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="pajak_aktif">
                                        <label class="form-check-label" for="pajak_aktif">Ya, tambahkan pajak di kasir</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="persentase_pajak">Persentase PPN (%)</label>
                                    <div class="input-group">
                                        <input type="number" id="persentase_pajak" class="form-control" value="11" disabled>
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <hr>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="mata_uang">Mata Uang</label>
                                    <select id="mata_uang" class="form-select">
                                        <option value="IDR" selected>Rupiah (Rp)</option>
                                        <option value="USD">US Dollar ($)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="pembulatan">Pembulatan Uang Kembalian</label>
                                    <select id="pembulatan" class="form-select">
                                        <option value="0">Tidak ada pembulatan</option>
                                        <option value="100">Bulatkan Rp 100 terdekat</option>
                                        <option value="500" selected>Bulatkan Rp 500 terdekat</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary">Simpan Keuangan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    // Toggle input disabled status when switch is clicked
    document.getElementById('pajak_aktif').addEventListener('change', function() {
        document.getElementById('persentase_pajak').disabled = !this.checked;
    });
</script>
@endsection
