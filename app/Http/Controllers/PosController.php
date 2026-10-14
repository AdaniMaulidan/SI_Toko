<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PosController extends Controller
{
    /**
     * Tampilkan halaman Point of Sale (POS) Kasir (FASE 4.1)
     */
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        $customers  = Customer::orderBy('name')->get();

        // Ambil produk yang aktif untuk katalog POS
        $products = Product::active()
            ->with(['category', 'unit'])
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'id'            => $product->id,
                    'barcode'       => $product->barcode,
                    'name'          => $product->name,
                    'category_id'   => $product->category_id,
                    'category_name' => $product->category->name ?? 'Umum',
                    'unit_name'     => $product->unit->name ?? 'Pcs',
                    'unit_symbol'   => $product->unit->symbol ?? '',
                    'cost_price'    => (float) $product->cost_price,
                    'selling_price' => (float) $product->selling_price,
                    'stock'         => (int) $product->stock,
                    'min_stock'     => (int) $product->min_stock,
                    'image_url'     => $product->image ? asset('storage/' . $product->image) : null,
                ];
            });

        return view('kasir.index', compact('categories', 'customers', 'products'));
    }

    /**
     * Endpoint API untuk scan barcode cepat atau autocomplete pencarian produk (FASE 4.1)
     */
    public function searchProducts(Request $request)
    {
        $query = Product::active()->with(['category', 'unit']);

        // 1. Pencarian tepat berdasarkan scan barcode
        if ($request->filled('barcode')) {
            $product = $query->where('barcode', trim($request->barcode))->first();
            
            if ($product) {
                return response()->json([
                    'success' => true,
                    'product' => [
                        'id'            => $product->id,
                        'barcode'       => $product->barcode,
                        'name'          => $product->name,
                        'category_id'   => $product->category_id,
                        'category_name' => $product->category->name ?? 'Umum',
                        'unit_name'     => $product->unit->name ?? 'Pcs',
                        'unit_symbol'   => $product->unit->symbol ?? '',
                        'cost_price'    => (float) $product->cost_price,
                        'selling_price' => (float) $product->selling_price,
                        'stock'         => (int) $product->stock,
                        'min_stock'     => (int) $product->min_stock,
                        'image_url'     => $product->image ? asset('storage/' . $product->image) : null,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Produk dengan barcode "' . $request->barcode . '" tidak ditemukan atau berstatus nonaktif.'
            ], 404);
        }

        // 2. Pencarian kata kunci nama atau kode barcode
        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $products = $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('barcode', 'like', "%{$keyword}%");
            })->limit(25)->get()->map(function ($p) {
                return [
                    'id'            => $p->id,
                    'barcode'       => $p->barcode,
                    'name'          => $p->name,
                    'category_id'   => $p->category_id,
                    'category_name' => $p->category->name ?? 'Umum',
                    'unit_name'     => $p->unit->name ?? 'Pcs',
                    'unit_symbol'   => $p->unit->symbol ?? '',
                    'cost_price'    => (float) $p->cost_price,
                    'selling_price' => (float) $p->selling_price,
                    'stock'         => (int) $p->stock,
                    'min_stock'     => (int) $p->min_stock,
                    'image_url'     => $p->image ? asset('storage/' . $p->image) : null,
                ];
            });

            return response()->json([
                'success'  => true,
                'products' => $products
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kata kunci atau barcode wajib disediakan.'
        ], 400);
    }

    /**
     * Eksekusi transaksi checkout POS dengan Database Transaction (FASE 4.3)
     */
    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_method'         => 'required|in:cash,qris,transfer,debt',
            'customer_id'            => 'nullable|exists:customers,id',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.qty'            => 'required|integer|min:1',
            'items.*.discount_type'  => 'nullable|in:rp,pct',
            'items.*.discount_value' => 'nullable|numeric|min:0',
            'global_discount_type'   => 'nullable|in:rp,pct',
            'global_discount_value'  => 'nullable|numeric|min:0',
            'paid_amount'            => 'required|numeric|min:0',
            'due_date'               => 'nullable|date',
            'notes'                  => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        $paymentMethod = $request->payment_method;
        $customerId    = $request->customer_id;
        $dueDate       = $request->due_date;

        // Validasi khusus metode Hutang
        if ($paymentMethod === 'debt') {
            if (!$customerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi hutang wajib memilih pelanggan terdaftar.'
                ], 422);
            }

            if (!$dueDate || $dueDate < date('Y-m-d')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal jatuh tempo hutang tidak valid atau sudah lewat.'
                ], 422);
            }
        }

        try {
            $sale = DB::transaction(function () use ($request, $paymentMethod, $customerId, $dueDate) {
                // 1. Validasi & Hitung Total dari Database (Server-side Integrity)
                $subtotalGross = 0;
                $totalItemDiscount = 0;
                $processedItems = [];

                foreach ($request->items as $itemData) {
                    // Lock baris produk untuk mencegah race condition kuantitas stok
                    $product = Product::lockForUpdate()->find($itemData['product_id']);

                    if (!$product || !$product->is_active) {
                        throw new Exception("Produk #{$itemData['product_id']} tidak aktif atau tidak ditemukan.");
                    }

                    $qty = (int) $itemData['qty'];
                    if ($product->stock < $qty) {
                        throw new Exception("Stok untuk produk '{$product->name}' tidak mencukupi! Tersisa {$product->stock} {$product->unit?->symbol}, diminta {$qty}.");
                    }

                    $itemPrice = (float) $product->selling_price;
                    $itemGross = $itemPrice * $qty;
                    $subtotalGross += $itemGross;

                    // Hitung diskon per item
                    $itemDiscVal = isset($itemData['discount_value']) ? (float) $itemData['discount_value'] : 0;
                    $itemDiscType = $itemData['discount_type'] ?? 'rp';
                    $discAmount = 0;

                    if ($itemDiscVal > 0) {
                        if ($itemDiscType === 'pct') {
                            $discAmount = round(($itemGross * min(100, $itemDiscVal)) / 100, 2);
                        } else {
                            $discAmount = min($itemGross, $itemDiscVal);
                        }
                    }
                    $totalItemDiscount += $discAmount;
                    $itemSubtotal = max(0, $itemGross - $discAmount);

                    $processedItems[] = [
                        'product'       => $product,
                        'qty'           => $qty,
                        'cost_price'    => (float) $product->cost_price,
                        'selling_price' => $itemPrice,
                        'subtotal'      => $itemSubtotal,
                    ];
                }

                // 2. Hitung Diskon Global / Transaksi
                $subtotalAfterItemDisc = max(0, $subtotalGross - $totalItemDiscount);
                $globalDiscVal = (float) ($request->global_discount_value ?? 0);
                $globalDiscType = $request->global_discount_type ?? 'rp';
                $globalDiscAmount = 0;

                if ($globalDiscVal > 0) {
                    if ($globalDiscType === 'pct') {
                        $globalDiscAmount = round(($subtotalAfterItemDisc * min(100, $globalDiscVal)) / 100, 2);
                    } else {
                        $globalDiscAmount = min($subtotalAfterItemDisc, $globalDiscVal);
                    }
                }

                $totalDiscount = $totalItemDiscount + $globalDiscAmount;
                $finalTotal = max(0, $subtotalGross - $totalDiscount);

                // 3. Validasi & Hitung Nominal Pembayaran
                $paidInput = (float) $request->paid_amount;
                $paidAmount = 0;
                $changeAmount = 0;
                $paymentStatus = 'paid';

                if ($paymentMethod === 'cash') {
                    if ($paidInput < $finalTotal) {
                        throw new Exception("Uang tunai yang diterima (Rp " . number_format($paidInput, 0, ',', '.') . ") kurang dari total belanja (Rp " . number_format($finalTotal, 0, ',', '.') . ").");
                    }
                    $paidAmount = $paidInput;
                    $changeAmount = max(0, $paidInput - $finalTotal);
                    $paymentStatus = 'paid';
                } elseif ($paymentMethod === 'qris' || $paymentMethod === 'transfer') {
                    $paidAmount = $finalTotal;
                    $changeAmount = 0;
                    $paymentStatus = 'paid';
                } elseif ($paymentMethod === 'debt') {
                    // Pada hutang, paid_amount adalah uang muka (DP)
                    $dp = min($finalTotal, max(0, $paidInput));
                    $paidAmount = $dp;
                    $changeAmount = 0;
                    $paymentStatus = 'debt';
                }

                // 4. Generate Nomor Faktur Unik (INV-YYYYMMDD-XXXX)
                $datePrefix = date('Ymd');
                $countToday = Sale::whereDate('created_at', today())->count() + 1;
                $invoiceNumber = 'INV-' . $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
                while (Sale::where('invoice_number', $invoiceNumber)->exists()) {
                    $countToday++;
                    $invoiceNumber = 'INV-' . $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
                }

                // 5. Simpan Record ke Tabel Sales
                $sale = Sale::create([
                    'invoice_number' => $invoiceNumber,
                    'user_id'        => Auth::id(),
                    'customer_id'    => $customerId,
                    'subtotal'       => $subtotalGross,
                    'discount'       => $totalDiscount,
                    'total_amount'   => $finalTotal,
                    'paid_amount'    => $paidAmount,
                    'change_amount'  => $changeAmount,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentStatus,
                    'notes'          => $request->notes,
                ]);

                // 6. Simpan Record ke Tabel SaleItems & Kurangi Stok Produk
                foreach ($processedItems as $item) {
                    $sale->items()->create([
                        'product_id'    => $item['product']->id,
                        'cost_price'    => $item['cost_price'],
                        'selling_price' => $item['selling_price'],
                        'quantity'      => $item['qty'],
                        'subtotal'      => $item['subtotal'],
                    ]);

                    // Kurangi stok produk secara otomatis
                    $item['product']->decrement('stock', $item['qty']);
                }

                // 7. Catat Kas Masuk ke Tabel CashFlows
                $cashFlowIn = match ($paymentMethod) {
                    'cash', 'qris', 'transfer' => $finalTotal,
                    'debt'                     => $paidAmount, // Hanya DP yang masuk kas pada transaksi ini
                    default                    => 0,
                };

                if ($cashFlowIn > 0) {
                    $methodLabel = match ($paymentMethod) {
                        'cash'     => 'Tunai',
                        'qris'     => 'QRIS',
                        'transfer' => 'Transfer Bank',
                        'debt'     => 'DP Hutang',
                    };

                    CashFlow::create([
                        'user_id'        => Auth::id(),
                        'type'           => 'in',
                        'category'       => 'Penjualan',
                        'amount'         => $cashFlowIn,
                        'reference_type' => 'sale',
                        'reference_id'   => $sale->id,
                        'date'           => now()->toDateString(),
                        'notes'          => "Kas masuk penjualan {$invoiceNumber} ({$methodLabel})" . ($request->notes ? " - {$request->notes}" : ""),
                    ]);
                }

                // 8. Jika Metode Hutang: Catat ke Tabel CustomerDebts & Update Total Hutang Pelanggan
                if ($paymentMethod === 'debt') {
                    $remainingDebt = max(0, $finalTotal - $paidAmount);
                    if ($remainingDebt > 0) {
                        $customer = Customer::lockForUpdate()->find($customerId);
                        if ($customer) {
                            CustomerDebt::create([
                                'sale_id'        => $sale->id,
                                'customer_id'    => $customer->id,
                                'total_debt'     => $remainingDebt,
                                'remaining_debt' => $remainingDebt,
                                'due_date'       => $dueDate,
                                'status'         => 'unpaid',
                                'notes'          => "Hutang transaksi penjualan {$invoiceNumber}" . ($request->notes ? " - {$request->notes}" : ""),
                            ]);

                            // Tambahkan akumulasi saldo hutang pelanggan
                            $customer->increment('total_debt', $remainingDebt);
                        }
                    }
                }

                return $sale;
            });

            // Muat relasi lengkap untuk kebutuhan response dan struk
            $sale->load(['items.product', 'customer', 'user']);

            return response()->json([
                'success'        => true,
                'message'        => 'Transaksi penjualan berhasil disimpan!',
                'sale'           => [
                    'id'             => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'subtotal'       => (float) $sale->subtotal,
                    'discount'       => (float) $sale->discount,
                    'total_amount'   => (float) $sale->total_amount,
                    'paid_amount'    => (float) $sale->paid_amount,
                    'change_amount'  => (float) $sale->change_amount,
                    'payment_method' => $sale->payment_method,
                    'payment_status' => $sale->payment_status,
                    'customer_name'  => $sale->customer?->name ?? 'Pelanggan Umum',
                    'customer_id'    => $sale->customer_id,
                    'customer_debt'  => $sale->customer ? (float) $sale->customer->fresh()->total_debt : 0,
                    'cashier_name'   => $sale->user?->name ?? 'Kasir',
                    'created_at'     => $sale->created_at->format('d/m/Y H:i:s'),
                    'items_count'    => $sale->items->count(),
                    'items'          => $sale->items->map(fn($it) => [
                        'product_id'   => $it->product_id,
                        'product_name' => $it->product?->name ?? 'Barang',
                        'qty'          => $it->quantity,
                        'price'        => (float) $it->selling_price,
                        'subtotal'     => (float) $it->subtotal,
                    ]),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
