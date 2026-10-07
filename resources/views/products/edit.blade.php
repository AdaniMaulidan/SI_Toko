@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Produk /</span> Edit Produk
    </h4>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <h5 class="card-header border-bottom">Informasi Produk</h5>
            <div class="card-body mt-4">
                <form action="#" method="POST" enctype="multipart/form-data">
                    @csrf
                    <!-- PUT method simulation -->
                    <input type="hidden" name="_method" value="PUT">
                    
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="barcode">Barcode / Kode Produk</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-barcode"></i></span>
                                <input type="text" class="form-control" id="barcode" name="barcode" value="BRG-001" readonly />
                            </div>
                            <div class="form-text">Kode produk tidak dapat diubah setelah dibuat.</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="nama_barang">Nama Barang <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang" value="Indomie Goreng Spesial" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="kategori_id">Kategori <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <select class="form-select" id="kategori_id" name="kategori_id" required>
                                <option value="1" selected>Sembako</option>
                                <option value="2">Minuman</option>
                                <option value="3">Snack</option>
                                <option value="4">Sabun & Sampo</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="satuan_id">Satuan <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <select class="form-select" id="satuan_id" name="satuan_id" required>
                                <option value="1">Pcs</option>
                                <option value="2" selected>Bungkus</option>
                                <option value="3">Botol</option>
                                <option value="4">Dus</option>
                                <option value="5">Kg</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3">Harga & Stok</h6>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="harga_beli">Harga Beli <span class="text-danger">*</span></label>
                        <div class="col-sm-4">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="harga_beli" name="harga_beli" value="2500" min="0" required />
                            </div>
                        </div>
                        <label class="col-sm-2 col-form-label" for="harga_jual">Harga Jual <span class="text-danger">*</span></label>
                        <div class="col-sm-4">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="harga_jual" name="harga_jual" value="3000" min="0" required />
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="stok_awal">Stok Saat Ini</label>
                        <div class="col-sm-4">
                            <input type="number" class="form-control" id="stok_awal" name="stok_awal" value="150" readonly />
                            <div class="form-text">Gunakan menu Penyesuaian Stok untuk mengubah jumlah fisik.</div>
                        </div>
                        <label class="col-sm-2 col-form-label" for="min_stok">Minimum Stok</label>
                        <div class="col-sm-4">
                            <input type="number" class="form-control" id="min_stok" name="min_stok" value="10" min="0" />
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3">Lainnya</h6>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="foto">Foto Barang</label>
                        <div class="col-sm-10">
                            <div class="d-flex align-items-start align-items-sm-center gap-4 mb-3">
                                <div class="avatar avatar-xl bg-label-secondary">
                                    <span class="avatar-initial rounded"><i class="bx bx-package fs-2"></i></span>
                                </div>
                                <div class="button-wrapper">
                                    <label for="foto" class="btn btn-outline-primary me-2 mb-2" tabindex="0">
                                        <span class="d-none d-sm-block">Upload foto baru</span>
                                        <i class="bx bx-upload d-block d-sm-none"></i>
                                        <input type="file" id="foto" class="account-file-input" hidden accept="image/png, image/jpeg, image/jpg" />
                                    </label>
                                    <button type="button" class="btn btn-outline-danger account-image-reset mb-2">
                                        <i class="bx bx-reset d-block d-sm-none"></i>
                                        <span class="d-none d-sm-block">Hapus</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="status">Status Produk</label>
                        <div class="col-sm-10">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="status" name="status" checked />
                                <label class="form-check-label" for="status">Aktif (Tersedia untuk dijual)</label>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-end mt-4">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
