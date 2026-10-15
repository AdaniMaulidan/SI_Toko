<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    protected DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Tampilkan halaman Backup & Restore Database (FASE 9.2)
     */
    public function index(): View
    {
        $dbStats = $this->backupService->getDatabaseStats();
        $backups = $this->backupService->getBackupFiles();

        return view('backup.index', compact('dbStats', 'backups'));
    }

    /**
     * Unduh backup database .sql toko sekali klik (FASE 9.2)
     */
    public function download(): BinaryFileResponse
    {
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename  = "backup-sitoko-{$timestamp}.sql";

        // Buat file backup di storage lokal agar tersimpan di riwayat juga
        $filePath = $this->backupService->createBackupFile($filename);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Buat backup snapshot baru di server
     */
    public function store(): RedirectResponse
    {
        $filePath = $this->backupService->createBackupFile();
        $filename = basename($filePath);

        return redirect()->route('backup.index')->with('success', "Backup database berhasil dibuat: {$filename}");
    }

    /**
     * Unduh file backup yang sudah tersimpan di server
     */
    public function downloadFile(string $filename): BinaryFileResponse
    {
        $cleanFilename = basename($filename);
        $filePath = $this->backupService->getBackupDirectory() . DIRECTORY_SEPARATOR . $cleanFilename;

        if (!File::exists($filePath)) {
            abort(404, 'File backup tidak ditemukan.');
        }

        return response()->download($filePath, $cleanFilename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Hapus file backup dari server
     */
    public function destroy(string $filename): RedirectResponse
    {
        $cleanFilename = basename($filename);
        $deleted = $this->backupService->deleteBackup($cleanFilename);

        if (!$deleted) {
            return redirect()->route('backup.index')->with('error', 'File backup tidak ditemukan atau gagal dihapus.');
        }

        return redirect()->route('backup.index')->with('success', "File backup {$cleanFilename} berhasil dihapus.");
    }

    /**
     * Pulihkan database dari file .sql yang diunggah atau dipilih
     */
    public function restore(Request $request): RedirectResponse
    {
        // Pilihan 1: Restore dari file yang diunggah
        if ($request->hasFile('file_restore')) {
            $request->validate([
                'file_restore' => 'required|file|max:51200', // max 50MB
            ], [
                'file_restore.required' => 'Pilih file backup .sql terlebih dahulu.',
                'file_restore.file'     => 'File tidak valid.',
                'file_restore.max'      => 'Ukuran file maksimal 50MB.',
            ]);

            $file = $request->file('file_restore');
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension !== 'sql') {
                return redirect()->route('backup.index')->with('error', 'Format file harus berupa skrip database berekstensi .sql');
            }

            try {
                $sqlContent = File::get($file->getRealPath());
                $this->backupService->restoreFromSql($sqlContent);

                return redirect()->route('backup.index')->with('success', 'Database toko berhasil dipulihkan (restore) dari file yang diunggah.');
            } catch (\Throwable $e) {
                return redirect()->route('backup.index')->with('error', 'Gagal memulihkan database: ' . $e->getMessage());
            }
        }

        // Pilihan 2: Restore dari riwayat file yang tersimpan di server
        if ($request->filled('backup_filename')) {
            $filename = basename($request->input('backup_filename'));
            $filePath = $this->backupService->getBackupDirectory() . DIRECTORY_SEPARATOR . $filename;

            try {
                $this->backupService->restoreFromFile($filePath);

                return redirect()->route('backup.index')->with('success', "Database toko berhasil dipulihkan dari file {$filename}.");
            } catch (\Throwable $e) {
                return redirect()->route('backup.index')->with('error', 'Gagal memulihkan database: ' . $e->getMessage());
            }
        }

        return redirect()->route('backup.index')->with('error', 'Silakan pilih file backup .sql yang valid untuk dipulihkan.');
    }
}
