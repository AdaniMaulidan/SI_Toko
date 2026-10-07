@extends('layouts.pos')

@section('title', 'Kasir (POS)')

@section('content')
<div class="pos-wrapper d-flex gap-3 h-100">
    
    <!-- Left: Product Catalog & Search -->
    <div class="pos-left d-flex flex-column h-100 flex-grow-1">
        <!-- Search Bar -->
        <div class="card mb-3">
            <div class="card-body p-3">
                <div class="row gx-2">
                    <div class="col-md-8">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control form-control-lg" placeholder="Cari barcode, nama produk, atau kode (F2)" autofocus>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-lg btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="bx bx-barcode-reader"></i> Scan Kamera
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Filter -->
        <div class="mb-3 d-flex gap-2 overflow-auto py-1" style="white-space: nowrap;">
            <button class="btn btn-primary rounded-pill">Semua</button>
            <button class="btn btn-outline-primary rounded-pill">Sembako</button>
            <button class="btn btn-outline-primary rounded-pill">Minuman</button>
            <button class="btn btn-outline-primary rounded-pill">Snack</button>
            <button class="btn btn-outline-primary rounded-pill">Rokok</button>
            <button class="btn btn-outline-primary rounded-pill">Sabun & Sampo</button>
        </div>

        <!-- Product Grid -->
        <div class="row g-3 overflow-auto flex-grow-1 align-content-start pb-3">
            @php
            $dummyProducts = [
                ['id'=>1, 'name'=>'Indomie Goreng', 'code'=>'BRG-001', 'price'=>3000, 'stock'=>150, 'cat'=>'Sembako'],
                ['id'=>2, 'name'=>'Aqua Botol 600ml', 'code'=>'BRG-002', 'price'=>3500, 'stock'=>85, 'cat'=>'Minuman'],
                ['id'=>3, 'name'=>'Beras Sania 5kg', 'code'=>'BRG-003', 'price'=>72000, 'stock'=>24, 'cat'=>'Sembako'],
                ['id'=>4, 'name'=>'Chitato Sapi Panggang', 'code'=>'BRG-004', 'price'=>11000, 'stock'=>42, 'cat'=>'Snack'],
                ['id'=>5, 'name'=>'Marlboro Merah', 'code'=>'BRG-005', 'price'=>42000, 'stock'=>30, 'cat'=>'Rokok'],
                ['id'=>6, 'name'=>'Sunlight Jeruk Nipis', 'code'=>'BRG-006', 'price'=>15000, 'stock'=>18, 'cat'=>'Sabun & Sampo'],
                ['id'=>7, 'name'=>'Teh Pucuk Harum', 'code'=>'BRG-007', 'price'=>4000, 'stock'=>120, 'cat'=>'Minuman'],
                ['id'=>8, 'name'=>'Minyak Bimoli 2L', 'code'=>'BRG-008', 'price'=>38000, 'stock'=>10, 'cat'=>'Sembako'],
                ['id'=>9, 'name'=>'Silverqueen Almond', 'code'=>'BRG-009', 'price'=>16500, 'stock'=>25, 'cat'=>'Snack'],
            ];
            @endphp
            
            @foreach($dummyProducts as $prod)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card h-100 cursor-pointer product-card border shadow-none" onclick="addToCart({{ $prod['id'] }}, '{{ $prod['name'] }}', {{ $prod['price'] }})">
                    <div class="card-body p-3 text-center">
                        <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3">
                            <span class="avatar-initial rounded">
                                <i class="bx bx-package fs-2"></i>
                            </span>
                        </div>
                        <h6 class="mb-1 text-truncate" title="{{ $prod['name'] }}">{{ $prod['name'] }}</h6>
                        <div class="text-primary fw-bold mb-1">Rp {{ number_format($prod['price'], 0, ',', '.') }}</div>
                        <small class="text-muted">Stok: {{ $prod['stock'] }}</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Right: Cart & Checkout -->
    <div class="pos-right card h-100 d-flex flex-column shadow-sm" style="width: 400px; border: none;">
        <!-- Cart Header -->
        <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center bg-white">
            <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="bx bx-cart text-primary fs-4"></i> Keranjang
            </h5>
            <span class="badge bg-danger rounded-pill" id="cart-count">0 item</span>
        </div>

        <!-- Customer & Info (Optional) -->
        <div class="p-3 border-bottom bg-white">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bx bx-user"></i></span>
                <input type="text" class="form-control" placeholder="Pelanggan Umum (Opsional)">
                <button class="btn btn-outline-secondary" type="button"><i class="bx bx-search"></i></button>
            </div>
        </div>

        <!-- Cart Items -->
        <div class="card-body p-0 pos-cart-items bg-white flex-grow-1 overflow-auto" id="cart-items">
            <!-- Empty state -->
            <div class="text-center p-5 text-muted" id="empty-cart-msg">
                <i class="bx bx-cart-add fs-1 mb-2 text-lighter"></i>
                <p class="mb-0">Keranjang masih kosong.<br>Silakan pilih atau scan produk.</p>
            </div>
            
            <!-- Items will be injected here via JS -->
        </div>

        <!-- Cart Footer / Totals -->
        <div class="border-top bg-white p-3">
            <div class="d-flex justify-content-between mb-2 text-muted">
                <span>Subtotal</span>
                <span id="subtotal-val">Rp 0</span>
            </div>
            <div class="d-flex justify-content-between mb-3 text-muted">
                <span>Diskon</span>
                <span class="text-danger">- Rp 0</span>
            </div>
            <div class="d-flex justify-content-between mb-3 align-items-center">
                <h4 class="mb-0">Total</h4>
                <h3 class="mb-0 text-primary fw-bold" id="total-val">Rp 0</h3>
            </div>
            
            <div class="row gx-2">
                <div class="col-4">
                    <button class="btn btn-outline-danger w-100" onclick="clearCart()">
                        <i class="bx bx-trash me-1"></i> Batal
                    </button>
                </div>
                <div class="col-8">
                    <button class="btn btn-success w-100 py-3 d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#paymentModal" id="btn-pay" disabled>
                        <i class="bx bx-wallet fs-4"></i>
                        <span class="fs-5">Bayar (F9)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pembayaran -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fs-4 fw-bold">Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <p class="text-muted mb-1">Total Tagihan</p>
                    <h2 class="text-primary fw-bold mb-0" id="modal-total-val">Rp 0</h2>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Metode Pembayaran</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="method_tunai" autocomplete="off" checked>
                            <label class="btn btn-outline-primary w-100 d-flex flex-column p-2" for="method_tunai">
                                <i class="bx bx-money fs-3 mb-1"></i> Tunai
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="method_qris" autocomplete="off">
                            <label class="btn btn-outline-primary w-100 d-flex flex-column p-2" for="method_qris">
                                <i class="bx bx-qr-scan fs-3 mb-1"></i> QRIS
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="method_transfer" autocomplete="off">
                            <label class="btn btn-outline-primary w-100 d-flex flex-column p-2" for="method_transfer">
                                <i class="bx bx-transfer fs-3 mb-1"></i> Transfer
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payment_method" id="method_ewallet" autocomplete="off">
                            <label class="btn btn-outline-primary w-100 d-flex flex-column p-2" for="method_ewallet">
                                <i class="bx bx-mobile fs-3 mb-1"></i> E-Wallet
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3" id="cash-input-group">
                    <label class="form-label fw-bold">Jumlah Uang Diterima (Rp)</label>
                    <input type="text" class="form-control form-control-lg text-end fw-bold" id="cash-input" placeholder="0" onkeyup="calculateChange()">
                    
                    <div class="d-flex gap-2 mt-2 flex-wrap">
                        <button class="btn btn-sm btn-outline-secondary flex-grow-1" onclick="setCash(50000)">50k</button>
                        <button class="btn btn-sm btn-outline-secondary flex-grow-1" onclick="setCash(100000)">100k</button>
                        <button class="btn btn-sm btn-outline-secondary flex-grow-1" onclick="setCashExact()">Uang Pas</button>
                    </div>
                </div>

                <div class="rounded bg-lighter p-3 mb-3 d-flex justify-content-between align-items-center border">
                    <span class="fs-5 text-muted">Kembalian</span>
                    <span class="fs-4 fw-bold text-success" id="change-val">Rp 0</span>
                </div>
            </div>
            <div class="modal-footer border-top p-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="processPayment()">Selesaikan Pembayaran</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Struk (Receipt) -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="clearCart()"></button>
            </div>
            <div class="modal-body pt-1" id="receipt-print">
                <div class="text-center mb-3">
                    <h5 class="fw-bold mb-0">SI TOKO</h5>
                    <small>Jl. Contoh Alamat No. 123, Kota</small><br>
                    <small>Telp: 0812-3456-7890</small>
                </div>
                <div class="border-bottom border-dashed mb-2 pb-2">
                    <div class="d-flex justify-content-between"><small>Waktu:</small> <small id="receipt-time"></small></div>
                    <div class="d-flex justify-content-between"><small>Kasir:</small> <small>Admin</small></div>
                    <div class="d-flex justify-content-between"><small>No:</small> <small>TRX-10029</small></div>
                </div>
                
                <div id="receipt-items" class="mb-2">
                    <!-- Items printed here -->
                </div>
                
                <div class="border-top border-dashed pt-2 mb-3">
                    <div class="d-flex justify-content-between fw-bold"><span>Total</span> <span id="receipt-total"></span></div>
                    <div class="d-flex justify-content-between"><small>Tunai</small> <small id="receipt-cash"></small></div>
                    <div class="d-flex justify-content-between"><small>Kembali</small> <small id="receipt-change"></small></div>
                </div>
                <div class="text-center">
                    <small>Terima kasih atas kunjungan Anda!</small>
                </div>
            </div>
            <div class="modal-footer border-top p-2 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal" onclick="clearCart()">Tutup</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                    <i class="bx bx-printer me-1"></i> Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-style')
