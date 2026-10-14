<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Tampilkan daftar seluruh satuan
     */
    public function index(Request $request)
    {
        $query = Unit::withCount('products');

        // Filter pencarian nama / singkatan / keterangan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $units = $query->latest()->paginate(10)->withQueryString();

        // Statistik satuan untuk metric cards
        $stats = [
            'total'    => Unit::count(),
            'used'     => Unit::has('products')->count(),
            'unused'   => Unit::doesntHave('products')->count(),
        ];

        return view('units.index', compact('units', 'stats'));
    }

    /**
     * Simpan satuan baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:units,name'],
            'short_name'  => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama satuan wajib diisi.',
            'name.unique'   => 'Nama satuan ini sudah terdaftar.',
            'name.max'      => 'Nama satuan maksimal 100 karakter.',
        ]);

        Unit::create($validated);

        return redirect()->route('units.index')->with('success', 'Satuan baru berhasil ditambahkan.');
    }

    /**
     * Perbarui data satuan
     */
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:units,name,' . $unit->id],
            'short_name'  => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama satuan wajib diisi.',
            'name.unique'   => 'Nama satuan ini sudah digunakan oleh satuan lain.',
            'name.max'      => 'Nama satuan maksimal 100 karakter.',
        ]);

        $unit->update($validated);

        return redirect()->route('units.index')->with('success', 'Data satuan berhasil diperbarui.');
    }

    /**
     * Hapus satuan (dengan proteksi jika terpakai di produk)
     */
    public function destroy(Unit $unit)
    {
        // Proteksi: cegah hapus jika satuan sedang dipakai oleh produk
        if ($unit->products()->exists()) {
            $count = $unit->products()->count();
            return redirect()->route('units.index')
                ->with('error', "Satuan \"{$unit->name}\" tidak dapat dihapus karena masih digunakan oleh {$count} produk.");
        }

        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Satuan berhasil dihapus.');
    }
}
