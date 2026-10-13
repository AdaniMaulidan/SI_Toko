<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Tampilkan form login
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Proses autentikasi login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        // Cek login via Auth
        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // Cek apakah akun aktif
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withInput($request->only('email', 'remember'))
                    ->withErrors(['email' => 'Akun Anda dinonaktifkan. Silakan hubungi Administrator.']);
            }

            $request->session()->regenerate();

            // Redirect sesuai role jika kasir ingin langsung ke POS atau dashboard
            if ($user->isCashier()) {
                return redirect()->intended(route('kasir.index'));
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'Kombinasi email dan password tidak sesuai.']);
    }

    /**
     * Proses logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda berhasil keluar dari sistem.');
    }
}