<style>
    .product-card { transition: all 0.2s ease-in-out; }
    .product-card:hover { transform: translateY(-3px); box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45) !important; border-color: var(--bs-primary) !important; }
    .cart-item { transition: background 0.1s; }
    .cart-item:hover { background: #f8f9fa; }
    .border-dashed { border-style: dashed !important; border-width: 1px !important; border-color: #d9dee3 !important; }
</style>
@endsection

@section('page-script')
<script>
    let cart = [];
    let total = 0;
    
    const formatRp = (num) => 'Rp ' + num.toLocaleString('id-ID');
    const parseRp = (str) => parseInt(str.replace(/[^0-9]/g, '')) || 0;

    function addToCart(id, name, price) {
        document.getElementById('empty-cart-msg').style.display = 'none';
        
        const existing = cart.find(item => item.id === id);
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({ id, name, price, qty: 1 });
        }
        renderCart();
    }

    function updateQty(id, change) {
        const item = cart.find(i => i.id === id);
        if (item) {
            item.qty += change;
            if (item.qty <= 0) {
                cart = cart.filter(i => i.id !== id);
            }
            renderCart();
        }
    }

    function renderCart() {
        const container = document.getElementById('cart-items');
        // keep empty msg element around, just hide/show
        const emptyMsg = document.getElementById('empty-cart-msg');
        
        // Remove old items
        Array.from(container.children).forEach(child => {
            if (child.id !== 'empty-cart-msg') child.remove();
        });

        total = 0;
        let count = 0;

        if (cart.length === 0) {
            emptyMsg.style.display = 'block';
            document.getElementById('btn-pay').disabled = true;
        } else {
            emptyMsg.style.display = 'none';
            document.getElementById('btn-pay').disabled = false;
            
            cart.forEach(item => {
                const subtotal = item.price * item.qty;
                total += subtotal;
                count += item.qty;
                
                const div = document.createElement('div');
                div.className = 'cart-item border-bottom p-3';
                div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="fw-medium text-heading">${item.name}</span>
                        <span class="fw-bold">${formatRp(subtotal)}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">${formatRp(item.price)}</div>
                        <div class="input-group input-group-sm w-auto">
                            <button class="btn btn-outline-secondary px-2" type="button" onclick="updateQty(${item.id}, -1)">
                                <i class="bx bx-minus"></i>
                            </button>
                            <input type="text" class="form-control text-center px-0" style="width: 40px;" value="${item.qty}" readonly>
                            <button class="btn btn-outline-secondary px-2" type="button" onclick="updateQty(${item.id}, 1)">
                                <i class="bx bx-plus"></i>
                            </button>
                        </div>
                    </div>
                `;
                container.appendChild(div);
            });
        }
        
        document.getElementById('cart-count').innerText = `${count} item`;
        document.getElementById('subtotal-val').innerText = formatRp(total);
        document.getElementById('total-val').innerText = formatRp(total);
        document.getElementById('modal-total-val').innerText = formatRp(total);
        calculateChange();
    }

    function clearCart() {
        cart = [];
        renderCart();
        document.getElementById('cash-input').value = '';
        document.getElementById('change-val').innerText = 'Rp 0';
    }

    function calculateChange() {
        const cashInput = document.getElementById('cash-input').value;
        const cash = parseRp(cashInput);
        
        // Format input value
        if (cashInput) {
            document.getElementById('cash-input').value = cash.toLocaleString('id-ID');
        }

        const change = cash - total;
        const changeEl = document.getElementById('change-val');
        
        if (change >= 0) {
            changeEl.innerText = formatRp(change);
            changeEl.className = 'fs-4 fw-bold text-success';
        } else {
            changeEl.innerText = 'Kurang ' + formatRp(Math.abs(change));
            changeEl.className = 'fs-4 fw-bold text-danger';
        }
    }

    function setCash(amount) {
        document.getElementById('cash-input').value = amount.toLocaleString('id-ID');
        calculateChange();
    }

    function setCashExact() {
        document.getElementById('cash-input').value = total.toLocaleString('id-ID');
        calculateChange();
    }

    function processPayment() {
        const cash = parseRp(document.getElementById('cash-input').value);
        if (cash < total && document.getElementById('method_tunai').checked) {
            alert('Jumlah uang tidak mencukupi!');
            return;
        }

        // Build receipt
        const now = new Date();
        document.getElementById('receipt-time').innerText = now.toLocaleDateString('id-ID') + ' ' + now.toLocaleTimeString('id-ID');
        
        const receiptItems = document.getElementById('receipt-items');
        receiptItems.innerHTML = '';
        cart.forEach(item => {
            receiptItems.innerHTML += `
                <div class="mb-1">
                    <div><small>${item.name}</small></div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">${item.qty} x ${item.price.toLocaleString('id-ID')}</small>
                        <small>${(item.qty * item.price).toLocaleString('id-ID')}</small>
                    </div>
                </div>
            `;
        });
        
        document.getElementById('receipt-total').innerText = total.toLocaleString('id-ID');
        
        if (document.getElementById('method_tunai').checked) {
            document.getElementById('receipt-cash').innerText = cash.toLocaleString('id-ID');
            document.getElementById('receipt-change').innerText = (cash - total).toLocaleString('id-ID');
        } else {
            document.getElementById('receipt-cash').innerText = 'NON-TUNAI';
            document.getElementById('receipt-change').innerText = '0';
        }

        // Close payment modal, show receipt modal
        bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
        new bootstrap.Modal(document.getElementById('receiptModal')).show();
    }

    // Toggle cash input based on payment method
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const cashGroup = document.getElementById('cash-input-group');
            if (this.id === 'method_tunai') {
                cashGroup.style.display = 'block';
            } else {
                cashGroup.style.display = 'none';
                setCashExact(); // Auto fill exact amount for non-cash
            }
        });
    });
</script>
@endsection
