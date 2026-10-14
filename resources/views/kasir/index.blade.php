@extends('layouts.pos')

@section('title', 'Kasir (POS)')

@section('content')
<div class="pos-wrapper d-flex gap-3 h-100">
    
    <!-- Left: Product Catalog & Search -->
    <div class="pos-left d-flex flex-column h-100 flex-grow-1 overflow-hidden">
        
        <!-- Search & Scan Bar -->
        <div class="card mb-3 border-0 shadow-sm flex-shrink-0">
            <div class="card-body p-3">
                <div class="row gx-2 align-items-center">
                    <div class="col-md-8 col-sm-12">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-barcode-reader fs-4 text-primary"></i></span>
                            <input type="text" 
                                   id="barcode-search-input" 
                                   class="form-control form-control-lg font-monospace" 
                                   placeholder="Scan barcode atau ketik nama produk... (Tekan F2)" 
                                   autofocus 
                                   autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="btn-clear-search" title="Bersihkan Pencarian">
                                <i class="bx bx-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 mt-2 mt-md-0 d-flex gap-2">
                        <button class="btn btn-lg btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2" id="btn-focus-scan">
                            <i class="bx bx-scan fs-4"></i>
                            <span>Scan Cepat (F2)</span>
                        </button>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                    <small class="text-muted">
                        <i class="bx bx-info-circle me-1"></i> Scan barcode scanner langsung otomatis masuk ke keranjang.
                    </small>
                    <span id="product-count-badge" class="badge bg-label-primary small">{{ count($products) }} produk tersedia</span>
                </div>
            </div>
        </div>

        <!-- Category Pills Filter -->
        <div class="mb-3 d-flex gap-2 overflow-auto py-1 flex-shrink-0 category-pills-bar" style="white-space: nowrap;">
            <button class="btn btn-primary rounded-pill px-3 category-pill active" data-category="all">
                <i class="bx bx-grid-alt me-1"></i> Semua Kategori
            </button>
            @foreach($categories as $cat)
                <button class="btn btn-outline-primary rounded-pill px-3 category-pill" data-category="{{ $cat->id }}">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>

        <!-- Product Grid Catalog -->
        <div class="row g-3 overflow-auto flex-grow-1 align-content-start pb-3" id="product-grid-container">
            @forelse($products as $prod)
            <div class="col-6 col-md-4 col-lg-3 product-item-col" 
                 data-id="{{ $prod['id'] }}" 
                 data-barcode="{{ $prod['barcode'] }}" 
                 data-name="{{ strtolower($prod['name']) }}" 
                 data-category="{{ $prod['category_id'] }}"
                 data-stock="{{ $prod['stock'] }}">
                <div class="card h-100 product-card border shadow-none position-relative {{ $prod['stock'] <= 0 ? 'out-of-stock' : '' }}" 
                     onclick="handleProductClick({{ $prod['id'] }})">
                    
                    {{-- Badge Stok --}}
                    <div class="position-absolute top-0 end-0 m-2" id="stock-badge-box-{{ $prod['id'] }}">
                        @if($prod['stock'] <= 0)
                            <span class="badge bg-danger">Habis</span>
                        @elseif($prod['stock'] <= $prod['min_stock'])
                            <span class="badge bg-warning text-dark">Sisa {{ $prod['stock'] }}</span>
                        @else
                            <span class="badge bg-success">Stok {{ $prod['stock'] }}</span>
                        @endif
                    </div>

                    <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                        <div>
                            @if($prod['image_url'])
                                <img src="{{ $prod['image_url'] }}" alt="{{ $prod['name'] }}" class="rounded mb-2 object-fit-cover w-100" style="height: 100px;">
                            @else
                                <div class="avatar avatar-xl bg-label-secondary mx-auto mb-2 rounded d-flex align-items-center justify-content-center" style="width: 100%; height: 100px;">
                                    <i class="bx bx-package fs-1 text-secondary"></i>
                                </div>
                            @endif
                            <h6 class="mb-1 text-truncate fw-bold text-heading" title="{{ $prod['name'] }}">{{ $prod['name'] }}</h6>
                            <div class="text-muted small font-monospace mb-1">{{ $prod['barcode'] }}</div>
                        </div>

                        <div>
                            <div class="text-primary fw-bold fs-6 mb-0">Rp {{ number_format($prod['selling_price'], 0, ',', '.') }}</div>
                            <small class="text-muted">/ {{ $prod['unit_name'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <div class="text-muted">
                    <i class="bx bx-package fs-1 mb-2"></i>
                    <h6>Belum ada produk aktif di katalog toko.</h6>
                    <a href="{{ route('products.create') }}" class="btn btn-sm btn-primary">Tambah Produk</a>
                </div>
            </div>
            @endforelse
            
            <!-- Not Found State in Search -->
            <div class="col-12 text-center py-5 d-none" id="no-search-results">
                <div class="text-muted">
                    <i class="bx bx-search-alt fs-1 mb-2 text-secondary"></i>
                    <h6 class="fw-bold">Produk tidak ditemukan</h6>
                    <p class="small">Tidak ada barang yang cocok dengan kata kunci atau barcode tersebut.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Cart & Calculation -->
    <div class="pos-right card h-100 d-flex flex-column shadow-sm border-0" style="width: 440px; min-width: 420px;">
        
        <!-- Cart Header -->
        <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center bg-white">
            <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="bx bx-cart text-primary fs-4"></i> Keranjang Belanja
            </h5>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-label-primary rounded-pill px-3 py-1" id="cart-item-count">0 Item</span>
                <button type="button" class="btn btn-sm btn-icon btn-outline-secondary" id="btn-reprint-last" onclick="printLastReceipt()" title="Cetak Ulang Struk Terakhir" style="display: none;">
                    <i class="bx bx-printer"></i>
                </button>
                <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="clearCart()" title="Kosongkan Keranjang">
                    <i class="bx bx-trash"></i>
                </button>
            </div>
        </div>

        <!-- Customer Selector -->
        <div class="p-3 border-bottom bg-white">
            <label class="form-label small text-muted mb-1 fw-semibold">Pelanggan Transaksi:</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bx bx-user text-primary"></i></span>
                <select id="pos-customer-select" class="form-select">
                    <option value="" selected>Pelanggan Umum (Non-Member)</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" data-debt="{{ $c->total_debt }}" data-phone="{{ $c->phone }}">
                            {{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }} {{ $c->total_debt > 0 ? '[Hutang: Rp '.number_format($c->total_debt, 0, ',', '.').']' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Cart Items List -->
        <div class="card-body p-0 pos-cart-items bg-white flex-grow-1 overflow-auto" id="cart-items-container">
            <!-- Empty state placeholder -->
            <div class="text-center p-5 text-muted h-100 d-flex flex-column justify-content-center align-items-center" id="empty-cart-msg">
                <div class="avatar avatar-xl bg-label-secondary mb-3 rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bx bx-cart-add fs-2 text-primary"></i>
                </div>
                <h6 class="fw-bold mb-1">Keranjang Masih Kosong</h6>
                <p class="small text-muted mb-0">Scan barcode atau klik produk pada katalog di sebelah kiri untuk menambahkan belanjaan.</p>
            </div>
            
            <!-- Injected dynamic Cart Items -->
            <div id="cart-items-list" class="d-none"></div>
        </div>

        <!-- Cart Footer / Calculations -->
        <div class="border-top bg-white p-3 shadow-lg">
            
            <!-- Subtotal -->
            <div class="d-flex justify-content-between mb-2 text-muted small">
                <span>Subtotal Barang:</span>
                <span class="fw-semibold text-body" id="subtotal-val">Rp 0</span>
            </div>

            <!-- Diskon Item Total -->
            <div class="d-flex justify-content-between mb-2 text-muted small" id="discount-items-row" style="display: none !important;">
                <span>Diskon Produk:</span>
                <span class="text-danger fw-semibold" id="discount-items-val">- Rp 0</span>
            </div>

            <!-- Diskon Global / Transaksi -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center gap-1">
                    <span class="small text-muted">Diskon Tambahan:</span>
                    <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1" data-bs-toggle="modal" data-bs-target="#globalDiscountModal" title="Atur Diskon Total">
                        <i class="bx bx-edit-alt me-1"></i><span id="btn-discount-label">Set Diskon</span>
                    </button>
                </div>
                <span class="text-danger fw-semibold small" id="global-discount-val">- Rp 0</span>
            </div>

            <!-- Total Akhir -->
            <div class="d-flex justify-content-between my-3 align-items-center p-2 rounded bg-label-primary">
                <div>
                    <span class="d-block small text-primary fw-semibold">TOTAL PEMBAYARAN</span>
                    <h3 class="mb-0 text-primary fw-bold" id="total-val">Rp 0</h3>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary fs-6 px-3 py-1" id="total-qty-badge">0 Item</span>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="row gx-2">
                <div class="col-4">
                    <button class="btn btn-outline-danger w-100 py-2 d-flex align-items-center justify-content-center gap-1" onclick="clearCart()">
                        <i class="bx bx-trash"></i>
                        <span>Batal</span>
                    </button>
                </div>
                <div class="col-8">
                    <button class="btn btn-success w-100 py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm" 
                            id="btn-pay" 
                            disabled 
                            data-bs-toggle="modal" 
                            data-bs-target="#paymentModal">
                        <i class="bx bx-wallet fs-4"></i>
                        <span class="fs-6 fw-bold">Bayar (F9)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Diskon Item -->
<div class="modal fade" id="itemDiscountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-bottom py-2">
                <h6 class="modal-title fw-bold">Diskon Produk</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <p class="small text-muted mb-2 text-truncate" id="discount-modal-item-name">-</p>
                <input type="hidden" id="discount-modal-item-id">
                
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Tipe Diskon</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="item_disc_type" id="disc_type_rp" value="rp" checked onchange="toggleItemDiscType()">
                        <label class="btn btn-outline-primary btn-sm" for="disc_type_rp">Nominal (Rp)</label>
                        
                        <input type="radio" class="btn-check" name="item_disc_type" id="disc_type_pct" value="pct" onchange="toggleItemDiscType()">
                        <label class="btn btn-outline-primary btn-sm" for="disc_type_pct">Persen (%)</label>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold" id="item-disc-input-label">Nilai Diskon (Rp)</label>
                    <input type="number" class="form-control" id="item-discount-input" min="0" value="0">
                </div>
            </div>
            <div class="modal-footer border-top py-2 d-flex justify-content-end gap-1">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="applyItemDiscount()">Terapkan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Diskon Global / Total Transaksi -->
<div class="modal fade" id="globalDiscountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-bottom py-2">
                <h6 class="modal-title fw-bold">Diskon Total Transaksi</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Tipe Diskon</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="global_disc_type" id="global_disc_type_rp" value="rp" checked onchange="toggleGlobalDiscType()">
                        <label class="btn btn-outline-primary btn-sm" for="global_disc_type_rp">Nominal (Rp)</label>
                        
                        <input type="radio" class="btn-check" name="global_disc_type" id="global_disc_type_pct" value="pct" onchange="toggleGlobalDiscType()">
                        <label class="btn btn-outline-primary btn-sm" for="global_disc_type_pct">Persen (%)</label>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold" id="global-disc-input-label">Nilai Diskon (Rp)</label>
                    <input type="number" class="form-control" id="global-discount-input" min="0" value="0">
                </div>
            </div>
            <div class="modal-footer border-top py-2 d-flex justify-content-end gap-1">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="applyGlobalDiscount()">Simpan Diskon</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Proses Pembayaran (Checkout Modal - FASE 4.2) -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
            <!-- Modal Header -->
            <div class="modal-header bg-lighter border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-sm bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bx bx-receipt fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-heading mb-0">Proses Pembayaran (Checkout)</h5>
                        <small class="text-muted" id="modal-invoice-preview">Invoice Transaksi Kasir</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <!-- Info Pelanggan & Total Tagihan Banner -->
                <div class="card bg-label-primary border-0 mb-3 shadow-none">
                    <div class="card-body p-3">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                            <div>
                                <span class="badge bg-primary px-3 py-1 mb-1 font-monospace" id="checkout-customer-name">
                                    <i class="bx bx-user me-1"></i> Pelanggan Umum
                                </span>
                                <div class="text-muted small" id="checkout-cart-summary">0 Jenis Barang (0 Qty)</div>
                            </div>
                            <div class="text-sm-end">
                                <span class="d-block small text-primary fw-semibold text-uppercase">Total Tagihan</span>
                                <h2 class="text-primary fw-bolder mb-0" id="checkout-total-bill">Rp 0</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pilihan Metode Pembayaran (Tunai, QRIS, Transfer Bank, Hutang) -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-0">Metode Pembayaran</label>
                        <span class="small text-danger fw-semibold d-none" id="debt-disabled-hint">
                            <i class="bx bx-lock-alt me-1"></i>Hutang hanya untuk pelanggan terdaftar
                        </span>
                    </div>

                    <div class="row g-2">
                        <!-- Tunai -->
                        <div class="col-6 col-md-3">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_method_cash" value="cash" checked onchange="switchPaymentMethod('cash')">
                            <label class="btn btn-outline-primary w-100 p-2 d-flex flex-column align-items-center justify-content-center h-100 payment-method-card" for="pay_method_cash">
                                <i class="bx bx-money fs-2 mb-1"></i>
                                <span class="fw-bold">Tunai</span>
                                <small class="text-muted" style="font-size: 0.7rem;">Cash Langsung</small>
                            </label>
                        </div>

                        <!-- QRIS -->
                        <div class="col-6 col-md-3">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_method_qris" value="qris" onchange="switchPaymentMethod('qris')">
                            <label class="btn btn-outline-primary w-100 p-2 d-flex flex-column align-items-center justify-content-center h-100 payment-method-card" for="pay_method_qris">
                                <i class="bx bx-qr-scan fs-2 mb-1"></i>
                                <span class="fw-bold">QRIS</span>
                                <small class="text-muted" style="font-size: 0.7rem;">Scan E-Wallet</small>
                            </label>
                        </div>

                        <!-- Transfer Bank -->
                        <div class="col-6 col-md-3">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_method_transfer" value="transfer" onchange="switchPaymentMethod('transfer')">
                            <label class="btn btn-outline-primary w-100 p-2 d-flex flex-column align-items-center justify-content-center h-100 payment-method-card" for="pay_method_transfer">
                                <i class="bx bx-credit-card fs-2 mb-1"></i>
                                <span class="fw-bold">Transfer</span>
                                <small class="text-muted" style="font-size: 0.7rem;">Bank Transfer</small>
                            </label>
                        </div>

                        <!-- Hutang (Bon) -->
                        <div class="col-6 col-md-3">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_method_debt" value="debt" onchange="switchPaymentMethod('debt')">
                            <label class="btn btn-outline-warning w-100 p-2 d-flex flex-column align-items-center justify-content-center h-100 payment-method-card" for="pay_method_debt" id="pay_method_debt_label">
                                <i class="bx bx-calendar-event fs-2 mb-1"></i>
                                <span class="fw-bold">Hutang (Bon)</span>
                                <small class="text-muted" style="font-size: 0.7rem;">Khusus Member</small>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 1. Section TUNAI (Nominal Cepat & Kembalian) -->
                <div id="payment-cash-section">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-1">Uang Tunai Diterima (Rp)</label>
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-white fw-bold text-primary px-3 fs-5">Rp</span>
                            <input type="text" 
                                   class="form-control form-control-lg fw-bolder text-end fs-3" 
                                   id="cash-received-input" 
                                   placeholder="0" 
                                   autocomplete="off" 
                                   oninput="onCashReceivedChange(this)">
                            <button class="btn btn-outline-secondary px-3" type="button" onclick="clearCashInput()" title="Bersihkan input nominal">
                                <i class="bx bx-x fs-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Nominal Cepat Uang Pas & Pecahan -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted fw-semibold">Nominal Cepat:</span>
                            <span class="small text-primary fw-medium" id="quick-action-hint">Pilih pecahan uang pas / bulat</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1" id="quick-cash-container">
                            <button type="button" class="btn btn-sm btn-primary fw-bold px-3" onclick="setCashExact()">
                                <i class="bx bx-check me-1"></i>Uang Pas
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCashAmount(10000)">Rp 10.000</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCashAmount(20000)">Rp 20.000</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCashAmount(50000)">Rp 50.000</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCashAmount(100000)">Rp 100.000</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCashAmount(200000)">Rp 200.000</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCashAmount(500000)">Rp 500.000</button>
                        </div>
                        <!-- Dynamic Smart Roundups -->
                        <div class="d-flex flex-wrap gap-1 mt-2 align-items-center" id="dynamic-cash-container"></div>
                    </div>

                    <!-- Kalkulasi Kembalian Box -->
                    <div id="change-card" class="card border mb-3 transition-all" style="background-color: #f8f9fa;">
                        <div class="card-body p-3 text-center">
                            <div class="text-uppercase small fw-bold text-muted mb-1" id="change-card-title">
                                <i class="bx bx-info-circle me-1"></i> Kembalian
                            </div>
                            <h2 class="mb-0 fw-bolder text-muted" id="change-card-val">Rp 0</h2>
                            <div class="small mt-1 text-muted" id="change-card-desc">Silakan masukkan uang pembayaran yang diterima dari pembeli.</div>
                        </div>
                    </div>
                </div>

                <!-- 2. Section QRIS & TRANSFER BANK -->
                <div id="payment-noncash-section" class="d-none">
                    <div class="card bg-label-info border-info mb-3">
                        <div class="card-body p-4 text-center">
                            <div class="avatar avatar-lg bg-info text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm">
                                <i class="bx bx-qr-scan fs-1" id="noncash-icon"></i>
                            </div>
                            <h5 class="fw-bold mb-1" id="noncash-title">Pembayaran QRIS</h5>
                            <p class="text-muted small mb-2" id="noncash-instruction">
                                Tampilkan QRIS toko kepada pelanggan. Pastikan dana telah terverifikasi masuk ke rekening sebelum menyelesaikan transaksi.
                            </p>
                            <div class="p-2 rounded bg-white border d-inline-block px-4 my-2">
                                <small class="text-muted d-block">Nominal Harus Dibayar (Uang Pas):</small>
                                <span class="fs-4 fw-bolder text-info" id="noncash-amount-display">Rp 0</span>
                            </div>
                            <div class="small text-success fw-semibold mt-2">
                                <i class="bx bx-check-circle me-1"></i> Kembalian: Rp 0 (Otomatis Pas)
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Section HUTANG (BON PELANGGAN) -->
                <div id="payment-debt-section" class="d-none">
                    <div class="card bg-label-warning border-warning mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-warning text-white rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bx bx-calendar-event fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">Data Pencatatan Hutang / Piutang</h6>
                                        <small class="text-muted">Transaksi ini akan dicatat ke buku hutang pelanggan.</small>
                                    </div>
                                </div>
                                <span class="badge bg-warning text-dark font-monospace" id="debt-status-badge">Terverifikasi</span>
                            </div>

                            <div class="row g-2 mb-3 bg-white p-2 rounded border">
                                <div class="col-6">
                                    <small class="text-muted d-block">Nama Pelanggan:</small>
                                    <strong class="text-dark" id="debt-customer-name-display">-</strong>
                                    <div class="small text-muted" id="debt-customer-phone-display">-</div>
                                </div>
                                <div class="col-6 text-end">
                                    <small class="text-muted d-block">Saldo Hutang Saat Ini:</small>
                                    <strong class="text-danger fs-6" id="debt-customer-current-balance">Rp 0</strong>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Jatuh Tempo Pembayaran <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="debt-due-date" min="{{ date('Y-m-d') }}">
                                    <small class="text-muted" style="font-size: 0.72rem;">Default: 14 hari dari hari ini</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Uang Muka / DP (Opsional)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control text-end fw-bold" id="debt-dp-input" placeholder="0" oninput="onDebtDpChange(this)">
                                    </div>
                                    <small class="text-muted" style="font-size: 0.72rem;">Isi jika pelanggan membayar uang muka</small>
                                </div>
                            </div>

                            <div class="p-2 rounded bg-white border mt-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-muted">Sisa Hutang Baru Transaksi Ini:</span>
                                    <strong class="text-danger fs-6" id="debt-new-amount">Rp 0</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center small text-muted border-top pt-1">
                                    <span>Estimasi Total Akumulasi Hutang:</span>
                                    <span class="fw-bold text-dark" id="debt-total-accumulated">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catatan Tambahan Transaksi -->
                <div class="mb-1">
                    <label class="form-label small text-muted fw-semibold mb-1">Catatan Transaksi (Opsional):</label>
                    <input type="text" class="form-control form-control-sm" id="checkout-notes" placeholder="Catatan internal kasir (contoh: pesanan diantar, bungkus rapi, dll.)">
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top py-2 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-arrow-back me-1"></i> Batal (Esc)
                </button>
                <button type="button" class="btn btn-success px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm" id="btn-submit-payment" onclick="processCheckout()" disabled>
                    <i class="bx bx-check-circle fs-4"></i>
                    <span>Selesaikan Transaksi (Enter)</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Transaksi Berhasil & Cetak Struk (FASE 4.4) -->
<div class="modal fade" id="receiptSuccessModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 text-center p-3">
            <div class="modal-body p-4">
                <!-- Checkmark Icon -->
                <div class="avatar avatar-xl bg-label-success rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
                    <i class="bx bx-check fs-1 text-success"></i>
                </div>

                <h4 class="fw-bolder text-heading mb-1">Transaksi Berhasil!</h4>
                <p class="text-muted small mb-3">Penjualan telah berhasil disimpan ke database.</p>

                <!-- Transaction Details Card -->
                <div class="bg-lighter p-3 rounded-3 mb-3 text-start small border">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">No. Faktur:</span>
                        <strong class="font-monospace text-primary" id="success-invoice-no">-</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Total Belanja:</span>
                        <strong class="text-dark" id="success-total-amount">Rp 0</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Metode Pembayaran:</span>
                        <span class="badge bg-label-primary font-monospace" id="success-payment-method">-</span>
                    </div>
                    <div class="d-flex justify-content-between pt-1 border-top" id="success-change-row">
                        <span class="text-muted fw-bold" id="success-change-label">Kembalian:</span>
                        <strong class="text-success fs-6" id="success-change-amount">Rp 0</strong>
                    </div>
                </div>

                <!-- Print Action Buttons (58mm, 80mm, PDF) -->
                <div class="d-grid gap-2 mb-2">
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-1 shadow-sm" onclick="printReceipt('58')">
                                <i class="bx bx-printer fs-5"></i>
                                <span>Cetak 58mm</span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-primary w-100 py-2 d-flex align-items-center justify-content-center gap-1" onclick="printReceipt('80')">
                                <i class="bx bx-printer fs-5"></i>
                                <span>Cetak 80mm</span>
                            </button>
                        </div>
                    </div>

                    <a href="#" id="success-download-pdf-btn" class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-1" target="_blank">
                        <i class="bx bxs-file-pdf fs-5 text-danger"></i>
                        <span>Download Struk PDF</span>
                    </a>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-secondary px-4 py-2 fw-semibold" data-bs-dismiss="modal" onclick="finishSaleAndReset()" id="btn-next-transaction">
                    <i class="bx bx-plus-circle me-1"></i> Transaksi Baru (Esc / Enter)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div id="pos-toast" class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2" id="pos-toast-msg">
                <i class="bx bx-check-circle fs-5"></i>
                <span>Notifikasi</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

@endsection

@section('page-style')
<style>
    .pos-wrapper { height: calc(100vh - 80px); }
    .product-card { 
        transition: all 0.2s ease-in-out; 
        cursor: pointer;
        background-color: #fff;
    }
    .product-card:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 0.5rem 1rem rgba(105, 108, 255, 0.15) !important; 
        border-color: var(--bs-primary) !important; 
    }
    .product-card.out-of-stock {
        opacity: 0.6;
        cursor: not-allowed;
        background-color: #fafafa;
    }
    .product-card.out-of-stock:hover {
        transform: none;
        box-shadow: none !important;
        border-color: #d9dee3 !important;
    }
    .cart-item { 
        transition: background-color 0.15s ease;
    }
    .cart-item:hover { 
        background-color: #f8f9fa; 
    }
    .btn-xs {
        padding: 0.15rem 0.4rem;
        font-size: 0.75rem;
    }
    .payment-method-card {
        border-width: 2px !important;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        min-height: 82px;
        cursor: pointer;
    }
    .btn-check:checked + .payment-method-card {
        background-color: var(--bs-primary) !important;
        color: #fff !important;
        border-color: var(--bs-primary) !important;
        box-shadow: 0 0.25rem 0.75rem rgba(105, 108, 255, 0.3) !important;
    }
    .btn-check:checked + .payment-method-card i,
    .btn-check:checked + .payment-method-card small,
    .btn-check:checked + .payment-method-card span {
        color: #fff !important;
    }
    .btn-check:checked + #pay_method_debt_label {
        background-color: #ffab00 !important;
        color: #fff !important;
        border-color: #ffab00 !important;
        box-shadow: 0 0.25rem 0.75rem rgba(255, 171, 0, 0.3) !important;
    }
    .btn-check:checked + #pay_method_debt_label i,
    .btn-check:checked + #pay_method_debt_label small,
    .btn-check:checked + #pay_method_debt_label span {
        color: #fff !important;
    }
    .payment-method-card.disabled {
        pointer-events: none;
        opacity: 0.45;
        background-color: #f5f5f5 !important;
        border-color: #d9dee3 !important;
        cursor: not-allowed;
    }
    .transition-all {
        transition: all 0.25s ease;
    }
</style>
@endsection

@section('page-script')
<script>
    // Data produk dari database (disiapkan untuk fast in-memory search & scanning)
    const allProducts = @json($products);

    // State Keranjang Belanja
    let cart = [];
    let globalDiscount = { type: 'rp', value: 0 }; // 'rp' atau 'pct'

    // State Pembayaran & Checkout (FASE 4.2)
    let currentSubtotal = 0;
    let currentDiscount = 0;
    let currentTotal = 0;
    let currentTotalQty = 0;
    let selectedPaymentMethod = 'cash';
    let currentCashReceived = 0;
    let currentDebtDp = 0;
    
    // Audio Feedback (Web Audio API Synthesizer - tidak perlu file mp3 eksternal)
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    function playBeep(freq = 880, duration = 0.08, type = 'sine') {
        try {
            if (audioCtx.state === 'suspended') audioCtx.resume();
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = type;
            osc.frequency.value = freq;
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
            osc.start();
            osc.stop(audioCtx.currentTime + duration);
        } catch (e) {
            console.log('Audio disabled', e);
        }
    }

    function showToast(message, type = 'primary') {
        const toastEl = document.getElementById('pos-toast');
        const toastMsg = document.getElementById('pos-toast-msg');
        
        toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
        
        let icon = 'bx-check-circle';
        if (type === 'danger' || type === 'warning') icon = 'bx-error-circle';
        
        toastMsg.innerHTML = `<i class="bx ${icon} fs-5"></i> <span>${message}</span>`;
        
        const toast = new bootstrap.Toast(toastEl, { delay: 2500 });
        toast.show();
    }

    const formatRp = (num) => 'Rp ' + Number(num).toLocaleString('id-ID');

    // 1. Tambah Produk ke Keranjang (Dengan Validasi Stok Lengkap)
    function addToCart(product, qtyToAdd = 1) {
        // Validasi 1: Stok Habis (0)
        if (product.stock <= 0) {
            playBeep(300, 0.15, 'sawtooth');
            showToast(`Stok "${product.name}" habis (0)! Tidak dapat dijual.`, 'danger');
            return;
        }

        const existing = cart.find(item => item.id === product.id);

        if (existing) {
            // Validasi 2: Cek Batas Stok Tersedia
            if (existing.qty + qtyToAdd > product.stock) {
                playBeep(300, 0.15, 'sawtooth');
                showToast(`Stok tidak mencukupi! "${product.name}" hanya tersisa ${product.stock} ${product.unit_symbol}.`, 'warning');
                return;
            }
            existing.qty += qtyToAdd;
        } else {
            if (qtyToAdd > product.stock) {
                playBeep(300, 0.15, 'sawtooth');
                showToast(`Stok tidak mencukupi! "${product.name}" hanya tersisa ${product.stock} ${product.unit_symbol}.`, 'warning');
                return;
            }
            cart.push({
                id: product.id,
                barcode: product.barcode,
                name: product.name,
                price: product.selling_price,
                stock: product.stock,
                unit_name: product.unit_name,
                unit_symbol: product.unit_symbol,
                qty: qtyToAdd,
                discount: { type: 'rp', value: 0 },
            });
        }

        playBeep(880, 0.08, 'sine');
        renderCart();
    }

    // Klik dari Product Card Katalog
    function handleProductClick(productId) {
        const product = allProducts.find(p => p.id === productId);
        if (product) {
            addToCart(product, 1);
        }
    }

    // 2. Ubah Kuantitas (Qty) Item
    function updateQty(id, change) {
        const item = cart.find(i => i.id === id);
        if (!item) return;

        const newQty = item.qty + change;
        if (newQty <= 0) {
            removeFromCart(id);
            return;
        }

        if (newQty > item.stock) {
            playBeep(300, 0.15, 'sawtooth');
            showToast(`Maksimal Qty tercapai! Stok "${item.name}" hanya ${item.stock} ${item.unit_symbol}.`, 'warning');
            return;
        }

        item.qty = newQty;
        renderCart();
    }

    // Ubah Kuantitas langsung dari Input Number
    function setQtyDirect(id, value) {
        const item = cart.find(i => i.id === id);
        if (!item) return;

        let num = parseInt(value) || 0;
        if (num <= 0) {
            removeFromCart(id);
            return;
        }

        if (num > item.stock) {
            playBeep(300, 0.15, 'sawtooth');
            showToast(`Qty dikembalikan ke batas stok (${item.stock} ${item.unit_symbol}).`, 'warning');
            num = item.stock;
        }

        item.qty = num;
        renderCart();
    }

    // Hapus Produk dari Keranjang
    function removeFromCart(id) {
        cart = cart.filter(i => i.id !== id);
        renderCart();
    }

    // Kosongkan Seluruh Keranjang
    function clearCart() {
        if (cart.length === 0) return;
        cart = [];
        globalDiscount = { type: 'rp', value: 0 };
        renderCart();
        showToast('Keranjang telah dikosongkan.', 'secondary');
    }

    // 3. Render Tampilan Keranjang Belanja & Kalkulasi Total
    function renderCart() {
        const emptyMsg = document.getElementById('empty-cart-msg');
        const cartList = document.getElementById('cart-items-list');
        const btnPay = document.getElementById('btn-pay');
        const cartCountBadge = document.getElementById('cart-item-count');

        if (cart.length === 0) {
            emptyMsg.classList.remove('d-none');
            cartList.classList.add('d-none');
            cartList.innerHTML = '';
            btnPay.disabled = true;
            cartCountBadge.innerText = '0 Item';
            
            currentSubtotal = 0;
            currentDiscount = 0;
            currentTotal = 0;
            currentTotalQty = 0;

            document.getElementById('subtotal-val').innerText = 'Rp 0';
            document.getElementById('discount-items-row').style.setProperty('display', 'none', 'important');
            document.getElementById('global-discount-val').innerText = '- Rp 0';
            document.getElementById('total-val').innerText = 'Rp 0';
            document.getElementById('total-qty-badge').innerText = '0 Item';
            return;
        }

        emptyMsg.classList.add('d-none');
        cartList.classList.remove('d-none');
        cartList.innerHTML = '';

        let subtotalGross = 0;
        let totalItemDiscount = 0;
        let totalItemQty = 0;

        cart.forEach(item => {
            const itemGross = item.price * item.qty;
            subtotalGross += itemGross;
            totalItemQty += item.qty;

            // Hitung diskon per item
            let discAmount = 0;
            if (item.discount.type === 'pct') {
                discAmount = Math.round((itemGross * item.discount.value) / 100);
            } else {
                discAmount = Math.min(itemGross, item.discount.value);
            }
            totalItemDiscount += discAmount;
            const itemNet = itemGross - discAmount;

            const div = document.createElement('div');
            div.className = 'cart-item border-bottom p-3';
            div.innerHTML = `
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="flex-grow-1 pe-2">
                        <span class="fw-bold text-heading d-block">${item.name}</span>
                        <div class="d-flex align-items-center gap-2 small text-muted">
                            <span>${formatRp(item.price)}</span>
                            <span>&bull;</span>
                            <span class="text-secondary font-monospace">${item.barcode}</span>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold fs-6 text-primary">${formatRp(itemNet)}</span>
                        ${discAmount > 0 ? `<div class="small text-danger text-decoration-line-through">${formatRp(itemGross)}</div>` : ''}
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <!-- Diskon Button per Item -->
                    <div>
                        <button type="button" class="btn btn-xs ${discAmount > 0 ? 'btn-danger' : 'btn-outline-secondary'} py-0 px-2" 
                                onclick="openItemDiscountModal(${item.id})">
                            <i class="bx bx-tag-alt me-1"></i>
                            ${discAmount > 0 ? `Disc -${formatRp(discAmount)}` : '+ Diskon'}
                        </button>
                    </div>

                    <!-- Qty Controls with stock max validation -->
                    <div class="d-flex align-items-center gap-1">
                        <div class="input-group input-group-sm w-auto">
                            <button class="btn btn-outline-secondary px-2" type="button" onclick="updateQty(${item.id}, -1)">
                                <i class="bx bx-minus"></i>
                            </button>
                            <input type="number" 
                                   class="form-control text-center px-1 fw-bold" 
                                   style="width: 50px;" 
                                   value="${item.qty}" 
                                   min="1" 
                                   max="${item.stock}" 
                                   onchange="setQtyDirect(${item.id}, this.value)">
                            <button class="btn btn-outline-secondary px-2" type="button" onclick="updateQty(${item.id}, 1)" ${item.qty >= item.stock ? 'disabled' : ''}>
                                <i class="bx bx-plus"></i>
                            </button>
                        </div>
                        <button class="btn btn-sm btn-icon btn-outline-danger ms-1" type="button" onclick="removeFromCart(${item.id})" title="Hapus">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            cartList.appendChild(div);
        });

        // Hitung Diskon Global / Transaksi
        const subtotalAfterItemDisc = subtotalGross - totalItemDiscount;
        let globalDiscAmount = 0;
        if (globalDiscount.type === 'pct') {
            globalDiscAmount = Math.round((subtotalAfterItemDisc * globalDiscount.value) / 100);
        } else {
            globalDiscAmount = Math.min(subtotalAfterItemDisc, globalDiscount.value);
        }

        const finalTotal = Math.max(0, subtotalAfterItemDisc - globalDiscAmount);

        // Update State Global Transaksi POS
        currentSubtotal = subtotalGross;
        currentDiscount = totalItemDiscount + globalDiscAmount;
        currentTotal = finalTotal;
        currentTotalQty = totalItemQty;

        // Update DOM Angka
        cartCountBadge.innerText = `${cart.length} Item (${totalItemQty} Qty)`;
        document.getElementById('subtotal-val').innerText = formatRp(subtotalGross);

        const itemDiscRow = document.getElementById('discount-items-row');
        if (totalItemDiscount > 0) {
            itemDiscRow.style.setProperty('display', 'flex', 'important');
            document.getElementById('discount-items-val').innerText = `- ${formatRp(totalItemDiscount)}`;
        } else {
            itemDiscRow.style.setProperty('display', 'none', 'important');
        }

        document.getElementById('global-discount-val').innerText = globalDiscAmount > 0 ? `- ${formatRp(globalDiscAmount)}` : '- Rp 0';
        document.getElementById('total-val').innerText = formatRp(finalTotal);
        document.getElementById('total-qty-badge').innerText = `${totalItemQty} Qty`;
        
        const modalTotalBill = document.getElementById('checkout-total-bill');
        if (modalTotalBill) modalTotalBill.innerText = formatRp(finalTotal);

        btnPay.disabled = false;
    }

    // 4. Modal Diskon per Item Logic
    function openItemDiscountModal(itemId) {
        const item = cart.find(i => i.id === itemId);
        if (!item) return;

        document.getElementById('discount-modal-item-id').value = item.id;
        document.getElementById('discount-modal-item-name').innerText = `${item.name} (${item.qty} ${item.unit_symbol})`;
        
        if (item.discount.type === 'pct') {
            document.getElementById('disc_type_pct').checked = true;
        } else {
            document.getElementById('disc_type_rp').checked = true;
        }
        toggleItemDiscType();
        document.getElementById('item-discount-input').value = item.discount.value || 0;

        new bootstrap.Modal(document.getElementById('itemDiscountModal')).show();
    }

    function toggleItemDiscType() {
        const isPct = document.getElementById('disc_type_pct').checked;
        const label = document.getElementById('item-disc-input-label');
        label.innerText = isPct ? 'Nilai Diskon (%)' : 'Nilai Diskon (Rp)';
    }

    function applyItemDiscount() {
        const itemId = parseInt(document.getElementById('discount-modal-item-id').value);
        const item = cart.find(i => i.id === itemId);
        if (!item) return;

        const isPct = document.getElementById('disc_type_pct').checked;
        let val = parseFloat(document.getElementById('item-discount-input').value) || 0;
        if (val < 0) val = 0;
        if (isPct && val > 100) val = 100;

        item.discount = {
            type: isPct ? 'pct' : 'rp',
            value: val
        };

        bootstrap.Modal.getInstance(document.getElementById('itemDiscountModal')).hide();
        renderCart();
        showToast(`Diskon untuk "${item.name}" diterapkan.`, 'success');
    }

    // 5. Modal Diskon Total Transaksi Logic
    function toggleGlobalDiscType() {
        const isPct = document.getElementById('global_disc_type_pct').checked;
        const label = document.getElementById('global-disc-input-label');
        label.innerText = isPct ? 'Nilai Diskon (%)' : 'Nilai Diskon (Rp)';
    }

    function applyGlobalDiscount() {
        const isPct = document.getElementById('global_disc_type_pct').checked;
        let val = parseFloat(document.getElementById('global-discount-input').value) || 0;
        if (val < 0) val = 0;
        if (isPct && val > 100) val = 100;

        globalDiscount = {
            type: isPct ? 'pct' : 'rp',
            value: val
        };

        const btnLabel = document.getElementById('btn-discount-label');
        btnLabel.innerText = val > 0 ? (isPct ? `${val}%` : formatRp(val)) : 'Set Diskon';

        bootstrap.Modal.getInstance(document.getElementById('globalDiscountModal')).hide();
        renderCart();
        showToast('Diskon total transaksi diterapkan.', 'success');
    }

    // 6. Barcode Scanner & Search Handler
    const searchInput = document.getElementById('barcode-search-input');
    const productGrid = document.getElementById('product-grid-container');
    const noResults = document.getElementById('no-search-results');

    // Filter katalog grid saat kasir mengetik (Live search)
    searchInput.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        filterProductGrid();
    });

    // Enter Key Handler (Barcode Scanner Input)
    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const barcode = this.value.trim();
            if (!barcode) return;

            // 1. Cari exact barcode di memori lokal
            let match = allProducts.find(p => p.barcode.toLowerCase() === barcode.toLowerCase());
            
            // 2. Jika tidak ada exact barcode, coba cari jika hanya ada 1 hasil filter nama
            if (!match) {
                const candidates = allProducts.filter(p => 
                    p.name.toLowerCase().includes(barcode.toLowerCase()) || 
                    p.barcode.toLowerCase().includes(barcode.toLowerCase())
                );
                if (candidates.length === 1) {
                    match = candidates[0];
                }
            }

            if (match) {
                addToCart(match, 1);
                this.value = '';
                filterProductGrid();
            } else {
                // Fallback: Query ke server backend via AJAX
                fetch(`{{ route('kasir.search') }}?barcode=${encodeURIComponent(barcode)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.product) {
                            addToCart(data.product, 1);
                            searchInput.value = '';
                            filterProductGrid();
                        } else {
                            playBeep(300, 0.15, 'sawtooth');
                            showToast(`Barcode "${barcode}" tidak ditemukan!`, 'danger');
                        }
                    })
                    .catch(() => {
                        playBeep(300, 0.15, 'sawtooth');
                        showToast(`Barcode "${barcode}" tidak ditemukan!`, 'danger');
                    });
            }
        }
    });

    // Tombol Bersihkan Pencarian
    document.getElementById('btn-clear-search').addEventListener('click', function () {
        searchInput.value = '';
        searchInput.focus();
        filterProductGrid();
    });

    // Tombol Fokus Scan
    document.getElementById('btn-focus-scan').addEventListener('click', function () {
        searchInput.focus();
        searchInput.select();
    });

    // 7. Filter Kategori Pills
    let selectedCategory = 'all';
    document.querySelectorAll('.category-pill').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.category-pill').forEach(b => {
                b.classList.remove('btn-primary', 'active');
                b.classList.add('btn-outline-primary');
            });
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary', 'active');

            selectedCategory = this.getAttribute('data-category');
            filterProductGrid();
        });
    });

    function filterProductGrid() {
        const query = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.product-item-col').forEach(col => {
            const name = col.getAttribute('data-name');
            const barcode = col.getAttribute('data-barcode').toLowerCase();
            const category = col.getAttribute('data-category');

            const matchQuery = !query || name.includes(query) || barcode.includes(query);
            const matchCategory = (selectedCategory === 'all') || (category === selectedCategory);

            if (matchQuery && matchCategory) {
                col.classList.remove('d-none');
                visibleCount++;
            } else {
                col.classList.add('d-none');
            }
        });

        if (visibleCount === 0) {
            noResults.classList.remove('d-none');
        } else {
            noResults.classList.add('d-none');
        }
    }

    // 8. Global Keyboard Shortcuts (F2 untuk Scan, F9 untuk Bayar, Esc untuk Batal)
    window.addEventListener('keydown', function (e) {
        // F2: Fokus kolom input barcode
        if (e.key === 'F2') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }

        // F9: Trigger Bayar jika ada item
        if (e.key === 'F9') {
            e.preventDefault();
            const btnPay = document.getElementById('btn-pay');
            if (btnPay && !btnPay.disabled) {
                btnPay.click();
            }
        }
    });

    // ─────────────────────────────────────────────────────────────
    // 9. LOGIKA CHECKOUT MODAL & KALKULASI PEMBAYARAN (FASE 4.2)
    // ─────────────────────────────────────────────────────────────

    const paymentModalEl = document.getElementById('paymentModal');
    if (paymentModalEl) {
        paymentModalEl.addEventListener('show.bs.modal', function () {
            initPaymentModal();
        });

        paymentModalEl.addEventListener('shown.bs.modal', function () {
            if (selectedPaymentMethod === 'cash') {
                const cashInput = document.getElementById('cash-received-input');
                if (cashInput) {
                    cashInput.focus();
                    cashInput.select();
                }
            }
        });
    }

    // Inisialisasi data modal pembayaran saat dibuka
    function initPaymentModal() {
        const customerSelect = document.getElementById('pos-customer-select');
        const selectedOpt = customerSelect.options[customerSelect.selectedIndex];
        const customerId = customerSelect.value;
        const customerText = selectedOpt ? selectedOpt.text.trim() : 'Pelanggan Umum (Non-Member)';
        const customerDebt = selectedOpt ? (parseFloat(selectedOpt.getAttribute('data-debt')) || 0) : 0;
        const customerPhone = selectedOpt ? (selectedOpt.getAttribute('data-phone') || '') : '';

        // Update Info Header & Ringkasan Belanja
        const custBadge = document.getElementById('checkout-customer-name');
        const summaryBadge = document.getElementById('checkout-cart-summary');
        const totalBillDisplay = document.getElementById('checkout-total-bill');

        totalBillDisplay.innerText = formatRp(currentTotal);
        summaryBadge.innerText = `${cart.length} Jenis Barang (${currentTotalQty} Qty)`;

        const debtRadio = document.getElementById('pay_method_debt');
        const debtLabel = document.getElementById('pay_method_debt_label');
        const debtNotice = document.getElementById('debt-disabled-hint');

        if (!customerId) {
            // Pelanggan Umum: Nonaktifkan metode Hutang
            custBadge.className = 'badge bg-secondary px-3 py-1 mb-1 font-monospace';
            custBadge.innerHTML = '<i class="bx bx-user me-1"></i> Pelanggan Umum (Non-Member)';

            debtRadio.disabled = true;
            debtLabel.classList.add('disabled');
            debtNotice.classList.remove('d-none');

            // Jika sebelumnya metode hutang terpilih, paksa kembali ke tunai
            if (selectedPaymentMethod === 'debt') {
                document.getElementById('pay_method_cash').checked = true;
                selectedPaymentMethod = 'cash';
            }
        } else {
            // Pelanggan Terdaftar: Aktifkan metode Hutang
            custBadge.className = 'badge bg-primary px-3 py-1 mb-1 font-monospace';
            custBadge.innerHTML = `<i class="bx bx-user-check me-1"></i> ${customerText.split('[Hutang')[0].trim()}`;

            debtRadio.disabled = false;
            debtLabel.classList.remove('disabled');
            debtNotice.classList.add('d-none');

            document.getElementById('debt-customer-name-display').innerText = customerText.split('[Hutang')[0].trim();
            document.getElementById('debt-customer-phone-display').innerText = customerPhone ? `Telp: ${customerPhone}` : 'No Telp: -';
            document.getElementById('debt-customer-current-balance').innerText = formatRp(customerDebt);
        }

        // Set default jatuh tempo hutang (+14 hari)
        const dueDateInput = document.getElementById('debt-due-date');
        if (dueDateInput) {
            const d = new Date();
            d.setDate(d.getDate() + 14);
            dueDateInput.value = d.toISOString().split('T')[0];
        }

        // Tampilkan section sesuai metode saat ini
        switchPaymentMethod(selectedPaymentMethod);

        // Buat tombol nominal pintar di atas total
        generateDynamicCashButtons();

        // Default set Uang Pas saat pertama kali dibuka jika metode tunai
        if (selectedPaymentMethod === 'cash') {
            setCashExact();
        }
    }

    // Ganti Pilihan Metode Pembayaran
    function switchPaymentMethod(method) {
        selectedPaymentMethod = method;

        const cashSec = document.getElementById('payment-cash-section');
        const noncashSec = document.getElementById('payment-noncash-section');
        const debtSec = document.getElementById('payment-debt-section');
        const btnSubmit = document.getElementById('btn-submit-payment');

        cashSec.classList.add('d-none');
        noncashSec.classList.add('d-none');
        debtSec.classList.add('d-none');

        if (method === 'cash') {
            cashSec.classList.remove('d-none');
            recalculateCash();
            setTimeout(() => {
                const input = document.getElementById('cash-received-input');
                if (input) {
                    input.focus();
                    input.select();
                }
            }, 100);
        } else if (method === 'qris' || method === 'transfer') {
            noncashSec.classList.remove('d-none');
            const icon = document.getElementById('noncash-icon');
            const title = document.getElementById('noncash-title');
            const desc = document.getElementById('noncash-instruction');
            const amountDisp = document.getElementById('noncash-amount-display');

            if (method === 'qris') {
                icon.className = 'bx bx-qr-scan fs-1';
                title.innerText = 'Pembayaran QRIS';
                desc.innerText = 'Tampilkan QRIS toko kepada pelanggan. Pastikan dana telah terverifikasi masuk ke rekening sebelum menyelesaikan transaksi.';
            } else {
                icon.className = 'bx bx-credit-card fs-1';
                title.innerText = 'Pembayaran Transfer Bank';
                desc.innerText = 'Berikan nomor rekening toko kepada pelanggan. Cek mutasi saldo untuk memastikan dana sudah masuk sesuai nominal.';
            }

            amountDisp.innerText = formatRp(currentTotal);
            btnSubmit.disabled = false;
        } else if (method === 'debt') {
            debtSec.classList.remove('d-none');
            recalculateDebt();
            btnSubmit.disabled = false;
        }
    }

    // Input Uang Tunai Diterima
    function onCashReceivedChange(el) {
        const raw = el.value.replace(/\D/g, '');
        const val = parseInt(raw) || 0;
        currentCashReceived = val;
        el.value = val > 0 ? val.toLocaleString('id-ID') : '';
        recalculateCash();
    }

    // Tombol Uang Pas
    function setCashExact() {
        currentCashReceived = currentTotal;
        const input = document.getElementById('cash-received-input');
        if (input) input.value = currentTotal > 0 ? currentTotal.toLocaleString('id-ID') : '';
        playBeep(920, 0.05, 'sine');
        recalculateCash();
    }

    // Tombol Nominal Cepat Tertentu
    function setCashAmount(amount) {
        currentCashReceived = amount;
        const input = document.getElementById('cash-received-input');
        if (input) input.value = amount.toLocaleString('id-ID');
        playBeep(920, 0.05, 'sine');
        recalculateCash();
    }

    // Bersihkan Input Uang Tunai
    function clearCashInput() {
        currentCashReceived = 0;
        const input = document.getElementById('cash-received-input');
        if (input) {
            input.value = '';
            input.focus();
        }
        recalculateCash();
    }

    // Kalkulasi Kembalian Otomatis (Real-time)
    function recalculateCash() {
        const card = document.getElementById('change-card');
        const title = document.getElementById('change-card-title');
        const val = document.getElementById('change-card-val');
        const desc = document.getElementById('change-card-desc');
        const btnSubmit = document.getElementById('btn-submit-payment');

        if (currentCashReceived === 0) {
            card.style.backgroundColor = '#f8f9fa';
            card.style.borderColor = '#d9dee3';
            title.className = 'text-uppercase small fw-bold text-muted mb-1';
            title.innerHTML = '<i class="bx bx-info-circle me-1"></i> Kembalian';
            val.className = 'mb-0 fw-bolder text-muted';
            val.innerText = 'Rp 0';
            desc.innerText = 'Silakan masukkan uang pembayaran yang diterima dari pembeli.';
            btnSubmit.disabled = true;
            return;
        }

        if (currentCashReceived >= currentTotal) {
            const change = currentCashReceived - currentTotal;
            card.style.backgroundColor = 'rgba(113, 221, 55, 0.12)';
            card.style.borderColor = '#71dd37';
            title.className = 'text-uppercase small fw-bold text-success mb-1';
            title.innerHTML = '<i class="bx bx-check-circle me-1"></i> Uang Kembalian (Pas / Lebih)';
            val.className = 'mb-0 fw-bolder text-success fs-2';
            val.innerText = formatRp(change);
            desc.innerHTML = change === 0 
                ? '<span class="text-success fw-semibold"><i class="bx bx-check-double me-1"></i>Uang Pas! Tidak ada kembalian.</span>' 
                : `<span class="text-success fw-semibold">Kembalikan <strong>${formatRp(change)}</strong> kepada pelanggan.</span>`;
            btnSubmit.disabled = false;
        } else {
            const diff = currentTotal - currentCashReceived;
            card.style.backgroundColor = 'rgba(255, 62, 29, 0.1)';
            card.style.borderColor = '#ff3e1d';
            title.className = 'text-uppercase small fw-bold text-danger mb-1';
            title.innerHTML = '<i class="bx bx-error-circle me-1"></i> Uang Pembayaran Kurang';
            val.className = 'mb-0 fw-bolder text-danger fs-3';
            val.innerText = `- ${formatRp(diff)}`;
            desc.innerHTML = `<span class="text-danger fw-semibold">Uang tunai kurang sebesar <strong>${formatRp(diff)}</strong>.</span>`;
            btnSubmit.disabled = true;
        }
    }

    // Generate tombol nominal cerdas (pembulatan pecahan di atas total)
    function generateDynamicCashButtons() {
        const container = document.getElementById('dynamic-cash-container');
        if (!container) return;
        container.innerHTML = '';

        if (currentTotal <= 0) return;

        const roundSteps = [5000, 10000, 20000, 50000, 100000, 200000];
        const suggestions = new Set();

        roundSteps.forEach(step => {
            const rounded = Math.ceil(currentTotal / step) * step;
            if (rounded > currentTotal && rounded <= 2000000) {
                suggestions.add(rounded);
            }
        });

        const sorted = Array.from(suggestions).sort((a, b) => a - b).slice(0, 4);

        if (sorted.length > 0) {
            const label = document.createElement('span');
            label.className = 'small text-muted me-1';
            label.style.fontSize = '0.75rem';
            label.innerText = 'Pecahan Pas:';
            container.appendChild(label);

            sorted.forEach(val => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-xs btn-outline-primary fw-semibold';
                btn.innerText = formatRp(val);
                btn.onclick = () => setCashAmount(val);
                container.appendChild(btn);
            });
        }
    }

    // Input Uang Muka (DP) Hutang
    function onDebtDpChange(el) {
        const raw = el.value.replace(/\D/g, '');
        let val = parseInt(raw) || 0;
        if (val > currentTotal) {
            val = currentTotal;
            showToast('Uang muka (DP) tidak boleh melebihi total tagihan!', 'warning');
        }
        currentDebtDp = val;
        el.value = val > 0 ? val.toLocaleString('id-ID') : '';
        recalculateDebt();
    }

    // Kalkulasi Sisa Hutang
    function recalculateDebt() {
        const customerSelect = document.getElementById('pos-customer-select');
        const selectedOpt = customerSelect.options[customerSelect.selectedIndex];
        const customerDebt = selectedOpt ? (parseFloat(selectedOpt.getAttribute('data-debt')) || 0) : 0;

        const remainingNewDebt = Math.max(0, currentTotal - currentDebtDp);
        const accumulatedTotal = customerDebt + remainingNewDebt;

        document.getElementById('debt-new-amount').innerText = formatRp(remainingNewDebt);
        document.getElementById('debt-total-accumulated').innerText = formatRp(accumulatedTotal);

        const btnSubmit = document.getElementById('btn-submit-payment');
        const customerId = customerSelect.value;
        const dueDate = document.getElementById('debt-due-date').value;

        if (!customerId || !dueDate) {
            btnSubmit.disabled = true;
        } else {
            btnSubmit.disabled = false;
        }
    }

    // Event listener shortcut Enter di input cash
    const cashInputEl = document.getElementById('cash-received-input');
    if (cashInputEl) {
        cashInputEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const btnSubmit = document.getElementById('btn-submit-payment');
                if (btnSubmit && !btnSubmit.disabled) {
                    btnSubmit.click();
                }
            }
        });
    }

    // Listener jika kasir mengganti pelanggan di dropdown saat masih di POS
    document.getElementById('pos-customer-select').addEventListener('change', function () {
        const customerId = this.value;
        const debtRadio = document.getElementById('pay_method_debt');
        const debtLabel = document.getElementById('pay_method_debt_label');
        const debtNotice = document.getElementById('debt-disabled-hint');

        if (!customerId) {
            debtRadio.disabled = true;
            debtLabel.classList.add('disabled');
            debtNotice.classList.remove('d-none');
            if (selectedPaymentMethod === 'debt') {
                document.getElementById('pay_method_cash').checked = true;
                selectedPaymentMethod = 'cash';
            }
        } else {
            debtRadio.disabled = false;
            debtLabel.classList.remove('disabled');
            debtNotice.classList.add('d-none');
        }
    });

    // Helper Update UI Badge Stok di Katalog Tanpa Reload
    function updateCatalogStockUI(productId, newStock, minStock = 5) {
        const box = document.getElementById(`stock-badge-box-${productId}`);
        const col = document.querySelector(`.product-item-col[data-id="${productId}"]`);
        const card = col ? col.querySelector('.product-card') : null;

        if (box) {
            if (newStock <= 0) {
                box.innerHTML = `<span class="badge bg-danger">Habis</span>`;
                if (card) card.classList.add('out-of-stock');
            } else if (newStock <= minStock) {
                box.innerHTML = `<span class="badge bg-warning text-dark">Sisa ${newStock}</span>`;
                if (card) card.classList.remove('out-of-stock');
            } else {
                box.innerHTML = `<span class="badge bg-success">Stok ${newStock}</span>`;
                if (card) card.classList.remove('out-of-stock');
            }
        }
        if (col) {
            col.setAttribute('data-stock', newStock);
        }
    }

    // Proses Submit Checkout & Eksekusi Database Transaction (FASE 4.3)
    function processCheckout() {
        const customerSelect = document.getElementById('pos-customer-select');
        const customerId = customerSelect.value;
        const notes = document.getElementById('checkout-notes').value.trim();

        if (cart.length === 0) {
            showToast('Keranjang masih kosong!', 'danger');
            return;
        }

        if (selectedPaymentMethod === 'cash') {
            if (currentCashReceived < currentTotal) {
                showToast('Uang tunai yang diterima belum mencukupi!', 'danger');
                return;
            }
        } else if (selectedPaymentMethod === 'debt') {
            if (!customerId) {
                showToast('Metode hutang hanya dapat digunakan untuk pelanggan terdaftar!', 'danger');
                return;
            }
            const dueDate = document.getElementById('debt-due-date').value;
            if (!dueDate) {
                showToast('Tanggal jatuh tempo hutang wajib diisi!', 'warning');
                return;
            }
        }

        const payload = {
            customer_id: customerId ? parseInt(customerId) : null,
            subtotal: currentSubtotal,
            discount: currentDiscount,
            global_discount_type: globalDiscount.type,
            global_discount_value: globalDiscount.value,
            total_amount: currentTotal,
            paid_amount: selectedPaymentMethod === 'cash' ? currentCashReceived : (selectedPaymentMethod === 'debt' ? currentDebtDp : currentTotal),
            change_amount: selectedPaymentMethod === 'cash' ? Math.max(0, currentCashReceived - currentTotal) : 0,
            payment_method: selectedPaymentMethod,
            due_date: selectedPaymentMethod === 'debt' ? document.getElementById('debt-due-date').value : null,
            notes: notes || null,
            items: cart.map(i => ({
                product_id: i.id,
                name: i.name,
                qty: i.qty,
                price: i.price,
                discount_type: i.discount.type,
                discount_value: i.discount.value
            }))
        };

        const btnSubmit = document.getElementById('btn-submit-payment');
        const origBtnContent = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan Transaksi...`;

        // Kirim ke server via AJAX ke endpoint checkout POS (DB::transaction)
        fetch('/kasir/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || 'Gagal menyimpan transaksi ke database.');
            }
            return data;
        })
        .then(data => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = origBtnContent;

            if (data.success && data.sale) {
                // 1. Sembunyikan Modal Checkout
                const modalInstance = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
                if (modalInstance) modalInstance.hide();

                // 2. Mainkan Suara Sukses (Chime ganda)
                playBeep(987, 0.1, 'sine');
                setTimeout(() => playBeep(1318, 0.18, 'sine'), 110);

                // 3. Kurangi stok produk secara lokal & update badge UI di katalog tanpa refresh
                data.sale.items.forEach(sold => {
                    const prod = allProducts.find(p => p.id === sold.product_id);
                    if (prod) {
                        prod.stock = Math.max(0, prod.stock - sold.qty);
                        updateCatalogStockUI(prod.id, prod.stock, prod.min_stock);
                    }
                });

                // 4. Update data hutang pelanggan pada dropdown jika transaksi menggunakan hutang
                if (data.sale.customer_id && data.sale.payment_method === 'debt') {
                    const opt = document.querySelector(`#pos-customer-select option[value="${data.sale.customer_id}"]`);
                    if (opt) {
                        opt.setAttribute('data-debt', data.sale.customer_debt);
                        const cleanName = data.sale.customer_name;
                        opt.textContent = `${cleanName} [Hutang: ${formatRp(data.sale.customer_debt)}]`;
                    }
                }

                // 5. Simpan transaksi yang baru selesai ke state window untuk Cetak Struk (FASE 4.4)
                window.lastCompletedSale = data.sale;

                // Tampilkan tombol Cetak Ulang Struk Terakhir di header keranjang
                const btnReprint = document.getElementById('btn-reprint-last');
                if (btnReprint) btnReprint.style.display = 'inline-flex';

                // 6. Kosongkan keranjang belanja kasir
                clearCart();

                // 7. Buka Modal Cetak Struk Berhasil (FASE 4.4)
                showReceiptSuccessModal(data.sale);
            }
        })
        .catch(err => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = origBtnContent;
            playBeep(300, 0.2, 'sawtooth');
            showToast(err.message || 'Terjadi kesalahan sistem.', 'danger');
            console.error('Checkout error:', err);
        });
    }

    // ─────────────────────────────────────────────────────────────
    // 10. FITUR CETAK STRUK & MODAL TRANSAKSI SELESAI (FASE 4.4)
    // ─────────────────────────────────────────────────────────────

    function showReceiptSuccessModal(sale) {
        document.getElementById('success-invoice-no').innerText = sale.invoice_number;
        document.getElementById('success-total-amount').innerText = formatRp(sale.total_amount);

        let methodBadge = sale.payment_method.toUpperCase();
        if (sale.payment_method === 'cash') methodBadge = 'TUNAI';
        else if (sale.payment_method === 'debt') methodBadge = 'HUTANG (BON)';
        else if (sale.payment_method === 'transfer') methodBadge = 'TRANSFER BANK';
        document.getElementById('success-payment-method').innerText = methodBadge;

        const changeRow = document.getElementById('success-change-row');
        const changeLabel = document.getElementById('success-change-label');
        const changeVal = document.getElementById('success-change-amount');

        if (sale.payment_method === 'cash') {
            changeRow.style.display = 'flex';
            changeLabel.innerText = 'Kembalian:';
            changeVal.className = 'text-success fs-6';
            changeVal.innerText = formatRp(sale.change_amount);
        } else if (sale.payment_method === 'debt') {
            changeRow.style.display = 'flex';
            changeLabel.innerText = 'Sisa Hutang Faktur:';
            changeVal.className = 'text-danger fs-6';
            const remaining = Math.max(0, sale.total_amount - sale.paid_amount);
            changeVal.innerText = formatRp(remaining);
        } else {
            changeRow.style.display = 'none';
        }

        // Link tombol Download PDF
        const pdfBtn = document.getElementById('success-download-pdf-btn');
        if (pdfBtn) {
            pdfBtn.href = `/kasir/receipt/${sale.id}/pdf`;
        }

        const modal = new bootstrap.Modal(document.getElementById('receiptSuccessModal'));
        modal.show();

        // Fokuskan tombol Transaksi Baru agar kasir bisa langsung tekan Enter jika tidak mau cetak
        setTimeout(() => {
            const nextBtn = document.getElementById('btn-next-transaction');
            if (nextBtn) nextBtn.focus();
        }, 300);
    }

    // Cetak Struk Thermal ke jendela popup baru
    function printReceipt(paper = '58') {
        const sale = window.lastCompletedSale;
        if (!sale) return;

        const url = `/kasir/receipt/${sale.id}?paper=${paper}&autoprint=1`;
        const width = paper === '80' ? 440 : 380;
        const height = 650;
        const left = (window.screen.width / 2) - (width / 2);
        const top = (window.screen.height / 2) - (height / 2);

        window.open(url, `Struk_${sale.invoice_number}`, `width=${width},height=${height},top=${top},left=${left},scrollbars=yes,status=no`);
    }

    // Cetak Ulang Struk Terakhir dari tombol header keranjang
    function printLastReceipt() {
        if (!window.lastCompletedSale) {
            showToast('Belum ada transaksi yang diselesaikan pada sesi kasir ini.', 'warning');
            return;
        }
        showReceiptSuccessModal(window.lastCompletedSale);
    }

    // Reset Kasir & Siap Transaksi Baru
    function finishSaleAndReset() {
        const modalEl = document.getElementById('receiptSuccessModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        setTimeout(() => {
            searchInput.focus();
            searchInput.select();
        }, 150);
    }
</script>
@endsection
