@extends('layouts.app')

@section('title', 'Pengaturan Toko')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-0">
            <span class="text-muted fw-light">Pengaturan /</span> Profil Toko & Struk
        </h4>
        <small class="text-muted">Kelola identitas usaha, kontak WhatsApp, logo, format struk kasir thermal, dan pajak toko</small>
    </div>
</div>

{{-- Alert Notifikasi Flash --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
    <i class="bx bx-check-circle fs-4 me-2"></i>
    <div>{{ session('success') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center mb-1">
        <i class="bx bx-error-circle fs-4 me-2"></i>
        <strong>Periksa kembali formulir berikut:</strong>
    </div>
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" id="form-settings">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Kolom Kiri: Tab Pengaturan & Formulir Input -->
        <div class="col-lg-7 col-xl-8 mb-4">
            <div class="nav-align-top mb-4">
                <ul class="nav nav-pills mb-3 gap-2" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active d-flex align-items-center" role="tab" data-bs-toggle="tab" data-bs-target="#tab-profil">
                            <i class="bx bx-store-alt me-1"></i> Profil Toko
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link d-flex align-items-center" role="tab" data-bs-toggle="tab" data-bs-target="#tab-struk">
                            <i class="bx bx-receipt me-1"></i> Format Struk Kasir
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link d-flex align-items-center" role="tab" data-bs-toggle="tab" data-bs-target="#tab-pajak">
                            <i class="bx bx-wallet me-1"></i> Pajak & Mata Uang
                        </button>
                    </li>
                </ul>

                <div class="tab-content shadow-sm rounded border-0 p-0">
                    
                    {{-- TAB 1: PROFIL & IDENTITAS TOKO --}}
                    <div class="tab-pane fade show active" id="tab-profil" role="tabpanel">
                        <div class="card border-0">
                            <div class="card-header border-bottom bg-transparent py-3">
                                <h5 class="card-title mb-0 fw-semibold">Identitas & Logo Usaha</h5>
                            </div>
                            <div class="card-body py-4">
                                
                                <!-- Upload & Preview Logo Toko -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-sm-auto text-center mb-3 mb-sm-0">
                                        <div class="position-relative d-inline-block">
                                            @if($setting->logo && file_exists(public_path('storage/' . $setting->logo)))
                                                <img src="{{ asset('storage/' . $setting->logo) }}" 
                                                     alt="Logo Toko" 
                                                     id="logo-preview-img" 
                                                     class="rounded border p-1 shadow-sm object-fit-contain bg-white" 
                                                     width="120" height="120">
                                            @else
                                                <img src="{{ asset('assets/img/avatars/1.png') }}" 
                                                     alt="Logo Toko" 
                                                     id="logo-preview-img" 
                                                     class="rounded border p-1 shadow-sm object-fit-cover bg-light" 
                                                     width="120" height="120">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <label class="form-label fw-semibold" for="logo">Unggah Logo Toko</label>
                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                            <label for="logo" class="btn btn-outline-primary btn-sm mb-0">
                                                <i class="bx bx-upload me-1"></i> Pilih Gambar Logo
                                                <input type="file" id="logo" name="logo" class="d-none" accept="image/png, image/jpeg, image/webp, image/svg+xml">
                                            </label>

                                            @if($setting->logo)
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDeleteLogo()">
                                                <i class="bx bx-trash me-1"></i> Hapus Logo
                                            </button>
                                            @endif
                                        </div>
                                        <small class="text-muted d-block">Mendukung format PNG, JPG, WEBP, atau SVG. Ukuran maksimal 2MB. Logo akan ditampilkan pada struk belanja dan kop laporan resmi.</small>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="store_name">
                                            Nama Toko / Bisnis <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-store"></i></span>
                                            <input type="text" 
                                                   id="store_name" 
                                                   name="store_name" 
                                                   class="form-control @error('store_name') is-invalid @enderror" 
                                                   value="{{ old('store_name', $setting->store_name) }}" 
                                                   required 
                                                   placeholder="Contoh: SI Toko Retail">
                                        </div>
                                        @error('store_name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="phone">
                                            Nomor Telepon / WhatsApp
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bxl-whatsapp text-success fs-5"></i></span>
                                            <input type="text" 
                                                   id="phone" 
                                                   name="phone" 
                                                   class="form-control @error('phone') is-invalid @enderror" 
                                                   value="{{ old('phone', $setting->phone) }}" 
                                                   placeholder="Contoh: 0812-3456-7890">
                                        </div>
                                        @error('phone')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold" for="email">
                                            Email Bisnis (Opsional)
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                            <input type="email" 
                                                   id="email" 
                                                   name="email" 
                                                   class="form-control @error('email') is-invalid @enderror" 
                                                   value="{{ old('email', $setting->email) }}" 
                                                   placeholder="Contoh: info@tokokita.com">
                                        </div>
                                        @error('email')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold" for="address">
                                            Alamat Lengkap Toko
                                        </label>
                                        <textarea id="address" 
                                                  name="address" 
                                                  class="form-control @error('address') is-invalid @enderror" 
                                                  rows="3" 
                                                  placeholder="Masukkan alamat toko, jalan, nomor, kecamatan, dan kota...">{{ old('address', $setting->address) }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: FORMAT STRUK KASIR THERMAL --}}
                    <div class="tab-pane fade" id="tab-struk" role="tabpanel">
                        <div class="card border-0">
                            <div class="card-header border-bottom bg-transparent py-3">
                                <h5 class="card-title mb-0 fw-semibold">Format Teks Struk Kasir Thermal</h5>
                            </div>
                            <div class="card-body py-4">
                                <div class="alert alert-primary d-flex align-items-center mb-4" role="alert">
                                    <i class="bx bx-info-circle fs-4 me-2"></i>
                                    <div>
                                        Teks header & footer ini akan tercetak otomatis pada struk thermal kasir (58mm & 80mm) serta nota cetak transaksi.
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold" for="header_receipt">
                                            Teks Header Struk Tambahan
                                        </label>
                                        <input type="text" 
                                               id="header_receipt" 
                                               name="header_receipt" 
                                               class="form-control @error('header_receipt') is-invalid @enderror" 
                                               value="{{ old('header_receipt', $setting->header_receipt) }}" 
                                               placeholder="Contoh: Cabang Utama - Melayani Grosir & Eceran">
                                        <small class="text-muted">Teks ini tampil tepat di bawah nama toko dan alamat pada struk belanja.</small>
                                        @error('header_receipt')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold" for="footer_receipt">
                                            Pesan Footer Struk (Bagian Bawah)
                                        </label>
                                        <textarea id="footer_receipt" 
                                                  name="footer_receipt" 
                                                  class="form-control @error('footer_receipt') is-invalid @enderror" 
                                                  rows="3" 
                                                  placeholder="Contoh: Terima kasih atas kunjungan Anda! Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.">{{ old('footer_receipt', $setting->footer_receipt) }}</textarea>
                                        <small class="text-muted">Pesan terima kasih, media sosial, atau kebijakan penukaran barang toko Anda.</small>
                                        @error('footer_receipt')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 3: PAJAK & MATA UANG --}}
                    <div class="tab-pane fade" id="tab-pajak" role="tabpanel">
                        <div class="card border-0">
                            <div class="card-header border-bottom bg-transparent py-3">
                                <h5 class="card-title mb-0 fw-semibold">Konfigurasi Mata Uang & Pajak</h5>
                            </div>
                            <div class="card-body py-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="currency_symbol">
                                            Simbol Mata Uang
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
                                            <input type="text" 
                                                   id="currency_symbol" 
                                                   name="currency_symbol" 
                                                   class="form-control @error('currency_symbol') is-invalid @enderror" 
                                                   value="{{ old('currency_symbol', $setting->currency_symbol ?? 'Rp') }}" 
                                                   placeholder="Rp">
                                        </div>
                                        <small class="text-muted">Simbol mata uang yang digunakan pada kasir & laporan (default: Rp).</small>
                                        @error('currency_symbol')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="tax_percentage">
                                            Tarif PPN / Pajak Kasir (%)
                                        </label>
                                        <div class="input-group">
                                            <input type="number" 
                                                   id="tax_percentage" 
                                                   name="tax_percentage" 
                                                   step="0.01" 
                                                   min="0" 
                                                   max="100" 
                                                   class="form-control @error('tax_percentage') is-invalid @enderror" 
                                                   value="{{ old('tax_percentage', $setting->tax_percentage ?? 0) }}" 
                                                   placeholder="0">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <small class="text-muted">Isi 0 jika toko tidak membebankan PPN ke pembeli.</small>
                                        @error('tax_percentage')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Tombol Simpan Perubahan -->
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Pastikan data yang Anda masukkan sudah sesuai sebelum menyimpan.</span>
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-1 px-4">
                        <i class="bx bx-save fs-5"></i>
                        <span>Simpan Pengaturan</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Live Thermal Receipt Preview -->
        <div class="col-lg-5 col-xl-4 mb-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold d-flex align-items-center gap-1">
                        <i class="bx bx-printer text-primary"></i> Preview Struk Kasir
                    </h6>
                    <span class="badge bg-label-info">Live 58mm</span>
                </div>
                <div class="card-body p-3 bg-light d-flex justify-content-center">
                    
                    <!-- Paper Mockup -->
                    <div id="thermal-receipt-mockup" 
                         class="bg-white p-3 shadow-sm rounded text-dark" 
                         style="width: 250px; font-family: 'Courier New', Courier, monospace; font-size: 11px; line-height: 1.35; border: 1px solid #e0e0e0;">
                        
                        <!-- Logo Preview in Mockup -->
                        <div class="text-center mb-1 {{ ($setting->logo && file_exists(public_path('storage/' . $setting->logo))) ? '' : 'd-none' }}" id="preview-logo-container">
                            <img src="{{ $setting->logo ? asset('storage/' . $setting->logo) : '' }}" 
                                 alt="Logo" 
                                 id="preview-receipt-logo" 
                                 style="max-height: 38px; max-width: 100px; object-fit: contain;">
                        </div>

                        <!-- Header Info -->
                        <div class="text-center mb-2">
                            <div class="fw-bold fs-6 text-uppercase" id="preview-store-name">
                                {{ $setting->store_name ?: 'SI TOKO RETAIL' }}
                            </div>
                            <div style="font-size: 9.5px;" id="preview-address">
                                {{ $setting->address ?: 'Jl. Contoh Alamat Toko No. 123' }}
                            </div>
                            <div style="font-size: 9.5px;" id="preview-phone">
                                {{ $setting->phone ? 'Telp: ' . $setting->phone : 'Telp: 0812-3456-7890' }}
                            </div>
                            <div style="font-size: 9px; font-style: italic; margin-top: 2px;" id="preview-header-receipt" class="{{ $setting->header_receipt ? '' : 'd-none' }}">
                                {{ $setting->header_receipt }}
                            </div>
                        </div>

                        <div style="border-top: 1px dashed #444; margin: 4px 0;"></div>

                        <!-- Meta Info Dummy -->
                        <div style="font-size: 9.5px;">
                            <div class="d-flex justify-content-between">
                                <span>No: TRX-0001</span>
                                <span>{{ date('d/m/y H:i') }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Kasir: Kasir Toko</span>
                                <span>Umum</span>
                            </div>
                        </div>

                        <div style="border-top: 1px dashed #444; margin: 4px 0;"></div>

                        <!-- Sample Items -->
                        <div style="font-size: 9.5px;">
                            <div class="d-flex justify-content-between">
                                <span>Gula Pasir 1kg</span>
                                <span>14.500</span>
                            </div>
                            <div class="text-muted" style="font-size: 8.5px;">1 x 14.500</div>

                            <div class="d-flex justify-content-between mt-1">
                                <span>Minyak Goreng 2L</span>
                                <span>32.000</span>
                            </div>
                            <div class="text-muted" style="font-size: 8.5px;">1 x 32.000</div>
                        </div>

                        <div style="border-top: 1px dashed #444; margin: 4px 0;"></div>

                        <!-- Totals -->
                        <div style="font-size: 9.5px;">
                            <div class="d-flex justify-content-between">
                                <span>Subtotal</span>
                                <span>46.500</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>TOTAL</span>
                                <span>46.500</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Tunai</span>
                                <span>50.000</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Kembali</span>
                                <span>3.500</span>
                            </div>
                        </div>

                        <div style="border-top: 1px dashed #444; margin: 4px 0;"></div>

                        <!-- Footer Text -->
                        <div class="text-center mt-2" style="font-size: 9px;" id="preview-footer-receipt">
                            {{ $setting->footer_receipt ?: 'Terima kasih atas kunjungan Anda! Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.' }}
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top py-2 text-center text-muted small">
                    <i class="bx bx-bulb text-warning me-1"></i> Preview berubah otomatis saat Anda mengetik.
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Form Terpisah untuk Menghapus Logo --}}
@if($setting->logo)
<form id="form-delete-logo" action="{{ route('settings.remove-logo') }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endif

@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Elements Input
        const storeNameInput = document.getElementById('store_name');
        const phoneInput     = document.getElementById('phone');
        const addressInput   = document.getElementById('address');
        const headerInput    = document.getElementById('header_receipt');
        const footerInput    = document.getElementById('footer_receipt');
        const logoInput      = document.getElementById('logo');

        // Elements Preview
        const previewStoreName      = document.getElementById('preview-store-name');
        const previewPhone          = document.getElementById('preview-phone');
        const previewAddress        = document.getElementById('preview-address');
        const previewHeaderReceipt  = document.getElementById('preview-header-receipt');
        const previewFooterReceipt  = document.getElementById('preview-footer-receipt');
        const previewReceiptLogo    = document.getElementById('preview-receipt-logo');
        const previewLogoContainer  = document.getElementById('preview-logo-container');
        const logoPreviewImg        = document.getElementById('logo-preview-img');

        // Live update Nama Toko
        if (storeNameInput && previewStoreName) {
            storeNameInput.addEventListener('input', function () {
                previewStoreName.textContent = this.value.trim() || 'SI TOKO RETAIL';
            });
        }

        // Live update No Telepon
        if (phoneInput && previewPhone) {
            phoneInput.addEventListener('input', function () {
                previewPhone.textContent = this.value.trim() ? ('Telp: ' + this.value.trim()) : 'Telp: 0812-3456-7890';
            });
        }

        // Live update Alamat
        if (addressInput && previewAddress) {
            addressInput.addEventListener('input', function () {
                previewAddress.textContent = this.value.trim() || 'Jl. Contoh Alamat Toko No. 123';
            });
        }

        // Live update Header Struk
        if (headerInput && previewHeaderReceipt) {
            headerInput.addEventListener('input', function () {
                if (this.value.trim()) {
                    previewHeaderReceipt.textContent = this.value.trim();
                    previewHeaderReceipt.classList.remove('d-none');
                } else {
                    previewHeaderReceipt.textContent = '';
                    previewHeaderReceipt.classList.add('d-none');
                }
            });
        }

        // Live update Footer Struk
        if (footerInput && previewFooterReceipt) {
            footerInput.addEventListener('input', function () {
                previewFooterReceipt.textContent = this.value.trim() || 'Terima kasih atas kunjungan Anda!';
            });
        }

        // Preview saat pilih file logo baru
        if (logoInput) {
            logoInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        if (logoPreviewImg) {
                            logoPreviewImg.src = event.target.result;
                        }
                        if (previewReceiptLogo) {
                            previewReceiptLogo.src = event.target.result;
                        }
                        if (previewLogoContainer) {
                            previewLogoContainer.classList.remove('d-none');
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });

    function confirmDeleteLogo() {
        if (confirm('Apakah Anda yakin ingin menghapus logo toko?')) {
            const form = document.getElementById('form-delete-logo');
            if (form) form.submit();
        }
    }
</script>
@endsection
