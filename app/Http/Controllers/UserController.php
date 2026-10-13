<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter pencarian nama / email / no telp
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        // Statistik user untuk metric cards
        $stats = [
            'total'    => User::count(),
            'admin'    => User::where('role', User::ROLE_ADMIN)->count(),
            'cashier'  => User::where('role', User::ROLE_CASHIER)->count(),
            'staff'    => User::where('role', User::ROLE_STAFF)->count(),
            'active'   => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        return view('users.index', compact('users', 'stats'));
    }

    /**
     * Simpan pengguna baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:6'],
            'role'      => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_CASHIER, User::ROLE_STAFF])],
            'phone'     => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable'],
        ], [
            'name.required'     => 'Nama pengguna wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
            'role.required'     => 'Role hak akses wajib dipilih.',
        ]);

        User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => $validated['role'],
            'phone'     => $validated['phone'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('users.index')->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Perbarui data profil pengguna
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'      => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_CASHIER, User::ROLE_STAFF])],
            'phone'     => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable'],
        ], [
            'name.required'  => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique'   => 'Email ini sudah digunakan oleh akun lain.',
            'role.required'  => 'Role hak akses wajib dipilih.',
        ]);

        $isActive = $request->has('is_active');

        // Cegah admin menonaktifkan akunnya sendiri
        if (Auth::id() === $user->id && !$isActive) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        // Cegah admin mengubah role dirinya sendiri menjadi non-admin
        if (Auth::id() === $user->id && $validated['role'] !== User::ROLE_ADMIN) {
            return back()->with('error', 'Anda tidak dapat menurunkan role akun Anda sendiri.');
        }

        $user->update([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'role'      => $validated['role'],
            'phone'     => $validated['phone'] ?? null,
            'is_active' => $isActive,
        ]);

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Ganti password pengguna
     */
    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'new_password'              => ['required', 'string', 'min:6', 'confirmed'],
            'new_password_confirmation' => ['required', 'string'],
        ], [
            'new_password.required'  => 'Password baru wajib diisi.',
            'new_password.min'       => 'Password minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('users.index')->with('success', 'Password pengguna berhasil diperbarui.');
    }

    /**
     * Toggle status aktif / non-aktif pengguna
     */
    public function toggleStatus(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat mengubah status akun Anda sendiri.');
        }

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('users.index')->with('success', "Akun pengguna berhasil {$statusText}.");
    }

    /**
     * Hapus akun pengguna
     */
    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
