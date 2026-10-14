@extends('layouts.app')

@section('title', 'Tambah Produk Baru')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-1">
            <span class="text-muted fw-light">Master Data / Produk /</span> Tambah Produk
        </h4>
        <p class="text-muted mb-0 small">Masukkan informasi detail produk barang dagangan baru.</p>
    </div>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar
    </a>
</div>

{{-- Error General Alert --}}
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

<div class="row">
    <div class="col-12">
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="card mb-4 border-0 shadow-sm">
                <h5 class="card-header border-bottom py-3">
                    <i class="bx bx-info-circle me-1 text-primary"></i> Informasi Produk
                </h5>
                <div class="card-body pt-4">
                    
                    {{-- Barcode / Kode --}}
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="barcode">
                            Barcode / Kode Produk
                        </label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-barcode"></i></span>
                                <input type="text" 
                                       class="form-control font-monospace @error('barcode') is-invalid @enderror" 
                                       id="barcode" 
                                       name="barcode" 
                                       placeholder="Ketik manual atau klik Buat Otomatis" 
                                       value="{{ old('barcode') }}" />
                                <button class="btn btn-outline-primary" type="button" id="btn-generate-barcode">
                                    <i class="bx bx-refresh me-1"></i> Buat Otomatis
                                </button>
                            </div>
                            @error('barcode')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Biarkan kosong jika ingin sistem membuatkan barcode secara otomatis saat disimpan.</div>
                        </div>
                    </div>

                    {{-- Nama Produk --}}
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="name">
                            Nama Produk <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   placeholder="Contoh: Indomie Goreng Spesial 85g" 
                                   value="{{ old('name') }}" 
                                   required />
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Kategori & Satuan --}}
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="category_id">
                            Kategori <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-9">
                            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                <option value="" selected disabled>-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($categories->isEmpty())
                                <div class="form-text text-danger">Belum ada data kategori. Silakan tambahkan di Master Kategori terlebih dahulu.</div>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="unit_id">
                            Satuan <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-9">
                            <select class="form-select @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id" required>
                                <option value="" selected disabled>-- Pilih Satuan --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }} ({{ $unit->symbol }})
                                    </option>
                                @endforeach
                            </select>
                            @error('unit_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($units->isEmpty())
                                <div class="form-text text-danger">Belum ada data satuan. Silakan tambahkan di Master Satuan terlebih dahulu.</div>
                            @endif
                        </div>
                    </div>

                    {{-- Deskripsi Produk --}}
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="description">Deskripsi</label>
                        <div class="col-sm-9">
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3" 
                                      placeholder="Keterangan singkat produk, varian, atau spesifikasi (opsional)">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>
            </div>

            <!-- Harga & Stok Card -->
            <div class="card mb-4 border-0 shadow-sm">
                <h5 class="card-header border-bottom py-3">
                    <i class="bx bx-dollar-circle me-1 text-success"></i> Harga & Manajemen Stok
                </h5>
                <div class="card-body pt-4">
                    
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="cost_price">
                            Harga Beli (Modal) <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" 
                                       class="form-control @error('cost_price') is-invalid @enderror" 
                                       id="cost_price" 
                                       name="cost_price" 
                                       placeholder="0" 
                                       min="0" 
                                       step="any" 
                                       value="{{ old('cost_price') }}" 
                                       required />
                            </div>
                            @error('cost_price')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Harga pokok pembelian dari supplier.</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="selling_price">
                            Harga Jual <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" 
                                       class="form-control @error('selling_price') is-invalid @enderror" 
                                       id="selling_price" 
                                       name="selling_price" 
                                       placeholder="0" 
                                       min="0" 
                                       step="any" 
                                       value="{{ old('selling_price') }}" 
                                       required />
                            </div>
                            @error('selling_price')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text" id="margin-calc-info">
                                Margin Keuntungan: <span class="fw-bold text-success" id="calc-margin-val">Rp 0 (0%)</span>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="stock">
                            Stok Awal
                        </label>
                        <div class="col-sm-9">
                            <input type="number" 
                                   class="form-control @error('stock') is-invalid @enderror" 
                                   id="stock" 
                                   name="stock" 
                                   placeholder="0" 
                                   min="0" 
                                   value="{{ old('stock', 0) }}" />
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Jumlah stok fisik saat pertama kali dimasukkan ke sistem.</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="min_stock">
                            Minimum Stok (Warning Threshold)
                        </label>
                        <div class="col-sm-9">
                            <input type="number" 
                                   class="form-control @error('min_stock') is-invalid @enderror" 
                                   id="min_stock" 
                                   name="min_stock" 
                                   placeholder="5" 
                                   min="0" 
                                   value="{{ old('min_stock', 5) }}" />
                            @error('min_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Sistem akan menampilkan <strong>badge peringatan kuning</strong> jika stok ≤ angka ini.</div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Foto & Status Card -->
            <div class="card mb-4 border-0 shadow-sm">
                <h5 class="card-header border-bottom py-3">
                    <i class="bx bx-image me-1 text-warning"></i> Foto Produk & Status
                </h5>
                <div class="card-body pt-4">
                    
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="image">Foto Produk</label>
                        <div class="col-sm-9">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded border p-1 bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; overflow: hidden;">
                                    <img id="image-preview" src="#" alt="Preview" class="w-100 h-100 object-fit-cover d-none" />
                                    <i id="image-placeholder" class="bx bx-image fs-1 text-muted"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <input class="form-control @error('image') is-invalid @enderror" 
                                           type="file" 
                                           id="image" 
                                           name="image" 
                                           accept="image/png, image/jpeg, image/jpg, image/webp" />
                                    <div class="form-text">Format: JPG, JPEG, PNG, WebP. Maksimal: 2MB.</div>
                                    @error('image')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="is_active">Status Produk</label>
                        <div class="col-sm-9">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', '1') ? 'checked' : '' }} />
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Aktif (Tersedia untuk transaksi penjualan di Kasir)
                                </label>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bx bx-save me-1"></i> Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Auto generate barcode generator
    const btnGenerate = document.getElementById('btn-generate-barcode');
    const inputBarcode = document.getElementById('barcode');

    if (btnGenerate && inputBarcode) {
        btnGenerate.addEventListener('click', function () {
            const date = new Date();
            const yy = String(date.getFullYear()).slice(-2);
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            const random = Math.floor(1000 + Math.random() * 9000);
            inputBarcode.value = `PRD${yy}${mm}${dd}${random}`;
        });
    }

    // 2. Margin Calculator
    const costInput = document.getElementById('cost_price');
    const sellInput = document.getElementById('selling_price');
    const marginDisplay = document.getElementById('calc-margin-val');

    function updateMargin() {
        const cost = parseFloat(costInput.value) || 0;
        const sell = parseFloat(sellInput.value) || 0;
        const profit = sell - cost;
        const percent = cost > 0 ? ((profit / cost) * 100).toFixed(1) : (sell > 0 ? 100 : 0);

        if (profit >= 0) {
            marginDisplay.className = 'fw-bold text-success';
            marginDisplay.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(profit)} (+${percent}%)`;
        } else {
            marginDisplay.className = 'fw-bold text-danger';
            marginDisplay.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(profit)} (${percent}%) Rugi`;
        }
    }

    if (costInput && sellInput) {
        costInput.addEventListener('input', updateMargin);
        sellInput.addEventListener('input', updateMargin);
        updateMargin();
    }

    // 3. Image Preview
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');
    const imagePlaceholder = document.getElementById('image-placeholder');

    if (imageInput && imagePreview && imagePlaceholder) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    imagePreview.classList.remove('d-none');
                    imagePlaceholder.classList.add('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.src = '#';
                imagePreview.classList.add('d-none');
                imagePlaceholder.classList.remove('d-none');
            }
        });
    }
});
</script>
@endpush
