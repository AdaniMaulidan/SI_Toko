@extends('layouts.app')

@section('title', 'Catat Pembelian Produk (Kulakan)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Pembelian /</span> Catat Pembelian Baru
    </h4>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Kembali ke Riwayat
    </a>
</div>

{{-- Flash Alert Messages --}}
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong><i class="bx bx-error-circle me-1"></i> Terjadi kesalahan input:</strong>
    <ul class="mb-0 mt-1">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<form action="{{ route('purchases.store') }}" method="POST" id="form-purchase">
    @csrf

    <div class="row">
        <!-- 1. Informasi Utama Pembelian -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom py-3 d-flex align-items-center gap-2">
                    <i class="bx bx-file-blank text-primary fs-4"></i>
                    <h5 class="card-title mb-0">Informasi Faktur & Supplier</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="row g-3">
                        <!-- No. Faktur / PO -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" for="invoice_number">
                                No. Faktur / Referensi PO <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bx bx-hash"></i></span>
                                <input type="text" 
                                       id="invoice_number" 
                                       name="invoice_number" 
                                       class="form-control font-monospace fw-bold @error('invoice_number') is-invalid @enderror" 
                                       value="{{ old('invoice_number', $defaultInvoice) }}" 
                                       placeholder="PO-YYYYMMDD-XXXX" 
                                       required>
                            </div>
                            <small class="text-muted">Bisa menggunakan nomor faktur dari nota supplier.</small>
                        </div>

                        <!-- Tanggal Pembelian -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" for="purchase_date">
                                Tanggal Pembelian <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   id="purchase_date" 
                                   name="purchase_date" 
                                   class="form-control @error('purchase_date') is-invalid @enderror" 
                                   value="{{ old('purchase_date', date('Y-m-d')) }}" 
                                   required>
                        </div>

                        <!-- Supplier -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" for="supplier_id">Supplier</label>
                            <select id="supplier_id" name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                                <option value="">-- Pilih Supplier (Opsional) --</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                                        {{ $sup->name }} {{ $sup->phone ? '(' . $sup->phone . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih supplier penyedia barang.</small>
                        </div>

                        <!-- Status Pembayaran -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" for="payment_status">
                                Status Pembayaran <span class="text-danger">*</span>
                            </label>
                            <select id="payment_status" name="payment_status" class="form-select" onchange="handlePaymentStatusChange()" required>
                                <option value="paid" {{ old('payment_status', 'paid') === 'paid' ? 'selected' : '' }}>Lunas (Kas Keluar)</option>
                                <option value="unpaid" {{ old('payment_status') === 'unpaid' ? 'selected' : '' }}>Hutang / Tempo</option>
                                <option value="partial" {{ old('payment_status') === 'partial' ? 'selected' : '' }}>Sebagian / Uang Muka (DP)</option>
                            </select>
                        </div>

                        <!-- Input Nominal Bayar (DP / Sebagian) -->
                        <div class="col-md-6" id="partial-paid-container" style="display: none;">
                            <label class="form-label fw-semibold" for="paid_amount">
                                Jumlah Uang Dibayar Saat Ini (Rp) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" 
                                       id="paid_amount" 
                                       name="paid_amount" 
                                       class="form-control" 
                                       value="{{ old('paid_amount', 0) }}" 
                                       min="0" 
                                       step="500">
                            </div>
                            <small class="text-muted">Nominal kas yang langsung dibayarkan ke supplier saat transaksi.</small>
                        </div>

                        <!-- Catatan Tambahan -->
                        <div class="col-12" id="notes-container">
                            <label class="form-label fw-semibold" for="notes">Catatan Tambahan (Opsional)</label>
                            <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="Contoh: Barang diterima kurir, ada promo diskon faktur, jatuh tempo 14 hari, dll.">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Detail Barang Kulakan (Barang Masuk) -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bx bx-package text-primary fs-4"></i>
                        <h5 class="card-title mb-0">Rincian Barang Kulakan</h5>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" onclick="addPurchaseRow()">
                        <i class="bx bx-plus me-1"></i> Tambah Baris Barang
                    </button>
                </div>
                
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0" id="purchase-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th style="min-width: 280px;">Produk</th>
                                <th style="width: 100px;" class="text-center">Satuan</th>
                                <th style="width: 120px;" class="text-center">Stok Saat Ini</th>
                                <th style="width: 180px;">Harga Beli Baru (Rp)</th>
                                <th style="width: 130px;" class="text-center">Kuantitas</th>
                                <th style="width: 180px;" class="text-end">Subtotal (Rp)</th>
                                <th style="width: 60px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="purchase-items-body">
                            <!-- Injected dynamically via JavaScript -->
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end fw-bold text-uppercase fs-6 align-middle py-3">
                                    TOTAL KULAKAN (BARANG MASUK):
                                </td>
                                <td class="text-center fw-bolder fs-6 align-middle py-3">
                                    <span id="grand-total-qty" class="badge bg-label-primary fs-6 font-monospace">0 pcs</span>
                                </td>
                                <td class="text-end fw-bolder fs-5 text-primary align-middle py-3">
                                    <span id="grand-total-display">Rp 0</span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="card-footer bg-light border-top py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        <i class="bx bx-info-circle me-1"></i> Anda dapat mengubah harga modal produk saat kulakan. Harga modal produk di master data akan diperbarui secara otomatis.
                    </small>
                    <button type="button" class="btn btn-xs btn-outline-primary" onclick="addPurchaseRow()">
                        <i class="bx bx-plus me-1"></i> Tambah Produk Lain
                    </button>
                </div>
            </div>
        </div>

        <!-- 3. Alert Otomatisasi & Tombol Eksekusi -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="alert alert-primary d-flex align-items-center mb-4" role="alert">
                        <span class="badge badge-center rounded-pill bg-primary p-3 me-3">
                            <i class="bx bx-check-double fs-4 text-white"></i>
                        </span>
                        <div>
                            <h6 class="alert-heading mb-1 fw-bold">Otomatisasi Sistem Pembelian:</h6>
                            <p class="mb-0 small">
                                Saat Anda menekan tombol <strong>Simpan Pembelian</strong>, sistem akan secara atomik:
                                <br>1. <strong>Menambahkan kuantitas stok produk</strong> ke inventaris gudang/toko.
                                <br>2. <strong>Memperbarui harga modal (cost price)</strong> produk dengan harga beli baru yang diinput.
                                <br>3. <strong>Mencatat kas keluar</strong> otomatis pada buku kas (jika status pembayaran Lunas atau ada DP).
                            </p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm" id="btn-submit-purchase">
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
    // Data Master Produk dari Backend
    const productCatalog = @json($products);

    let rowIndexCounter = 0;

    const formatRp = (num) => 'Rp ' + Number(num).toLocaleString('id-ID');

    // Handle Perubahan Status Pembayaran
    function handlePaymentStatusChange() {
        const status = document.getElementById('payment_status').value;
        const partialContainer = document.getElementById('partial-paid-container');
        
        if (status === 'partial') {
            partialContainer.style.display = 'block';
        } else {
            partialContainer.style.display = 'none';
        }
    }

    // Tambah Baris Pembelian Baru
    function addPurchaseRow(defaultProductId = null, defaultPrice = 0, defaultQty = 1) {
        const tbody = document.getElementById('purchase-items-body');
        const idx = rowIndexCounter++;

        const tr = document.createElement('tr');
        tr.id = `row-item-${idx}`;

        // Generate Option Produk
        let optionsHtml = '<option value="" disabled selected>-- Cari / Pilih Produk --</option>';
        productCatalog.forEach(p => {
            const isSelected = (defaultProductId && p.id == defaultProductId) ? 'selected' : '';
            optionsHtml += `
                <option value="${p.id}" 
                        data-cost="${p.cost_price}" 
                        data-unit="${p.unit_symbol}" 
                        data-stock="${p.stock}" 
                        ${isSelected}>
                    ${p.name} (${p.barcode}) - Stok: ${p.stock} ${p.unit_symbol}
                </option>
            `;
        });

        tr.innerHTML = `
            <td class="text-center row-number fw-bold text-muted">${tbody.children.length + 1}</td>
            <td>
                <select name="items[${idx}][product_id]" class="form-select product-select" required onchange="onProductSelectChange(this, ${idx})">
                    ${optionsHtml}
                </select>
            </td>
            <td class="text-center align-middle">
                <span class="badge bg-label-secondary font-monospace" id="unit-badge-${idx}">-</span>
            </td>
            <td class="text-center align-middle">
                <span class="badge bg-label-info font-monospace" id="stock-badge-${idx}">-</span>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                    <input type="number" 
                           name="items[${idx}][cost_price]" 
                           id="cost-price-${idx}" 
                           class="form-control item-cost-price" 
                           value="${defaultPrice}" 
                           min="0" 
                           step="100" 
                           required 
                           oninput="calculateRow(${idx})">
                </div>
            </td>
            <td>
                <input type="number" 
                       name="items[${idx}][quantity]" 
                       id="quantity-${idx}" 
                       class="form-control form-control-sm text-center item-quantity fw-bold" 
                       value="${defaultQty}" 
                       min="1" 
                       required 
                       oninput="calculateRow(${idx})">
            </td>
            <td class="text-end fw-bold text-primary align-middle">
                <span id="subtotal-${idx}">Rp 0</span>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-icon btn-outline-danger btn-sm" onclick="removePurchaseRow(${idx})" title="Hapus Baris">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        // Jika default product diberikan, trigger perubahan
        if (defaultProductId) {
            const selectEl = tr.querySelector('.product-select');
            onProductSelectChange(selectEl, idx);
        }

        updateRowNumbers();
        calculateGrandTotal();
    }

    // Handler Saat Produk Dipilih
    function onProductSelectChange(selectEl, idx) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;

        const costPrice = parseFloat(selectedOption.getAttribute('data-cost')) || 0;
        const unitSymbol = selectedOption.getAttribute('data-unit') || 'Pcs';
        const currentStock = selectedOption.getAttribute('data-stock') || '0';

        document.getElementById(`unit-badge-${idx}`).innerText = unitSymbol;
        document.getElementById(`stock-badge-${idx}`).innerText = `${currentStock} ${unitSymbol}`;

        const priceInput = document.getElementById(`cost-price-${idx}`);
        // Jika harga belum diisi atau masih 0, isi dengan harga modal default produk
        if (parseFloat(priceInput.value) <= 0 || !priceInput.dataset.userEdited) {
            priceInput.value = costPrice;
        }

        calculateRow(idx);
    }

    // Hitung Subtotal Baris
    function calculateRow(idx) {
        const costPriceInput = document.getElementById(`cost-price-${idx}`);
        const qtyInput = document.getElementById(`quantity-${idx}`);
        if (!costPriceInput || !qtyInput) return;

        costPriceInput.dataset.userEdited = "true";

        const price = Math.max(0, parseFloat(costPriceInput.value) || 0);
        const qty = Math.max(0, parseInt(qtyInput.value) || 0);

        const subtotal = price * qty;
        document.getElementById(`subtotal-${idx}`).innerText = formatRp(subtotal);

        calculateGrandTotal();
    }

    // Hapus Baris
    function removePurchaseRow(idx) {
        const tbody = document.getElementById('purchase-items-body');
        if (tbody.children.length <= 1) {
            alert('Minimal harus ada 1 barang dalam transaksi pembelian!');
            return;
        }

        const row = document.getElementById(`row-item-${idx}`);
        if (row) {
            row.remove();
            updateRowNumbers();
            calculateGrandTotal();
        }
    }

    // Perbarui Nomor Urut Baris
    function updateRowNumbers() {
        const tbody = document.getElementById('purchase-items-body');
        Array.from(tbody.children).forEach((tr, index) => {
            const numEl = tr.querySelector('.row-number');
            if (numEl) numEl.innerText = index + 1;
        });
    }

    // Hitung Total Seluruh Pembelian
    function calculateGrandTotal() {
        let grandTotal = 0;
        let totalQty = 0;

        const tbody = document.getElementById('purchase-items-body');
        Array.from(tbody.children).forEach(tr => {
            const priceInput = tr.querySelector('.item-cost-price');
            const qtyInput = tr.querySelector('.item-quantity');

            if (priceInput && qtyInput) {
                const price = Math.max(0, parseFloat(priceInput.value) || 0);
                const qty = Math.max(0, parseInt(qtyInput.value) || 0);
                grandTotal += (price * qty);
                totalQty += qty;
            }
        });

        document.getElementById('grand-total-display').innerText = formatRp(grandTotal);
        document.getElementById('grand-total-qty').innerText = `${totalQty} pcs`;
    }

    // Inisialisasi: Tambahkan 1 baris default saat halaman pertama kali dibuka
    document.addEventListener('DOMContentLoaded', function () {
        handlePaymentStatusChange();

        // Tambah baris awal jika kosong
        const tbody = document.getElementById('purchase-items-body');
        if (tbody.children.length === 0) {
            addPurchaseRow();
        }

        // Prevent double submit
        const form = document.getElementById('form-purchase');
        form.addEventListener('submit', function (e) {
            const btnSubmit = document.getElementById('btn-submit-purchase');
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan Pembelian...`;
        });
    });
</script>
@endsection
