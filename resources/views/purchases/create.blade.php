@extends('layouts.app')

@section('title', 'Catat Pembelian Baru')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Pembelian /</span> Tambah Pembelian
    </h4>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
    </a>
</div>

<form action="#" method="POST">
    @csrf
    <div class="row">
        <!-- Informasi Umum Pembelian -->
        <div class="col-12 mb-4">
            <div class="card">
                <h5 class="card-header border-bottom">Informasi Pembelian (Barang Masuk)</h5>
                <div class="card-body mt-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="no_po">No. Referensi / PO <span class="text-danger">*</span></label>
                            <input type="text" id="no_po" name="no_po" class="form-control" value="PO-{{ date('Ym') }}-{{ rand(100, 999) }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="tanggal">Tanggal Pembelian <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="supplier_id">Supplier <span class="text-danger">*</span></label>
                            <select id="supplier_id" name="supplier_id" class="form-select" required>
                                <option value="" selected disabled>Pilih Supplier</option>
                                <option value="1">PT. Indofood Sukses Makmur</option>
                                <option value="2">PT. Tirta Investama (Aqua)</option>
                                <option value="3">Grosir Sembako Jaya</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Barang Masuk -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Detail Barang</h5>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addPurchaseRow()">
                        <i class="bx bx-plus me-1"></i> Tambah Baris
                    </button>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered mb-0" id="purchase-table">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th style="width: 150px;">Harga Beli (Rp)</th>
                                <th style="width: 120px;">Qty</th>
                                <th style="width: 180px;">Subtotal (Rp)</th>
                                <th style="width: 50px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="purchase-items-body">
                            <!-- Default Row -->
                            <tr>
                                <td>
                                    <select class="form-select product-select" required onchange="calculateRow(this)">
                                        <option value="" selected disabled>Pilih Produk</option>
                                        <option value="1" data-price="2500">Indomie Goreng</option>
                                        <option value="2" data-price="2800">Aqua Botol 600ml</option>
                                        <option value="3" data-price="68000">Beras Sania 5kg</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control item-price" value="0" min="0" onkeyup="calculateRow(this)" onchange="calculateRow(this)" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control item-qty" value="1" min="1" onkeyup="calculateRow(this)" onchange="calculateRow(this)" required>
                                </td>
                                <td>
                                    <input type="text" class="form-control item-subtotal text-end fw-bold" value="0" readonly>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-icon btn-outline-danger btn-sm" onclick="removeRow(this)">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold align-middle">TOTAL BIAYA:</td>
                                <td colspan="2">
                                    <input type="text" class="form-control form-control-lg text-end fw-bold text-primary" id="grand-total" value="0" readonly>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Status & Simpan -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="status_pembayaran">Status Pembayaran <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input name="status_pembayaran" class="form-check-input" type="radio" value="Lunas" id="status_lunas" checked>
                                    <label class="form-check-label" for="status_lunas"> Lunas (Tunai/Transfer) </label>
                                </div>
                                <div class="form-check">
                                    <input name="status_pembayaran" class="form-check-input" type="radio" value="Hutang" id="status_hutang">
                                    <label class="form-check-label" for="status_hutang"> Hutang / Tempo </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="catatan">Catatan Tambahan (Opsional)</label>
                            <textarea id="catatan" name="catatan" class="form-control" rows="2" placeholder="Catatan..."></textarea>
                        </div>
                    </div>
                    
                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <span class="alert-icon rounded-circle bg-info text-white me-3 p-2">
                            <i class="bx bx-info-circle"></i>
                        </span>
                        <div>
                            Tindakan ini akan <strong>otomatis menambah stok fisik barang</strong> setelah Anda menekan tombol "Simpan Pembelian".
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Simpan Pembelian & Tambah Stok
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('page-script')
<script>
    function calculateRow(element) {
        const row = element.closest('tr');
        
        // Auto-fill price if select changed
        if (element.classList.contains('product-select')) {
            const selectedOption = element.options[element.selectedIndex];
            if (selectedOption.value) {
                const defaultPrice = selectedOption.getAttribute('data-price');
                row.querySelector('.item-price').value = defaultPrice;
            }
        }
        
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        
        const subtotal = price * qty;
        
        // Format subtotal display
        row.querySelector('.item-subtotal').value = subtotal.toLocaleString('id-ID');
        
        calculateGrandTotal();
    }
    
    function calculateGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.item-subtotal').forEach(input => {
            // Remove non-numeric characters before parsing
            const val = parseFloat(input.value.replace(/[^0-9.-]+/g,"")) || 0;
            grandTotal += val;
        });
        
        document.getElementById('grand-total').value = grandTotal.toLocaleString('id-ID');
    }
    
    function addPurchaseRow() {
        const tbody = document.getElementById('purchase-items-body');
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td>
                <select class="form-select product-select" required onchange="calculateRow(this)">
                    <option value="" selected disabled>Pilih Produk</option>
                    <option value="1" data-price="2500">Indomie Goreng</option>
                    <option value="2" data-price="2800">Aqua Botol 600ml</option>
                    <option value="3" data-price="68000">Beras Sania 5kg</option>
                </select>
            </td>
            <td>
                <input type="number" class="form-control item-price" value="0" min="0" onkeyup="calculateRow(this)" onchange="calculateRow(this)" required>
            </td>
            <td>
                <input type="number" class="form-control item-qty" value="1" min="1" onkeyup="calculateRow(this)" onchange="calculateRow(this)" required>
            </td>
            <td>
                <input type="text" class="form-control item-subtotal text-end fw-bold" value="0" readonly>
            </td>
            <td>
                <button type="button" class="btn btn-icon btn-outline-danger btn-sm" onclick="removeRow(this)">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(tr);
    }
    
    function removeRow(btn) {
        const tbody = document.getElementById('purchase-items-body');
        if (tbody.children.length > 1) {
            btn.closest('tr').remove();
            calculateGrandTotal();
        } else {
            alert('Minimal harus ada satu baris barang!');
        }
    }
</script>
@endsection
