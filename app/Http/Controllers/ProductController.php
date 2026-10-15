<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Tampilkan daftar seluruh produk
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'unit']);

        // Filter pencarian nama / barcode
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter kategori
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter status stok
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    $query->lowStock()->where('stock', '>', 0);
                    break;
                case 'empty':
                    $query->where('stock', 0);
                    break;
                case 'safe':
                    $query->whereColumn('stock', '>', 'min_stock');
                    break;
            }
        }

        // Filter status aktif
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        // Statistik produk untuk metric cards
        $stats = [
            'total'     => Product::count(),
            'active'    => Product::where('is_active', true)->count(),
            'low_stock' => Product::lowStock()->where('stock', '>', 0)->count(),
            'out_stock' => Product::where('stock', 0)->count(),
        ];

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'stats', 'categories'));
    }

    /**
     * Tampilkan form tambah produk
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('products.create', compact('categories', 'units'));
    }

    /**
     * Simpan produk baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'barcode'       => ['nullable', 'string', 'max:100', 'unique:products,barcode'],
            'name'          => ['required', 'string', 'max:200'],
            'category_id'   => ['required', 'exists:categories,id'],
            'unit_id'       => ['required', 'exists:units,id'],
            'cost_price'    => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock'         => ['nullable', 'integer', 'min:0'],
            'min_stock'     => ['nullable', 'integer', 'min:0'],
            'image'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'description'   => ['nullable', 'string', 'max:500'],
            'is_active'     => ['nullable'],
        ], [
            'name.required'          => 'Nama produk wajib diisi.',
            'category_id.required'   => 'Kategori wajib dipilih.',
            'category_id.exists'     => 'Kategori yang dipilih tidak valid.',
            'unit_id.required'       => 'Satuan wajib dipilih.',
            'unit_id.exists'         => 'Satuan yang dipilih tidak valid.',
            'cost_price.required'    => 'Harga beli wajib diisi.',
            'cost_price.min'         => 'Harga beli tidak boleh negatif.',
            'selling_price.required' => 'Harga jual wajib diisi.',
            'selling_price.min'      => 'Harga jual tidak boleh negatif.',
            'barcode.unique'         => 'Barcode ini sudah digunakan oleh produk lain.',
            'image.max'              => 'Ukuran gambar maksimal 2MB.',
            'image.mimes'            => 'Format gambar harus JPG, JPEG, PNG, atau WebP.',
        ]);

        // Auto-generate barcode jika kosong
        if (empty($validated['barcode'])) {
            $validated['barcode'] = $this->generateBarcode();
        }

        // Handle upload gambar
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['stock']     = $validated['stock'] ?? 0;
        $validated['min_stock'] = $validated['min_stock'] ?? 5;
        $validated['is_active'] = $request->has('is_active');

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Produk baru berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail produk
     */
    public function show(Product $product)
    {
        $product->load(['category', 'unit']);

        return view('products.show', compact('product'));
    }

    /**
     * Tampilkan form edit produk
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'units'));
    }

    /**
     * Perbarui data produk
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'barcode'       => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->ignore($product->id)],
            'name'          => ['required', 'string', 'max:200'],
            'category_id'   => ['required', 'exists:categories,id'],
            'unit_id'       => ['required', 'exists:units,id'],
            'cost_price'    => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'min_stock'     => ['nullable', 'integer', 'min:0'],
            'image'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'description'   => ['nullable', 'string', 'max:500'],
            'is_active'     => ['nullable'],
        ], [
            'name.required'          => 'Nama produk wajib diisi.',
            'category_id.required'   => 'Kategori wajib dipilih.',
            'unit_id.required'       => 'Satuan wajib dipilih.',
            'cost_price.required'    => 'Harga beli wajib diisi.',
            'selling_price.required' => 'Harga jual wajib diisi.',
            'barcode.unique'         => 'Barcode ini sudah digunakan oleh produk lain.',
            'image.max'              => 'Ukuran gambar maksimal 2MB.',
        ]);

        // Handle upload gambar baru
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Handle hapus gambar
        if ($request->has('remove_image') && !$request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = null;
        }

        $validated['min_stock'] = $validated['min_stock'] ?? 5;
        $validated['is_active'] = $request->has('is_active');

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Data produk berhasil diperbarui.');
    }

    /**
     * Hapus produk (dengan proteksi jika terpakai di transaksi)
     */
    public function destroy(Product $product)
    {
        // Proteksi: cegah hapus jika produk pernah masuk transaksi penjualan
        if ($product->saleItems()->exists()) {
            return redirect()->route('products.index')
                ->with('error', "Produk \"{$product->name}\" tidak dapat dihapus karena sudah memiliki riwayat transaksi penjualan.");
        }

        // Proteksi: cegah hapus jika produk pernah masuk pembelian
        if ($product->purchaseItems()->exists()) {
            return redirect()->route('products.index')
                ->with('error', "Produk \"{$product->name}\" tidak dapat dihapus karena sudah memiliki riwayat pembelian.");
        }

        // Hapus gambar jika ada
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $productName = $product->name;
        $barcode = $product->barcode;

        $product->delete();

        ActivityLog::record('delete', "Produk \"{$productName}\" (Barcode: {$barcode}) dihapus dari master produk.");

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Generate barcode otomatis unik
     */
    private function generateBarcode(): string
    {
        do {
            // Format: PRD + timestamp 6 digit + random 4 digit angka
            $barcode = 'PRD' . date('ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Product::where('barcode', $barcode)->exists());

        return $barcode;
    }
}
