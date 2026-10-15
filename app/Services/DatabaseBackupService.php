<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class DatabaseBackupService
{
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (!File::isDirectory($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    /**
     * Dapatkan path direktori penyimpanan file backup
     */
    public function getBackupDirectory(): string
    {
        return $this->backupDir;
    }

    /**
     * Dapatkan ringkasan statistik database saat ini
     */
    public function getDatabaseStats(): array
    {
        $connection = DB::connection();
        $driver     = $connection->getDriverName();
        $dbName     = $connection->getDatabaseName();
        $tables     = $this->getTableNames();

        $totalRecords = 0;
        foreach ($tables as $table) {
            $totalRecords += DB::table($table)->count();
        }

        return [
            'driver'        => $driver,
            'database_name' => $dbName,
            'total_tables'  => count($tables),
            'total_records' => $totalRecords,
            'tables'        => $tables,
        ];
    }

    /**
     * Ambil daftar nama tabel yang relevan dalam database aktif
     */
    public function getTableNames(): array
    {
        $connection = DB::connection();
        $driver     = $connection->getDriverName();

        if ($driver === 'sqlite') {
            return array_column(
                DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"),
                'name'
            );
        }

        // MySQL / MariaDB
        $databaseName = $connection->getDatabaseName();
        $results = DB::select(
            "SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = ? AND TABLE_TYPE = 'BASE TABLE'",
            [$databaseName]
        );

        return array_column($results, 'TABLE_NAME');
    }

    /**
     * Generate isi skrip dump SQL database lengkap
     */
    public function dumpSql(): string
    {
        $connection = DB::connection();
        $driver     = $connection->getDriverName();
        $dbName     = $connection->getDatabaseName();
        $pdo        = $connection->getPdo();
        $timestamp  = Carbon::now()->toIso8601String();
        $appName    = config('app.name', 'SI Toko');

        $out = "-- ============================================================\n";
        $out .= "-- {$appName} - Database SQL Dump Backup\n";
        $out .= "-- Driver       : {$driver}\n";
        $out .= "-- Database     : {$dbName}\n";
        $out .= "-- Generated At : {$timestamp}\n";
        $out .= "-- ============================================================\n\n";

        if ($driver === 'sqlite') {
            $out .= "PRAGMA foreign_keys = OFF;\n\n";
        } else {
            $out .= "SET FOREIGN_KEY_CHECKS = 0;\n";
            $out .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
            $out .= "SET time_zone = '+00:00';\n\n";
        }

        $tables = $this->getTableNames();

        foreach ($tables as $table) {
            $out .= "-- ------------------------------------------------------------\n";
            $out .= "-- Struktur Tabel `{$table}`\n";
            $out .= "-- ------------------------------------------------------------\n";

            if ($driver === 'sqlite') {
                $out .= "DROP TABLE IF EXISTS \"{$table}\";\n";
                $row = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$table]);
                if ($row && !empty($row->sql)) {
                    $out .= $row->sql . ";\n\n";
                }
            } else {
                $out .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $createRow = DB::selectOne("SHOW CREATE TABLE `{$table}`");
                if ($createRow) {
                    $createArr = (array) $createRow;
                    $createSql = $createArr['Create Table'] ?? '';
                    $out .= $createSql . ";\n\n";
                }
            }

            // Dump data baris
            $rows = DB::table($table)->get();
            if ($rows->isNotEmpty()) {
                $out .= "-- Data Tabel `{$table}` (" . $rows->count() . " baris)\n";

                $firstRow    = (array) $rows->first();
                $columns     = array_keys($firstRow);
                $escapedCols = array_map(function ($col) use ($driver) {
                    return $driver === 'sqlite' ? "\"{$col}\"" : "`{$col}`";
                }, $columns);
                $columnsList = implode(', ', $escapedCols);

                $tableWrapper = $driver === 'sqlite' ? "\"{$table}\"" : "`{$table}`";

                foreach ($rows->chunk(100) as $chunk) {
                    $valuesSql = [];
                    foreach ($chunk as $r) {
                        $rowArr    = (array) $r;
                        $valTokens = [];
                        foreach ($columns as $c) {
                            $val = $rowArr[$c] ?? null;
                            if ($val === null) {
                                $valTokens[] = 'NULL';
                            } elseif (is_int($val) || is_float($val)) {
                                $valTokens[] = $val;
                            } else {
                                $valTokens[] = $pdo->quote((string) $val);
                            }
                        }
                        $valuesSql[] = '(' . implode(', ', $valTokens) . ')';
                    }

                    $out .= "INSERT INTO {$tableWrapper} ({$columnsList}) VALUES\n" . implode(",\n", $valuesSql) . ";\n";
                }
                $out .= "\n";
            }
        }

        if ($driver === 'sqlite') {
            $out .= "PRAGMA foreign_keys = ON;\n";
        } else {
            $out .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        }

        return $out;
    }

    /**
     * Buat dan simpan file backup SQL ke direktori lokal
     */
    public function createBackupFile(?string $customFilename = null): string
    {
        $sqlContent = $this->dumpSql();

        if (!$customFilename) {
            $timestamp = Carbon::now()->format('Y-m-d_His');
            $customFilename = "backup-sitoko-{$timestamp}.sql";
        }

        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $customFilename;
        File::put($filePath, $sqlContent);

        return $filePath;
    }

    /**
     * Ambil daftar file backup yang tersimpan di disk
     */
    public function getBackupFiles(): array
    {
        if (!File::isDirectory($this->backupDir)) {
            return [];
        }

        $files = File::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'sql') {
                continue;
            }

            $backups[] = [
                'filename'   => $file->getFilename(),
                'size_bytes' => $file->getSize(),
                'size_human' => $this->formatBytes($file->getSize()),
                'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                'path'       => $file->getRealPath(),
            ];
        }

        // Urutkan dari yang terbaru
        usort($backups, fn($a, $b) => $b['created_at']->timestamp <=> $a['created_at']->timestamp);

        return $backups;
    }

    /**
     * Hapus file backup dari direktori
     */
    public function deleteBackup(string $filename): bool
    {
        $cleanFilename = basename($filename);
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $cleanFilename;

        if (File::exists($filePath)) {
            return File::delete($filePath);
        }

        return false;
    }

    /**
     * Pulihkan database dari isi script SQL
     */
    public function restoreFromSql(string $sqlContent): void
    {
        // Jalankan perintah multi-statement SQL
        DB::unprepared($sqlContent);
    }

    /**
     * Pulihkan database dari file SQL yang tersimpan di disk
     */
    public function restoreFromFile(string $filePath): void
    {
        if (!File::exists($filePath)) {
            throw new \InvalidArgumentException("File backup tidak ditemukan: {$filePath}");
        }

        $sqlContent = File::get($filePath);
        $this->restoreFromSql($sqlContent);
    }

    /**
     * Helper pemformat ukuran byte ke string human readable
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
