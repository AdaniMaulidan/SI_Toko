<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Tampilkan Riwayat Aktivitas & Audit Trail Sistem (FASE 10.2)
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        // Filter Tanggal
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter User / Pengguna
        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }

        // Filter Jenis Aksi / Aktivitas
        if ($request->filled('action') && $request->action !== 'all') {
            $query->where('action', strtolower($request->action));
        }

        // Filter Pencarian Teks
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $activities = $query->latest('id')->paginate(15)->withQueryString();

        // Statistik Metrik Aktivitas Hari Ini
        $today = Carbon::today();
        $stats = [
            'total_today'       => ActivityLog::whereDate('created_at', $today)->count(),
            'logins_today'      => ActivityLog::whereDate('created_at', $today)->where('action', 'login')->count(),
            'deletions_today'   => ActivityLog::whereDate('created_at', $today)->where('action', 'delete')->count(),
            'adjustments_today' => ActivityLog::whereDate('created_at', $today)->whereIn('action', ['adjustment', 'settings', 'update', 'restore'])->count(),
        ];

        // Daftar User untuk Filter
        $users = User::orderBy('name')->get(['id', 'name', 'role']);

        return view('activity.index', compact('activities', 'stats', 'users'));
    }
}
