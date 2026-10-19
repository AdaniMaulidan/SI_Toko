<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil pengguna yang sedang login
     */
    public function index(): View
    {
        $user = Auth::user();

        return view('profile.index', compact('user'));
    }

    /**
     * Perbarui data profil akun (nama, telepon, foto profil)
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'image.image'   => 'File foto profil harus berupa gambar valid.',
            'image.mimes'   => 'Format foto profil harus JPG, PNG, atau WEBP.',
            'image.max'     => 'Ukuran foto profil maksimal 2MB.',
        ]);

        $userData = [
            'name'  => $validated['name'],
            'phone' => $validated['phone'] ?? null,
        ];

        if ($request->hasFile('image')) {
            // Hapus file foto lama jika ada
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('image')->store('users', 'public');
            $userData['image'] = $path;
            $userData['avatar'] = $path;
        }

        $user->update($userData);

        ActivityLog::record('update', "Pengguna {$user->name} memperbarui data profil akun & foto profil.", $user);

        return redirect()->route('profile.index')->with('success', 'Profil akun dan foto profil Anda berhasil diperbarui.');
    }

    /**
     * Hapus foto profil pengguna saat ini
     */
    public function removeImage(): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->image && Storage::disk('public')->exists($user->image)) {
            Storage::disk('public')->delete($user->image);
        }
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update([
            'image'  => null,
            'avatar' => null,
        ]);

        ActivityLog::record('update', "Pengguna {$user->name} menghapus foto profil akun.", $user);

        return redirect()->route('profile.index')->with('success', 'Foto profil berhasil dihapus.');
    }

    /**
     * Perbarui kata sandi akun pengguna sendiri
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'current_password'          => ['required', 'current_password'],
            'new_password'              => ['required', 'string', 'min:6', 'confirmed'],
            'new_password_confirmation' => ['required', 'string'],
        ], [
            'current_password.required'         => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini yang Anda masukkan tidak sesuai.',
            'new_password.required'             => 'Password baru wajib diisi.',
            'new_password.min'                  => 'Password baru minimal 6 karakter.',
            'new_password.confirmed'            => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        ActivityLog::record('update', "Pengguna {$user->name} berhasil mengubah password akun pribadinya.", $user);

        return redirect()->route('profile.index')->with('success', 'Password akun Anda berhasil diperbarui.');
    }
}
