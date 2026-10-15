<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\ImportExportService;
use Illuminate\Http\Request;

class ImportExportController extends Controller
{
    protected ImportExportService $service;

    public function __construct(ImportExportService $service)
    {
        $this->service = $service;
    }

    /**
     * Tampilkan Halaman Import & Export
     */
    public function index()
    {
        $stats = [
            'total_products'  => Product::count(),
            'total_customers' => Customer::count(),
            'total_suppliers' => Supplier::count(),
        ];

        return view('import_export.index', compact('stats'));
    }

    /**
     * Proses Download / Export Data Master Toko
     */
    public function export(Request $request)
    {
        $request->validate([
            'export_type'   => 'required|in:products,customers,suppliers',
            'export_format' => 'required|in:xlsx,csv',
        ], [
            'export_type.required'   => 'Pilih jenis data yang ingin diekspor.',
            'export_type.in'         => 'Pilihan data tidak valid.',
            'export_format.required' => 'Pilih format file ekspor.',
            'export_format.in'       => 'Format file hanya mendukung Excel (.xlsx) atau CSV (.csv).',
        ]);

        $type   = $request->export_type;
        $format = $request->export_format;

        ActivityLog::record('export', "Data {$type} diekspor ke format {$format}.");

        return match ($type) {
            'products'  => $this->service->exportProducts($format),
            'customers' => $this->service->exportCustomers($format),
            'suppliers' => $this->service->exportSuppliers($format),
        };
    }

    /**
     * Unduh Template Excel untuk Migrasi / Import Data
     */
    public function downloadTemplate(Request $request)
    {
        $type = $request->query('type', 'products');
        if (!in_array($type, ['products', 'customers'])) {
            $type = 'products';
        }

        return $this->service->getTemplate($type);
    }

    /**
     * Proses Unggah / Import Data dari File Excel atau CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_type'     => 'required|in:products,customers',
            'import_file'     => 'required|file|max:10240|mimes:xlsx,xls,csv,txt',
            'update_existing' => 'nullable|boolean',
        ], [
            'import_type.required' => 'Pilih jenis data yang akan diimpor.',
            'import_type.in'       => 'Jenis data import tidak valid.',
            'import_file.required' => 'Silakan pilih berkas file yang akan diunggah.',
            'import_file.file'     => 'Berkas harus berupa file yang valid.',
            'import_file.max'      => 'Ukuran file maksimal adalah 10 MB.',
            'import_file.mimes'    => 'Format file yang didukung hanya Excel (.xlsx, .xls) atau CSV (.csv).',
        ]);

        $file = $request->file('import_file');
        $type = $request->import_type;
        $updateExisting = $request->boolean('update_existing', true);

        try {
            if ($type === 'products') {
                $result = $this->service->importProducts($file, $updateExisting);
                
                $message = "Proses import produk selesai! Berhasil menambah {$result['imported']} produk baru, memperbarui {$result['updated']} produk";
                if ($result['skipped'] > 0) {
                    $message .= ", dan melewati {$result['skipped']} data.";
                } else {
                    $message .= ".";
                }

                ActivityLog::record('import', "Import data produk selesai: {$result['imported']} produk baru, {$result['updated']} diperbarui.");

                return back()
                    ->with('success', $message)
                    ->with('import_summary', $result);
            } else {
                $result = $this->service->importCustomers($file);

                $message = "Proses import pelanggan selesai! Berhasil menambah {$result['imported']} data pelanggan";
                if ($result['skipped'] > 0) {
                    $message .= ", dan melewati {$result['skipped']} data yang sudah ada.";
                } else {
                    $message .= ".";
                }

                ActivityLog::record('import', "Import data pelanggan selesai: {$result['imported']} pelanggan baru ditambahkan.");

                return back()
                    ->with('success', $message)
                    ->with('import_summary', $result);
            }
        } catch (\Throwable $e) {
            return back()
                ->with('error', 'Terjadi kesalahan saat memproses file import: ' . $e->getMessage());
        }
    }
}
