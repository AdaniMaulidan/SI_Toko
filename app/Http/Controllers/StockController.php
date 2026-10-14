<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Tampilkan halaman status dan manajemen stok saat ini (FASE 5)
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'unit']);

        // 1. Filter Kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 2. Filter Status Stok
        if ($request->filled('status')) {
            if ($request->status === 'habis') {
                $query->where('stock', '<=', 0);
            } elseif ($request->status === 'menipis') {
                $query->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock');
            } elseif ($request->status === 'aman') {
                $query->whereColumn('stock', '>', 'min_stock');
            }
        }

        // 3. Pencarian Nama atau Barcode
        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('barcode', 'like', "%{$keyword}%");
            });
        }

        // Hitung Ringkasan Statistik Stok
        $stats = [
            'total_items' => Product::count(),
            'safe_stock'  => Product::whereColumn('stock', '>', 'min_stock')->count(),
            'low_stock'   => Product::where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock')->count(),
            'out_of_stock'=> Product::where('stock', '<=', 0)->count(),
        ];

        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('stock.index', compact('products', 'categories', 'stats'));
    }

    /**
     * Form penyesuaian stok / stock opname (FASE 5.3)
     */
    public function adjustment(Request $request)
    {
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
                    'stock'         => (int) $p->stock,
                ];
            });

        // Generate Kode Opname Otomatis: ADJ-YYYYMMDD-XXXX
        $datePrefix = date('Ymd');
        $countToday = StockAdjustment::whereDate('created_at', today())->count() + 1;
        $defaultCode = 'ADJ-' . $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
        while (StockAdjustment::where('code', $defaultCode)->exists()) {
            $countToday++;
            $defaultCode = 'ADJ-' . $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
        }

        // Riwayat Penyesuaian Terakhir (10 Data Terbaru)
        $recentAdjustments = StockAdjustment::with(['user', 'items.product.unit'])
            ->latest('id')
            ->limit(10)
            ->get();

        // Parameter pra-pilih produk jika datang dari tombol di tabel stok
        $preselectedProductId = $request->get('product_id');

        return view('stock.adjustment', compact('products', 'defaultCode', 'recentAdjustments', 'preselectedProductId'));
    }

    /**
     * Simpan penyesuaian stok, catat selisih & sinkronisasi stok master data otomatis (FASE 5.3)
     */
    public function storeAdjustment(Request $request)
    {
        $request->validate([
            'code'                   => 'required|string|max:50|unique:stock_adjustments,code',
            'date'                   => 'required|date',
            'type'                   => 'required|in:opname,in,out',
            'reason_category'        => 'required|string',
            'notes'                  => 'nullable|string|max:1000',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.actual_stock'   => 'required|integer|min:0',
        ], [
            'code.required'                 => 'Kode penyesuaian stok wajib diisi.',
            'code.unique'                   => 'Kode penyesuaian ini sudah pernah digunakan.',
            'date.required'                 => 'Tanggal penyesuaian wajib diisi.',
            'reason_category.required'      => 'Kategori alasan penyesuaian wajib dipilih.',
            'items.required'                => 'Minimal harus ada 1 barang yang disesuaikan stoknya.',
            'items.min'                     => 'Minimal harus ada 1 barang yang disesuaikan stoknya.',
            'items.*.product_id.required'   => 'Pilihan produk tidak valid.',
            'items.*.actual_stock.required' => 'Stok fisik aktual wajib diisi.',
            'items.*.actual_stock.min'      => 'Stok fisik tidak boleh bernilai negatif.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Rangkum catatan lengkap (kategori alasan + catatan tambahan)
                $combinedNotes = $request->reason_category;
                if ($request->filled('notes')) {
                    $combinedNotes .= ' - ' . trim($request->notes);
                }

                // 1. Simpan Header Penyesuaian Stok
                $adjustment = StockAdjustment::create([
                    'code'    => trim($request->code),
                    'user_id' => Auth::id(),
                    'date'    => $request->date,
                    'type'    => $request->type,
                    'notes'   => $combinedNotes,
                ]);

                // 2. Simpan Setiap Item, Hitung Selisih & Sinkronisasi Stok Produk Otomatis
                foreach ($request->items as $itemData) {
                    $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);
                    
                    $previousStock = (int) $product->stock;
                    $actualStock = (int) $itemData['actual_stock'];
                    $difference = $actualStock - $previousStock;

                    // Simpan rincian selisih ke tabel stock_adjustment_items
                    $adjustment->items()->create([
                        'product_id'     => $product->id,
                        'previous_stock' => $previousStock,
                        'actual_stock'   => $actualStock,
                        'difference'     => $difference,
                    ]);

                    // Sinkronisasi stok master data produk agar sama persis dengan stok fisik
                    $product->stock = $actualStock;
                    $product->save();
                }

                return $adjustment;
            });

            return redirect()->route('stock.adjustment')
                ->with('success', 'Penyesuaian stok berhasil disimpan! Stok master data produk telah disinkronkan secara otomatis.');

        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memproses penyesuaian stok: ' . $e->getMessage());
        }
    }
}
