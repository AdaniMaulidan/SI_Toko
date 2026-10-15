<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Tampilkan halaman formulir pengaturan toko (FASE 9.1)
     */
    public function index(): View
    {
        $setting = StoreSetting::getSettings();

        return view('settings.index', compact('setting'));
    }

    /**
     * Perbarui data pengaturan dan profil toko (FASE 9.1)
     */
    public function update(Request $request): RedirectResponse
    {
        $setting = StoreSetting::getSettings();

        $validated = $request->validate([
            'store_name'      => 'required|string|max:150',
            'address'         => 'nullable|string|max:1000',
            'phone'           => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:100',
            'header_receipt'  => 'nullable|string|max:255',
            'footer_receipt'  => 'nullable|string|max:255',
            'tax_percentage'  => 'nullable|numeric|min:0|max:100',
            'currency_symbol' => 'nullable|string|max:10',
            'logo'            => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ], [
            'store_name.required' => 'Nama toko wajib diisi.',
            'store_name.max'      => 'Nama toko maksimal 150 karakter.',
            'logo.image'          => 'File logo harus berupa gambar.',
            'logo.mimes'          => 'Format logo yang didukung: JPG, PNG, WEBP, SVG.',
            'logo.max'            => 'Ukuran logo maksimal 2MB.',
            'tax_percentage.numeric' => 'Persentase pajak harus berupa angka.',
        ]);

        // Upload Logo Toko jika ada file baru yang diunggah
        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($setting->logo && Storage::disk('public')->exists($setting->logo)) {
                Storage::disk('public')->delete($setting->logo);
            }

            $validated['logo'] = $request->file('logo')->store('store', 'public');
        }

        // Set default tax percentage jika kosong
        $validated['tax_percentage'] = $request->filled('tax_percentage') ? (float) $validated['tax_percentage'] : 0;
        $validated['currency_symbol'] = $request->input('currency_symbol', 'Rp') ?: 'Rp';

        $setting->update($validated);

        ActivityLog::record('settings', "Pengaturan profil toko ({$setting->store_name}) diperbarui.", $setting);

        return redirect()->route('settings.index')->with('success', 'Pengaturan profil toko dan struk berhasil diperbarui.');
    }

    /**
     * Hapus logo toko yang tersimpan
     */
    public function removeLogo(): RedirectResponse
    {
        $setting = StoreSetting::getSettings();

        if ($setting->logo && Storage::disk('public')->exists($setting->logo)) {
            Storage::disk('public')->delete($setting->logo);
        }

        $setting->update(['logo' => null]);

        ActivityLog::record('settings', "Logo toko dihapus.", $setting);

        return redirect()->route('settings.index')->with('success', 'Logo toko berhasil dihapus.');
    }
}
