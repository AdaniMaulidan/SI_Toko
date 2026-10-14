<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Tampilkan daftar riwayat pembelian produk
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'items.product', 'user']);

        // Filter supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter status bayar
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter rentang tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('purchase_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('purchase_date', '<=', $request->end_date);
        }

        // Filter no faktur / catatan
        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('invoice_number', 'like', "%{$keyword}%")
                  ->orWhere('notes', 'like', "%{$keyword}%");
            });
        }

        $purchases = $query->latest('purchase_date')->latest('id')->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    /**
     * Form input pembelian baru (FASE 5.1)
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        
        $products = Product::active()
            ->with(['unit', 'category'])
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                return [
                    'id'            => $p->id,
                    'barcode'       => $p->barcode,
                    'name'          => $p->name,
                    'category_name' => $p->category->name ?? '-',
                    'unit_symbol'   => $p->unit->symbol ?? 'Pcs',
                    'cost_price'    => (float) $p->cost_price,
                    'stock'         => (int) $p->stock,
                ];
            });

        // Generate nomor PO / Faktur default: PO-YYYYMMDD-XXXX
        $datePrefix = date('Ymd');
        $countToday = Purchase::whereDate('created_at', today())->count() + 1;
        $defaultInvoice = 'PO-' . $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
        while (Purchase::where('invoice_number', $defaultInvoice)->exists()) {
            $countToday++;
            $defaultInvoice = 'PO-' . $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
        }

        return view('purchases.create', compact('suppliers', 'products', 'defaultInvoice'));
    }

    /**
     * Eksekusi penyimpanan pembelian, penambahan stok & update harga modal (FASE 5.1)
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_number'     => 'required|string|max:50|unique:purchases,invoice_number',
            'supplier_id'        => 'nullable|exists:suppliers,id',
            'purchase_date'      => 'required|date',
            'payment_status'     => 'required|in:paid,partial,unpaid',
            'paid_amount'        => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string|max:1000',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.cost_price' => 'required|numeric|min:0',
            'items.*.quantity'   => 'required|integer|min:1',
        ], [
            'invoice_number.required'     => 'Nomor faktur / PO wajib diisi.',
            'invoice_number.unique'       => 'Nomor faktur / PO ini sudah pernah digunakan.',
            'purchase_date.required'      => 'Tanggal pembelian wajib diisi.',
            'items.required'              => 'Minimal harus ada 1 barang dalam daftar kulakan.',
            'items.min'                   => 'Minimal harus ada 1 barang dalam daftar kulakan.',
            'items.*.product_id.required' => 'Pilihan produk tidak valid.',
            'items.*.cost_price.required' => 'Harga beli barang wajib diisi.',
            'items.*.cost_price.min'      => 'Harga beli tidak boleh negatif.',
            'items.*.quantity.required'   => 'Kuantitas beli wajib diisi.',
            'items.*.quantity.min'        => 'Kuantitas beli minimal 1.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Hitung Total Biaya Pembelian
                $totalAmount = 0;
                foreach ($request->items as $item) {
                    $totalAmount += (float) $item['cost_price'] * (int) $item['quantity'];
                }

                // 2. Hitung Nominal Bayar Sesuai Status Pembayaran
                $paidAmount = 0;
                if ($request->payment_status === 'paid') {
                    $paidAmount = $totalAmount;
                } elseif ($request->payment_status === 'partial') {
                    $paidAmount = min($totalAmount, (float) ($request->paid_amount ?? 0));
                } else {
                    $paidAmount = 0;
                }

                // 3. Simpan Record ke Tabel purchases
                $purchase = Purchase::create([
                    'invoice_number' => trim($request->invoice_number),
                    'supplier_id'    => $request->supplier_id ?: null,
                    'user_id'        => Auth::id(),
                    'total_amount'   => $totalAmount,
                    'paid_amount'    => $paidAmount,
                    'payment_status' => $request->payment_status,
                    'purchase_date'  => $request->purchase_date,
                    'notes'          => $request->notes,
                ]);

                // 4. Simpan Record ke purchase_items, Update Harga Modal, dan Tambah Stok Produk
                foreach ($request->items as $itemData) {
                    $qty = (int) $itemData['quantity'];
                    $costPrice = (float) $itemData['cost_price'];
                    $subtotal = $costPrice * $qty;

                    // Simpan item pembelian
                    $purchase->items()->create([
                        'product_id' => $itemData['product_id'],
                        'cost_price' => $costPrice,
                        'quantity'   => $qty,
                        'subtotal'   => $subtotal,
                    ]);

                    // Update produk: update harga modal (cost_price) dan tambah stok otomatis
                    $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);
                    $product->cost_price = $costPrice;
                    $product->save();
                    
                    // Tambah kuantitas stok produk
                    $product->increment('stock', $qty);
                }

                // 5. Catat Arus Kas Keluar (Cash Out) jika ada pembayaran
                if ($paidAmount > 0) {
                    $statusLabel = match ($request->payment_status) {
                        'paid'    => 'Lunas',
                        'partial' => 'Uang Muka / Sebagian',
                        default   => '',
                    };

                    CashFlow::create([
                        'user_id'        => Auth::id(),
                        'type'           => 'out',
                        'category'       => 'Pembelian',
                        'amount'         => $paidAmount,
                        'reference_type' => 'purchase',
                        'reference_id'   => $purchase->id,
                        'date'           => $request->purchase_date,
                        'notes'          => "Biaya kulakan / pembelian barang faktur {$purchase->invoice_number} ({$statusLabel})" . ($request->notes ? " - {$request->notes}" : ""),
                    ]);
                }

                return $purchase;
            });

            return redirect()->route('purchases.index')
                ->with('success', 'Pembelian berhasil disimpan! Stok produk otomatis bertambah dan harga modal telah diperbarui.');

        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan transaksi pembelian: ' . $e->getMessage());
        }
    }
}
