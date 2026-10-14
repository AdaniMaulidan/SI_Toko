<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
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
     * Validasi proses pembayaran checkout modal kasir (FASE 4.2)
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

        // Validasi khusus metode Hutang (Hanya untuk pelanggan terdaftar & tanggal jatuh tempo valid)
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

        return response()->json([
            'success' => true,
            'message' => 'Validasi checkout modal berhasil.'
        ]);
    }
}
