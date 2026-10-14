@extends('layouts.app')

@section('title', 'Edit Produk - ' . $product->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-1">
            <span class="text-muted fw-light">Master Data / Produk /</span> Edit Produk
        </h4>
        <p class="text-muted mb-0 small">Perbarui data produk <strong>{{ $product->name }}</strong>.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('products.show', $product) }}" class="btn btn-outline-info">
            <i class="bx bx-show me-1"></i> Lihat Detail
        </a>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>
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
        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Informasi Produk -->
            <div class="card mb-4 border-0 shadow-sm">
                <h5 class="card-header border-bottom py-3">
                    <i class="bx bx-info-circle me-1 text-primary"></i> Informasi Produk
                </h5>
                <div class="card-body pt-4">
                    
                    {{-- Barcode / Kode Produk --}}
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="barcode">
                            Barcode / Kode Produk <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-barcode"></i></span>
                                <input type="text" 
                                       class="form-control font-monospace @error('barcode') is-invalid @enderror" 
                                       id="barcode" 
                                       name="barcode" 
                                       placeholder="Kode barcode" 
                                       value="{{ old('barcode', $product->barcode) }}" 
                                       required />
                                <button class="btn btn-outline-primary" type="button" id="btn-generate-barcode">
                                    <i class="bx bx-refresh me-1"></i> Buat Ulang
                                </button>
                            </div>
                            @error('barcode')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Barcode harus unik dan tidak boleh sama dengan produk lain.</div>
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
                                   placeholder="Contoh: Indomie Goreng Spesial" 
                                   value="{{ old('name', $product->name) }}" 
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
                                <option value="" disabled>-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="unit_id">
                            Satuan <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-9">
                            <select class="form-select @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id" required>
                                <option value="" disabled>-- Pilih Satuan --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }} ({{ $unit->symbol }})
                                    </option>
                                @endforeach
                            </select>
                            @error('unit_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                      placeholder="Keterangan singkat produk (opsional)">{{ old('description', $product->description) }}</textarea>
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
                                       value="{{ old('cost_price', $product->cost_price) }}" 
                                       required />
                            </div>
                            @error('cost_price')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
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
                                       value="{{ old('selling_price', $product->selling_price) }}" 
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
                        <label class="col-sm-3 col-form-label" for="current_stock">
                            Stok Saat Ini
                        </label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control bg-light fw-bold" 
                                       id="current_stock" 
                                       value="{{ $product->stock }} {{ $product->unit->name ?? '' }}" 
                                       readonly />
                                @if($product->stock <= 0)
                                    <span class="input-group-text bg-label-danger text-danger fw-semibold">Habis</span>
                                @elseif($product->stock <= $product->min_stock)
                                    <span class="input-group-text bg-label-warning text-warning fw-semibold">Menipis</span>
                                @else
                                    <span class="input-group-text bg-label-success text-success fw-semibold">Aman</span>
                                @endif
                            </div>
                            <div class="form-text">
                                Stok fisik diperbarui otomatis melalui modul <strong>Kasir (POS)</strong>, <strong>Pembelian</strong>, atau <strong>Penyesuaian Stok (Opname)</strong>.
                            </div>
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
                                   value="{{ old('min_stock', $product->min_stock) }}" />
                            @error('min_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Peringatan stok menipis akan aktif jika stok berada di bawah atau sama dengan angka ini.</div>
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
                                <div class="rounded border p-1 bg-light d-flex align-items-center justify-content-center position-relative" style="width: 90px; height: 90px; overflow: hidden;">
                                    @if($product->image)
                                        <img id="image-preview" src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover" />
                                        <i id="image-placeholder" class="bx bx-image fs-1 text-muted d-none"></i>
                                    @else
                                        <img id="image-preview" src="#" alt="Preview" class="w-100 h-100 object-fit-cover d-none" />
                                        <i id="image-placeholder" class="bx bx-image fs-1 text-muted"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <input class="form-control @error('image') is-invalid @enderror" 
                                           type="file" 
                                           id="image" 
                                           name="image" 
                                           accept="image/png, image/jpeg, image/jpg, image/webp" />
                                    <div class="form-text">Format: JPG, JPEG, PNG, WebP. Maksimal: 2MB. Biarkan kosong jika tidak ingin mengubah foto.</div>
                                    @error('image')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror

                                    @if($product->image)
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1" />
                                            <label class="form-check-label text-danger small" for="remove_image">
                                                <i class="bx bx-trash me-1"></i> Hapus foto saat ini
                                            </label>
                                        </div>
                                    @endif
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
                                       {{ old('is_active', $product->is_active) ? 'checked' : '' }} />
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
                    <i class="bx bx-save me-1"></i> Simpan Perubahan
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

    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    imagePreview.classList.remove('d-none');
                    if (imagePlaceholder) imagePlaceholder.classList.add('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
@endpush
