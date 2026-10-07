@extends('layouts.app')

@section('title', 'Penyesuaian Stok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Stok /</span> Penyesuaian Stok (Opname)
    </h4>
    <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <h5 class="card-header border-bottom">Form Penyesuaian Stok Manual</h5>
            <div class="card-body mt-4">
                <form action="#" method="POST">
                    @csrf
                    
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="tanggal">Tanggal</label>
                        <div class="col-sm-4">
                            <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="product_id">Pilih Produk <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            <select class="form-select" id="product_id" name="product_id" required onchange="updateCurrentStock(this)">
                                <option value="" selected disabled>Cari / Pilih Produk</option>
                                <option value="1" data-stock="150" data-unit="Bungkus">BRG-001 - Indomie Goreng</option>
                                <option value="2" data-stock="85" data-unit="Botol">BRG-002 - Aqua Botol 600ml</option>
                                <option value="3" data-stock="5" data-unit="Pcs">BRG-003 - Beras Sania 5kg</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-4 bg-lighter rounded p-3 mx-0">
                        <div class="col-md-4 mb-2 mb-md-0 text-center border-end">
                            <small class="text-muted text-uppercase">Stok Sistem Saat Ini</small>
                            <h4 class="mb-0 mt-1 text-primary fw-bold" id="stok_sistem">-</h4>
                        </div>
                        <div class="col-md-8 d-flex align-items-center">
                            <p class="mb-0 text-muted">
                                <i class="bx bx-info-circle me-1"></i> Jika jumlah fisik (aktual) berbeda dengan sistem, catat penyesuaian di bawah ini beserta alasannya.
                            </p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="tipe_penyesuaian">Tipe Penyesuaian <span class="text-danger">*</span></label>
                        <div class="col-sm-4">
                            <select class="form-select" id="tipe_penyesuaian" name="tipe_penyesuaian" required>
                                <option value="pengurangan" class="text-danger">(-) Pengurangan (Barang Rusak/Hilang)</option>
                                <option value="penambahan" class="text-success">(+) Penambahan (Kelebihan Fisik)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="jumlah">Jumlah Penyesuaian <span class="text-danger">*</span></label>
                        <div class="col-sm-3">
                            <div class="input-group">
                                <input type="number" class="form-control" id="jumlah" name="jumlah" placeholder="0" min="1" required />
                                <span class="input-group-text bg-label-secondary" id="unit-label">Unit</span>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="keterangan">Keterangan / Alasan <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <textarea id="keterangan" name="keterangan" class="form-control" rows="3" placeholder="Contoh: Kemasan rusak dimakan tikus" required></textarea>
                        </div>
                    </div>

                    <div class="row justify-content-end mt-4">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bx bx-check-circle me-1"></i> Simpan Penyesuaian
                            </button>
                            <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    function updateCurrentStock(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const stock = selectedOption.getAttribute('data-stock');
        const unit = selectedOption.getAttribute('data-unit');
        
        if(stock) {
            document.getElementById('stok_sistem').innerText = stock + ' ' + unit;
            document.getElementById('unit-label').innerText = unit;
        } else {
            document.getElementById('stok_sistem').innerText = '-';
            document.getElementById('unit-label').innerText = 'Unit';
        }
    }
</script>
@endsection
