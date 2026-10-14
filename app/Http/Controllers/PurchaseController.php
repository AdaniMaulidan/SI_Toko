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
     * Tampilkan daftar riwayat pembelian produk dengan statistik dan filter lengkap (FASE 5.2)
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'items.product.unit', 'user']);

        // 1. Filter Supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // 2. Filter Status Pembayaran (paid, partial, unpaid)
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // 3. Filter Rentang Tanggal Pembelian
        if ($request->filled('start_date')) {
            $query->whereDate('purchase_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('purchase_date', '<=', $request->end_date);
        }

        // 4. Filter Kata Kunci (Nomor Faktur / Catatan)
        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('invoice_number', 'like', "%{$keyword}%")
                  ->orWhere('notes', 'like', "%{$keyword}%")
                  ->orWhereHas('supplier', function ($sq) use ($keyword) {
                      $sq->where('name', 'like', "%{$keyword}%")
                         ->orWhere('phone', 'like', "%{$keyword}%");
                  });
            });
        }

        // Ringkasan Statistik Berdasarkan Filter Aktif
        $stats = [
            'total_purchases' => (float) (clone $query)->sum('total_amount'),
            'total_invoices'  => (clone $query)->count(),
            'total_paid'      => (float) (clone $query)->sum('paid_amount'),
            'total_debt'      => (float) max(0, (clone $query)->sum('total_amount') - (clone $query)->sum('paid_amount')),
        ];

        $purchases = $query->latest('purchase_date')->latest('id')->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers', 'stats'));
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

    /**
     * Tampilkan halaman detail lengkap faktur pembelian (FASE 5.2)
     */
    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'items.product.unit', 'items.product.category', 'user'])->findOrFail($id);

        return view('purchases.show', compact('purchase'));
    }

    /**
     * Endpoint API JSON untuk memuat data modal detail pembelian secara realtime (FASE 5.2)
     */
    public function detail($id)
    {
        $purchase = Purchase::with(['supplier', 'items.product.unit', 'user'])->findOrFail($id);

        $remainingDebt = max(0, (float) ($purchase->total_amount - $purchase->paid_amount));

        return response()->json([
            'success'  => true,
            'purchase' => [
                'id'               => $purchase->id,
                'invoice_number'   => $purchase->invoice_number,
                'purchase_date'    => $purchase->purchase_date->format('d/m/Y'),
                'created_at'       => $purchase->created_at->format('d/m/Y H:i'),
                'creator_name'     => $purchase->user?->name ?? 'Staff Gudang',
                'supplier_name'    => $purchase->supplier?->name ?? 'Supplier Umum / Tanpa Nama',
                'supplier_contact' => $purchase->supplier?->contact_person ?? '-',
                'supplier_phone'   => $purchase->supplier?->phone ?? '-',
                'supplier_address' => $purchase->supplier?->address ?? '-',
                'payment_status'   => $purchase->payment_status,
                'total_amount'     => (float) $purchase->total_amount,
                'paid_amount'      => (float) $purchase->paid_amount,
                'remaining_debt'   => $remainingDebt,
                'notes'            => $purchase->notes,
                'items_count'      => $purchase->items->count(),
                'total_qty'        => (int) $purchase->items->sum('quantity'),
                'items'            => $purchase->items->map(function ($it) {
                    return [
                        'product_name' => $it->product?->name ?? 'Barang',
                        'barcode'      => $it->product?->barcode ?? '-',
                        'unit'         => $it->product?->unit?->symbol ?? 'Pcs',
                        'cost_price'   => (float) $it->cost_price,
                        'quantity'     => (int) $it->quantity,
                        'subtotal'     => (float) $it->subtotal,
                    ];
                }),
            ]
        ]);
    }
}
