@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Produk /</span> Tambah Produk
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
                    
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="barcode">Barcode / Kode Produk</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-barcode"></i></span>
                                <input type="text" class="form-control" id="barcode" name="barcode" placeholder="Scan barcode atau ketik manual" autofocus />
                                <button class="btn btn-outline-primary" type="button"><i class="bx bx-scan"></i> Scan Kamera</button>
                            </div>
                            <div class="form-text">Biarkan kosong jika ingin sistem yang membuatkan (auto-generate).</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="nama_barang">Nama Barang <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang" placeholder="Contoh: Indomie Goreng Spesial" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="kategori_id">Kategori <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <select class="form-select" id="kategori_id" name="kategori_id" required>
                                <option value="" selected disabled>Pilih Kategori</option>
                                <option value="1">Sembako</option>
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
                                <option value="" selected disabled>Pilih Satuan</option>
                                <option value="1">Pcs</option>
                                <option value="2">Bungkus</option>
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
                                <input type="number" class="form-control" id="harga_beli" name="harga_beli" placeholder="0" min="0" required />
                            </div>
                        </div>
                        <label class="col-sm-2 col-form-label" for="harga_jual">Harga Jual <span class="text-danger">*</span></label>
                        <div class="col-sm-4">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="harga_jual" name="harga_jual" placeholder="0" min="0" required />
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="stok_awal">Stok Awal</label>
                        <div class="col-sm-4">
                            <input type="number" class="form-control" id="stok_awal" name="stok_awal" placeholder="0" min="0" />
                        </div>
                        <label class="col-sm-2 col-form-label" for="min_stok">Minimum Stok</label>
                        <div class="col-sm-4">
                            <input type="number" class="form-control" id="min_stok" name="min_stok" placeholder="5" min="0" value="5" />
                            <div class="form-text">Sistem akan memberi peringatan jika stok di bawah angka ini.</div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3">Lainnya</h6>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="foto">Foto Barang</label>
                        <div class="col-sm-10">
                            <input class="form-control" type="file" id="foto" name="foto" accept="image/png, image/jpeg, image/jpg" />
                            <div class="form-text">Format: JPG, PNG. Maksimal 2MB.</div>
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
                            <button type="submit" class="btn btn-primary me-2">Simpan Produk</button>
                            <button type="reset" class="btn btn-outline-secondary">Batal</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
