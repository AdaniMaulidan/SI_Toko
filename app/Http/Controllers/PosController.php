<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;

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
}
