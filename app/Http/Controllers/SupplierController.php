<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Tampilkan daftar supplier mitra toko
     */
    public function index(Request $request)
    {
        $query = Supplier::withCount('purchases');

        // Filter pencarian nama, PIC, telepon, email, alamat
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->latest()->paginate(15)->withQueryString();

        // Metrik statistik supplier
        $stats = [
            'total'               => Supplier::count(),
            'active_partners'     => Supplier::has('purchases')->count(),
            'total_purchases_num' => Purchase::count(),
            'total_spent'         => Purchase::sum('total_amount'),
        ];

        return view('suppliers.index', compact('suppliers', 'stats'));
    }

    /**
     * Simpan data supplier baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:100'],
            'address'        => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama supplier/perusahaan wajib diisi.',
            'email.email'   => 'Format alamat email tidak valid.',
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier baru berhasil ditambahkan.');
    }

    /**
     * Perbarui data supplier
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:100'],
            'address'        => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama supplier/perusahaan wajib diisi.',
            'email.email'   => 'Format alamat email tidak valid.',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')->with('success', 'Data supplier berhasil diperbarui.');
    }

    /**
     * Hapus data supplier (dengan proteksi integritas data)
     */
    public function destroy(Supplier $supplier)
    {
        // Proteksi: cegah hapus jika supplier sudah memiliki riwayat faktur pembelian
        if ($supplier->purchases()->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', "Supplier \"{$supplier->name}\" tidak dapat dihapus karena sudah memiliki riwayat faktur pengadaan/pembelian barang. Anda dapat mengubah datanya.");
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Data supplier berhasil dihapus.');
    }
}
