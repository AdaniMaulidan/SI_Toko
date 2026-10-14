@extends('layouts.app')

@section('title', 'Penyesuaian Stok / Stock Opname')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Stok /</span> Penyesuaian Stok (Stock Opname)
    </h4>
    <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Kembali ke Stok Saat Ini
    </a>
</div>

{{-- Flash Alert Messages --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <strong><i class="bx bx-error-circle me-1"></i> Terjadi kesalahan input:</strong>
    <ul class="mb-0 mt-1">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Form Penyesuaian Stok (FASE 5.3) -->
<form action="{{ route('stock.adjustment.store') }}" method="POST" id="form-stock-adjustment">
    @csrf

    <div class="row">
        <!-- 1. Informasi Opname & Alasan -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom py-3 d-flex align-items-center gap-2">
                    <i class="bx bx-spreadsheet text-primary fs-4"></i>
                    <h5 class="card-title mb-0">Informasi Penyesuaian & Alasan</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="row g-3">
                        <!-- Kode Opname -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" for="code">
                                Kode Referensi Opname <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bx bx-hash"></i></span>
                                <input type="text" 
                                       id="code" 
                                       name="code" 
                                       class="form-control font-monospace fw-bold @error('code') is-invalid @enderror" 
                                       value="{{ old('code', $defaultCode) }}" 
                                       required>
                            </div>
                        </div>

                        <!-- Tanggal Penyesuaian -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" for="date">
                                Tanggal Penyesuaian <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   id="date" 
                                   name="date" 
                                   class="form-control @error('date') is-invalid @enderror" 
                                   value="{{ old('date', date('Y-m-d')) }}" 
                                   required>
                        </div>

                        <!-- Tipe Penyesuaian -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" for="type">
                                Jenis Penyesuaian <span class="text-danger">*</span>
                            </label>
                            <select id="type" name="type" class="form-select" required>
                                <option value="opname" {{ old('type', 'opname') === 'opname' ? 'selected' : '' }}>Opname Fisik (Pencocokan)</option>
                                <option value="out" {{ old('type') === 'out' ? 'selected' : '' }}>Pengurangan (Barang Rusak / Hilang / Kadaluarsa)</option>
                                <option value="in" {{ old('type') === 'in' ? 'selected' : '' }}>Penambahan (Kelebihan Fisik / Koreksi Plus)</option>
                            </select>
                        </div>

                        <!-- Kategori Alasan -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" for="reason_category">
                                Alasan Utama <span class="text-danger">*</span>
                            </label>
                            <select id="reason_category" name="reason_category" class="form-select" required onchange="handleReasonChange(this)">
                                <option value="Koreksi Rutin / Stock Opname Berkala">Koreksi Rutin / Opname Berkala</option>
                                <option value="Barang Rusak (Kemasan Robek / Bocor / Cacat)">Barang Rusak / Cacat</option>
                                <option value="Barang Hilang / Selisih Fisik Kurang">Barang Hilang</option>
                                <option value="Barang Kadaluarsa (Expired)">Barang Kadaluarsa (Expired)</option>
                                <option value="Salah Hitung / Kesalahan Input Sebelumnya">Salah Hitung / Koreksi Input</option>
                                <option value="Kelebihan Fisik / Bonus Supplier">Kelebihan Fisik / Bonus</option>
                                <option value="Lainnya">Alasan Lainnya</option>
                            </select>
                        </div>

                        <!-- Catatan / Keterangan Tambahan -->
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="notes">Keterangan / Kronologi Tambahan (Opsional)</label>
                            <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="Tuliskan keterangan detail kronologi jika ada barang rusak atau hilang...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Tabel Pencocokan Stok Fisik vs Sistem -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bx bx-check-square text-primary fs-4"></i>
                        <h5 class="card-title mb-0">Pencocokan Stok Fisik vs Stok Sistem</h5>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" onclick="addAdjustmentRow()">
                        <i class="bx bx-plus me-1"></i> Tambah Barang Opname
                    </button>
                </div>

                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0" id="adjustment-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th style="min-width: 300px;">Produk</th>
                                <th style="width: 100px;" class="text-center">Satuan</th>
                                <th style="width: 140px;" class="text-center">Stok Sistem</th>
                                <th style="width: 180px;" class="text-center">Stok Fisik Aktual</th>
                                <th style="width: 160px;" class="text-center">Selisih Stok</th>
                                <th style="width: 60px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="adjustment-items-body">
                            <!-- Injected dynamically via JavaScript -->
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold text-uppercase fs-6 align-middle py-3">
                                    TOTAL BARANG DIPERIKSA:
                                </td>
                                <td class="text-center fw-bold fs-6 align-middle py-3">
                                    <span id="total-system-stock-display" class="badge bg-label-secondary font-monospace">0 pcs</span>
                                </td>
                                <td class="text-center fw-bold fs-6 align-middle py-3">
                                    <span id="total-actual-stock-display" class="badge bg-label-primary font-monospace">0 pcs</span>
                                </td>
                                <td class="text-center fw-bold fs-6 align-middle py-3">
                                    <span id="total-diff-display" class="badge bg-secondary font-monospace">0 pcs</span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="card-footer bg-light border-top py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        <i class="bx bx-info-circle me-1"></i> Masukkan jumlah <strong>Stok Fisik Aktual</strong> yang Anda hitung langsung di rak/gudang. Selisih akan dikalkulasi otomatis.
                    </small>
                    <button type="button" class="btn btn-xs btn-outline-primary" onclick="addAdjustmentRow()">
                        <i class="bx bx-plus me-1"></i> Tambah Produk Lain
                    </button>
                </div>
            </div>
        </div>

        <!-- 3. Alert Otomatisasi & Tombol Simpan -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <span class="badge badge-center rounded-pill bg-info p-3 me-3">
                            <i class="bx bx-sync fs-4 text-white"></i>
                        </span>
                        <div>
                            <h6 class="alert-heading mb-1 fw-bold">Sinkronisasi Otomatis Master Data Produk:</h6>
                            <p class="mb-0 small">
                                Menekan tombol <strong>Simpan & Sinkronkan Stok</strong> akan memperbarui angka stok di master barang secara otomatis agar persis sama dengan hasil opname fisik Anda. Riwayat selisih akan disimpan permanen untuk rekam jejak audit gudang.
                            </p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                        <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm" id="btn-submit-adjustment">
                            <i class="bx bx-check-circle me-1"></i> Simpan & Sinkronkan Stok Master Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- 4. Riwayat 10 Penyesuaian Terakhir -->
<div class="card border-0 shadow-sm mt-2">
    <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 d-flex align-items-center gap-2">
            <i class="bx bx-history text-primary fs-4"></i> Riwayat Penyesuaian Stok Terakhir
        </h5>
        <span class="badge bg-label-primary">{{ count($recentAdjustments) }} data terakhir</span>
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode Opname</th>
                    <th>Tanggal</th>
                    <th>Petugas</th>
                    <th>Tipe & Alasan</th>
                    <th>Rincian Produk Disinkronkan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentAdjustments as $adj)
                <tr>
                    <td>
                        <span class="fw-bold font-monospace text-primary">{{ $adj->code }}</span>
                    </td>
                    <td>
                        <span class="fw-semibold text-dark">{{ $adj->date->format('d M Y') }}</span>
                    </td>
                    <td>
                        <span class="small text-muted">{{ $adj->user?->name ?? 'Staff Gudang' }}</span>
                    </td>
                    <td>
                        @if($adj->type === 'out')
                            <span class="badge bg-label-danger mb-1 d-inline-block">Pengurangan</span>
                        @elseif($adj->type === 'in')
                            <span class="badge bg-label-success mb-1 d-inline-block">Penambahan</span>
                        @else
                            <span class="badge bg-label-primary mb-1 d-inline-block">Opname Fisik</span>
                        @endif
                        <div class="small text-dark">{{ $adj->notes ?: '-' }}</div>
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            @foreach($adj->items as $it)
                                <div class="small">
                                    <strong class="text-heading">{{ $it->product?->name ?? 'Barang' }}</strong>: 
                                    <span class="text-muted">Sistem: {{ $it->previous_stock }}</span> &rarr; 
                                    <span class="fw-bold text-dark">Fisik: {{ $it->actual_stock }}</span>
                                    @if($it->difference > 0)
                                        <span class="badge bg-label-success ms-1">+{{ $it->difference }}</span>
                                    @elseif($it->difference < 0)
                                        <span class="badge bg-label-danger ms-1">{{ $it->difference }}</span>
                                    @else
                                        <span class="badge bg-label-secondary ms-1">0</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="bx bx-check-circle fs-2 text-secondary mb-1"></i>
                        <p class="mb-0 small">Belum ada riwayat penyesuaian stok yang tersimpan.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('page-script')
<script>
    const productCatalog = @json($products);
    const preselectedId = @json($preselectedProductId);

    let rowIndexCounter = 0;

    // Handle Kategori Alasan Mengubah Tipe Penyesuaian
    function handleReasonChange(selectEl) {
        const val = selectEl.value;
        const typeSelect = document.getElementById('type');

        if (val.includes('Rusak') || val.includes('Hilang') || val.includes('Kadaluarsa')) {
            typeSelect.value = 'out';
        } else if (val.includes('Kelebihan')) {
            typeSelect.value = 'in';
        } else {
            typeSelect.value = 'opname';
        }
    }

    // Tambah Baris Penyesuaian Baru
    function addAdjustmentRow(defaultProductId = null) {
        const tbody = document.getElementById('adjustment-items-body');
        const idx = rowIndexCounter++;

        const tr = document.createElement('tr');
        tr.id = `row-adj-${idx}`;

        // Options Produk
        let optionsHtml = '<option value="" disabled selected>-- Pilih Produk yang Diperiksa --</option>';
        productCatalog.forEach(p => {
            const isSelected = (defaultProductId && p.id == defaultProductId) ? 'selected' : '';
            optionsHtml += `
                <option value="${p.id}" 
                        data-stock="${p.stock}" 
                        data-unit="${p.unit_symbol}" 
                        ${isSelected}>
                    ${p.name} (${p.barcode}) - Stok Sistem: ${p.stock} ${p.unit_symbol}
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
                <span class="badge bg-label-secondary fs-6 font-monospace" id="system-stock-${idx}">0</span>
                <input type="hidden" id="system-stock-val-${idx}" value="0">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" 
                           name="items[${idx}][actual_stock]" 
                           id="actual-stock-${idx}" 
                           class="form-control text-center fw-bold fs-6 actual-stock-input" 
                           value="0" 
                           min="0" 
                           required 
                           oninput="calculateDiff(${idx})">
                </div>
            </td>
            <td class="text-center align-middle">
                <span class="badge bg-secondary font-monospace fs-6" id="diff-badge-${idx}">0</span>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-icon btn-outline-danger btn-sm" onclick="removeAdjustmentRow(${idx})" title="Hapus Baris">
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

        const systemStock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
        const unitSymbol = selectedOption.getAttribute('data-unit') || 'Pcs';

        document.getElementById(`unit-badge-${idx}`).innerText = unitSymbol;
        document.getElementById(`system-stock-${idx}`).innerText = `${systemStock} ${unitSymbol}`;
        document.getElementById(`system-stock-val-${idx}`).value = systemStock;

        const actualInput = document.getElementById(`actual-stock-${idx}`);
        // Jika belum diedit user, defaultkan ke stok sistem
        if (!actualInput.dataset.userEdited) {
            actualInput.value = systemStock;
        }

        calculateDiff(idx);
    }

    // Hitung Selisih Baris
    function calculateDiff(idx) {
        const actualInput = document.getElementById(`actual-stock-${idx}`);
        const systemStockVal = parseInt(document.getElementById(`system-stock-val-${idx}`).value) || 0;
        if (!actualInput) return;

        actualInput.dataset.userEdited = "true";

        const actualStock = Math.max(0, parseInt(actualInput.value) || 0);
        const diff = actualStock - systemStockVal;

        const diffBadge = document.getElementById(`diff-badge-${idx}`);
        if (diff === 0) {
            diffBadge.className = 'badge bg-label-secondary font-monospace fs-6';
            diffBadge.innerText = '0 (Cocok)';
        } else if (diff > 0) {
            diffBadge.className = 'badge bg-success font-monospace fs-6';
            diffBadge.innerText = `+${diff} (Kelebihan)`;
        } else {
            diffBadge.className = 'badge bg-danger font-monospace fs-6';
            diffBadge.innerText = `${diff} (Kurang)`;
        }

        calculateGrandTotal();
    }

    // Hapus Baris
    function removeAdjustmentRow(idx) {
        const tbody = document.getElementById('adjustment-items-body');
        if (tbody.children.length <= 1) {
            alert('Minimal harus ada 1 barang yang disesuaikan!');
            return;
        }

        const row = document.getElementById(`row-adj-${idx}`);
        if (row) {
            row.remove();
            updateRowNumbers();
            calculateGrandTotal();
        }
    }

    // Perbarui Nomor Urut Baris
    function updateRowNumbers() {
        const tbody = document.getElementById('adjustment-items-body');
        Array.from(tbody.children).forEach((tr, index) => {
            const numEl = tr.querySelector('.row-number');
            if (numEl) numEl.innerText = index + 1;
        });
    }

    // Hitung Total Seluruh Opname
    function calculateGrandTotal() {
        let totalSystem = 0;
        let totalActual = 0;

        const tbody = document.getElementById('adjustment-items-body');
        Array.from(tbody.children).forEach(tr => {
            const systemInput = tr.querySelector('input[id^="system-stock-val-"]');
            const actualInput = tr.querySelector('.actual-stock-input');

            if (systemInput && actualInput) {
                totalSystem += (parseInt(systemInput.value) || 0);
                totalActual += (parseInt(actualInput.value) || 0);
            }
        });

        const totalDiff = totalActual - totalSystem;

        document.getElementById('total-system-stock-display').innerText = `${totalSystem} pcs`;
        document.getElementById('total-actual-stock-display').innerText = `${totalActual} pcs`;

        const diffDisplay = document.getElementById('total-diff-display');
        if (totalDiff === 0) {
            diffDisplay.className = 'badge bg-secondary font-monospace';
            diffDisplay.innerText = '0 pcs';
        } else if (totalDiff > 0) {
            diffDisplay.className = 'badge bg-success font-monospace';
            diffDisplay.innerText = `+${totalDiff} pcs`;
        } else {
            diffDisplay.className = 'badge bg-danger font-monospace';
            diffDisplay.innerText = `${totalDiff} pcs`;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Jika ada preselected product ID dari URL query param
        if (preselectedId) {
            addAdjustmentRow(preselectedId);
        } else {
            addAdjustmentRow();
        }

        // Prevent double submit
        const form = document.getElementById('form-stock-adjustment');
        form.addEventListener('submit', function () {
            const btnSubmit = document.getElementById('btn-submit-adjustment');
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyinkronkan Stok...`;
        });
    });
</script>
@endsection
