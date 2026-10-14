<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Tampilkan daftar seluruh kategori
     */
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        // Filter pencarian nama / slug / keterangan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query->latest()->paginate(10)->withQueryString();

        // Statistik kategori untuk metric cards
        $stats = [
            'total'  => Category::count(),
            'used'   => Category::has('products')->count(),
            'unused' => Category::doesntHave('products')->count(),
        ];

        return view('categories.index', compact('categories', 'stats'));
    }

    /**
     * Simpan kategori baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique'   => 'Nama kategori ini sudah terdaftar.',
            'name.max'      => 'Nama kategori maksimal 100 karakter.',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Pastikan slug unik
        $slugBase = $validated['slug'];
        $counter = 1;
        while (Category::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $slugBase . '-' . $counter++;
        }

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    /**
     * Perbarui data kategori
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:categories,name,' . $category->id],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique'   => 'Nama kategori ini sudah digunakan oleh kategori lain.',
            'name.max'      => 'Nama kategori maksimal 100 karakter.',
        ]);

        // Regenerate slug jika nama berubah
        if ($category->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);

            $slugBase = $validated['slug'];
            $counter = 1;
            while (Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->exists()) {
                $validated['slug'] = $slugBase . '-' . $counter++;
            }
        }

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Data kategori berhasil diperbarui.');
    }

    /**
     * Hapus kategori (dengan proteksi jika terpakai di produk)
     */
    public function destroy(Category $category)
    {
        // Proteksi: cegah hapus jika kategori sedang dipakai oleh produk
        if ($category->products()->exists()) {
            $count = $category->products()->count();
            return redirect()->route('categories.index')
                ->with('error', "Kategori \"{$category->name}\" tidak dapat dihapus karena masih digunakan oleh {$count} produk.");
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
