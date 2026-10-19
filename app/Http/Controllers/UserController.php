<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

        $users = $query->orderByRaw("CASE 
            WHEN email = 'ownertoko@email.com' THEN 1 
            WHEN email = 'admintoko@email.com' THEN 2 
            WHEN email = 'kasirtoko@email.com' THEN 3 
            WHEN email = 'gudangtoko@email.com' THEN 4 
            ELSE 5 END ASC")
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->withQueryString();

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
            'image'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['nullable'],
        ], [
            'name.required'     => 'Nama pengguna wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
            'role.required'     => 'Role hak akses wajib dipilih.',
            'image.image'       => 'File foto harus berupa gambar valid.',
            'image.mimes'       => 'Format foto harus berupa JPG, PNG, atau WEBP.',
            'image.max'         => 'Ukuran foto maksimal 2MB.',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('users', 'public');
        }

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => $validated['role'],
            'phone'     => $validated['phone'] ?? null,
            'image'     => $imagePath,
            'avatar'    => $imagePath,
            'is_active' => $request->has('is_active'),
        ]);

        ActivityLog::record('create', "Pengguna baru {$user->name} ({$user->email}, Role: {$user->role}) berhasil ditambahkan oleh " . Auth::user()->name, $user);

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
            'image'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['nullable'],
        ], [
            'name.required'  => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique'   => 'Email ini sudah digunakan oleh akun lain.',
            'role.required'  => 'Role hak akses wajib dipilih.',
            'image.image'    => 'File foto harus berupa gambar valid.',
            'image.mimes'    => 'Format foto harus berupa JPG, PNG, atau WEBP.',
            'image.max'      => 'Ukuran foto maksimal 2MB.',
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

        $userData = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'role'      => $validated['role'],
            'phone'     => $validated['phone'] ?? null,
            'is_active' => $isActive,
        ];

        // Hapus foto jika diminta
        if ($request->boolean('remove_image')) {
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $userData['image'] = null;
            $userData['avatar'] = null;
        }

        // Upload foto baru jika ada
        if ($request->hasFile('image')) {
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

        ActivityLog::record('update', "Data pengguna {$user->name} ({$user->email}) diperbarui oleh " . Auth::user()->name, $user);

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

        ActivityLog::record('update', "Password akun pengguna {$user->name} ({$user->email}) diperbarui oleh " . Auth::user()->name, $user);

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

        ActivityLog::record('update', "Status akun pengguna {$user->name} {$statusText} oleh " . Auth::user()->name, $user);

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

        $name = $user->name;
        $email = $user->email;

        if ($user->image && Storage::disk('public')->exists($user->image)) {
            Storage::disk('public')->delete($user->image);
        }
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        ActivityLog::record('delete', "Akun pengguna {$name} ({$email}) dihapus oleh " . Auth::user()->name);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
