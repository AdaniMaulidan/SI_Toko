<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportExportService
{
    /**
     * Ekspor Data Produk ke format Excel / CSV
     */
    public function exportProducts(string $format = 'xlsx'): StreamedResponse
    {
        $products = Product::with(['category', 'unit'])->orderBy('name')->get();

        $headers = [
            'No',
            'Barcode',
            'Nama Produk',
            'Kategori',
            'Satuan',
            'Harga Modal (Beli)',
            'Harga Jual',
            'Stok',
            'Stok Minimum',
            'Status',
            'Deskripsi',
        ];

        $rows = [];
        foreach ($products as $idx => $prod) {
            $rows[] = [
                $idx + 1,
                $prod->barcode,
                $prod->name,
                $prod->category->name ?? '',
                $prod->unit->symbol ?? ($prod->unit->name ?? 'Pcs'),
                $prod->cost_price,
                $prod->selling_price,
                $prod->stock,
                $prod->min_stock,
                $prod->is_active ? 'Aktif' : 'Nonaktif',
                $prod->description ?? '',
            ];
        }

        $filename = 'data-produk-' . date('Y-m-d_His') . '.' . $format;

        return $this->generateDownloadResponse($filename, 'Data Produk & Stok', $headers, $rows, $format);
    }

    /**
     * Ekspor Data Pelanggan ke format Excel / CSV
     */
    public function exportCustomers(string $format = 'xlsx'): StreamedResponse
    {
        $customers = Customer::with('debts')->orderBy('name')->get();

        $headers = [
            'No',
            'Nama Pelanggan',
            'No Telepon / WhatsApp',
            'Email',
            'Alamat',
            'Total Piutang Aktif',
            'Tanggal Terdaftar',
        ];

        $rows = [];
        foreach ($customers as $idx => $cust) {
            $remainingDebt = $cust->debts->where('status', '!=', 'paid')->sum('remaining_debt');
            $rows[] = [
                $idx + 1,
                $cust->name,
                $cust->phone ?? '',
                $cust->email ?? '',
                $cust->address ?? '',
                $remainingDebt,
                $cust->created_at ? $cust->created_at->format('Y-m-d H:i') : '',
            ];
        }

        $filename = 'data-pelanggan-' . date('Y-m-d_His') . '.' . $format;

        return $this->generateDownloadResponse($filename, 'Data Pelanggan Toko', $headers, $rows, $format);
    }

    /**
     * Ekspor Data Supplier ke format Excel / CSV
     */
    public function exportSuppliers(string $format = 'xlsx'): StreamedResponse
    {
        $suppliers = Supplier::orderBy('name')->get();

        $headers = [
            'No',
            'Nama Supplier',
            'Kontak Person',
            'No Telepon / WhatsApp',
            'Alamat',
            'Tanggal Terdaftar',
        ];

        $rows = [];
        foreach ($suppliers as $idx => $supp) {
            $rows[] = [
                $idx + 1,
                $supp->name,
                $supp->contact_person ?? '',
                $supp->phone ?? '',
                $supp->address ?? '',
                $supp->created_at ? $supp->created_at->format('Y-m-d H:i') : '',
            ];
        }

        $filename = 'data-supplier-' . date('Y-m-d_His') . '.' . $format;

        return $this->generateDownloadResponse($filename, 'Data Supplier Toko', $headers, $rows, $format);
    }

    /**
     * Unduh Template Excel untuk Import Produk / Pelanggan
     */
    public function getTemplate(string $type): StreamedResponse
    {
        if ($type === 'customers') {
            $headers = ['Nama Pelanggan (Wajib)', 'No WhatsApp / Telepon', 'Email', 'Alamat'];
            $sampleRows = [
                ['Budi Santoso', '081234567890', 'budi@example.com', 'Jl. Merdeka No. 10'],
                ['Siti Aminah', '089876543210', '', 'Komplek Griya Asri Blok B1'],
            ];
            $filename = 'template_import_pelanggan.xlsx';
            $title = 'Template Import Pelanggan';
        } else {
            $headers = [
                'Barcode (Opsional)',
                'Nama Produk (Wajib)',
                'Kategori',
                'Satuan',
                'Harga Modal (Angka)',
                'Harga Jual (Angka)',
                'Stok Awal',
                'Stok Minimum',
                'Deskripsi',
            ];
            $sampleRows = [
                ['8991234567890', 'Beras Sania 5kg', 'Sembako', 'Sak', 65000, 72000, 20, 5, 'Beras pulen kemasan 5 kg'],
                ['', 'Minyak Goreng Bimoli 1L', 'Minyak', 'Pcs', 17000, 19500, 50, 10, 'Kemasan pouch 1 liter'],
            ];
            $filename = 'template_import_produk.xlsx';
            $title = 'Template Import Produk';
        }

        return $this->generateDownloadResponse($filename, $title, $headers, $sampleRows, 'xlsx');
    }

    /**
     * Import Data Produk dari File Excel / CSV
     */
    public function importProducts(UploadedFile $file, bool $updateExisting = true): array
    {
        $rows = $this->readRowsFromFile($file);

        if (empty($rows)) {
            return [
                'success' => false,
                'message' => 'File kosong atau format data tidak dapat dibaca.',
                'imported' => 0,
                'updated'  => 0,
                'skipped'  => 0,
                'errors'   => ['File tidak berisi baris data.'],
            ];
        }

        // Baris pertama diasumsikan header
        $headerRow = array_shift($rows);
        $headerMap = $this->mapProductHeaders($headerRow);

        $imported = 0;
        $updated  = 0;
        $skipped  = 0;
        $errors   = [];

        // Cache category & unit untuk performa
        $categoryCache = Category::all()->keyBy(fn($c) => strtolower(trim($c->name)));
        $unitCache     = Unit::all()->keyBy(fn($u) => strtolower(trim($u->name)));
        $unitSymbolCache = Unit::all()->keyBy(fn($u) => strtolower(trim($u->symbol)));

        foreach ($rows as $rowIndex => $row) {
            $rowNum = $rowIndex + 2; // +2 karena index 0 dan ada header di baris 1

            // Cek jika seluruh baris kosong
            if (empty(array_filter($row, fn($val) => trim((string)$val) !== ''))) {
                continue;
            }

            $name = isset($headerMap['name']) ? trim((string)($row[$headerMap['name']] ?? '')) : '';
            if (empty($name)) {
                $errors[] = "Baris {$rowNum}: Nama produk wajib diisi, dilewati.";
                $skipped++;
                continue;
            }

            $barcode = isset($headerMap['barcode']) ? trim((string)($row[$headerMap['barcode']] ?? '')) : '';
            $catName = isset($headerMap['category']) ? trim((string)($row[$headerMap['category']] ?? '')) : '';
            $unitName = isset($headerMap['unit']) ? trim((string)($row[$headerMap['unit']] ?? '')) : '';
            $costPrice = isset($headerMap['cost_price']) ? $this->sanitizeNumeric($row[$headerMap['cost_price']] ?? 0) : 0;
            $sellingPrice = isset($headerMap['selling_price']) ? $this->sanitizeNumeric($row[$headerMap['selling_price']] ?? 0) : 0;
            $stock = isset($headerMap['stock']) ? (int)$this->sanitizeNumeric($row[$headerMap['stock']] ?? 0) : 0;
            $minStock = isset($headerMap['min_stock']) ? (int)$this->sanitizeNumeric($row[$headerMap['min_stock']] ?? 5) : 5;
            $description = isset($headerMap['description']) ? trim((string)($row[$headerMap['description']] ?? '')) : null;

            // Resolusi Kategori
            $categoryId = null;
            if (!empty($catName)) {
                $catKey = strtolower($catName);
                if (isset($categoryCache[$catKey])) {
                    $categoryId = $categoryCache[$catKey]->id;
                } else {
                    $newCat = Category::create([
                        'name' => $catName,
                        'slug' => Str::slug($catName) . '-' . Str::random(4),
                    ]);
                    $categoryCache[$catKey] = $newCat;
                    $categoryId = $newCat->id;
                }
            }

            // Resolusi Satuan
            $unitId = null;
            if (!empty($unitName)) {
                $unitKey = strtolower($unitName);
                if (isset($unitCache[$unitKey])) {
                    $unitId = $unitCache[$unitKey]->id;
                } elseif (isset($unitSymbolCache[$unitKey])) {
                    $unitId = $unitSymbolCache[$unitKey]->id;
                } else {
                    $newUnit = Unit::create([
                        'name'   => $unitName,
                        'symbol' => Str::limit($unitName, 10, ''),
                    ]);
                    $unitCache[$unitKey] = $newUnit;
                    $unitId = $newUnit->id;
                }
            } else {
                // Default ke unit pertama atau 'Pcs'
                $defaultUnit = Unit::firstOrCreate(['name' => 'Pcs'], ['symbol' => 'Pcs']);
                $unitId = $defaultUnit->id;
            }

            // Cek apakah produk sudah ada (berdasarkan barcode jika ada, atau nama produk)
            $existingProduct = null;
            if (!empty($barcode)) {
                $existingProduct = Product::where('barcode', $barcode)->first();
            }

            if (!$existingProduct) {
                $existingProduct = Product::where('name', $name)->first();
            }

            if ($existingProduct) {
                if ($updateExisting) {
                    $existingProduct->update([
                        'category_id'   => $categoryId ?? $existingProduct->category_id,
                        'unit_id'       => $unitId ?? $existingProduct->unit_id,
                        'cost_price'    => $costPrice > 0 ? $costPrice : $existingProduct->cost_price,
                        'selling_price' => $sellingPrice > 0 ? $sellingPrice : $existingProduct->selling_price,
                        'stock'         => $stock,
                        'min_stock'     => $minStock,
                        'description'   => $description ?? $existingProduct->description,
                    ]);
                    $updated++;
                } else {
                    $skipped++;
                }
            } else {
                // Generate barcode unik jika kosong
                if (empty($barcode)) {
                    $barcode = 'PRD' . date('ymd') . strtoupper(Str::random(5));
                }

                Product::create([
                    'barcode'       => $barcode,
                    'name'          => $name,
                    'category_id'   => $categoryId,
                    'unit_id'       => $unitId,
                    'cost_price'    => $costPrice,
                    'selling_price' => $sellingPrice,
                    'stock'         => $stock,
                    'min_stock'     => $minStock,
                    'is_active'     => true,
                    'description'   => $description,
                ]);
                $imported++;
            }
        }

        return [
            'success'  => true,
            'imported' => $imported,
            'updated'  => $updated,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ];
    }

    /**
     * Import Data Pelanggan dari File Excel / CSV
     */
    public function importCustomers(UploadedFile $file): array
    {
        $rows = $this->readRowsFromFile($file);

        if (empty($rows)) {
            return [
                'success' => false,
                'message' => 'File kosong atau format data tidak dapat dibaca.',
                'imported' => 0,
                'skipped'  => 0,
                'errors'   => ['File tidak berisi baris data.'],
            ];
        }

        $headerRow = array_shift($rows);
        $headerMap = $this->mapCustomerHeaders($headerRow);

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($rows as $rowIndex => $row) {
            $rowNum = $rowIndex + 2;

            if (empty(array_filter($row, fn($val) => trim((string)$val) !== ''))) {
                continue;
            }

            $name = isset($headerMap['name']) ? trim((string)($row[$headerMap['name']] ?? '')) : '';
            if (empty($name)) {
                $errors[] = "Baris {$rowNum}: Nama pelanggan kosong, dilewati.";
                $skipped++;
                continue;
            }

            $phone   = isset($headerMap['phone']) ? trim((string)($row[$headerMap['phone']] ?? '')) : null;
            $email   = isset($headerMap['email']) ? trim((string)($row[$headerMap['email']] ?? '')) : null;
            $address = isset($headerMap['address']) ? trim((string)($row[$headerMap['address']] ?? '')) : null;

            // Cek duplikasi pelanggan berdasarkan nomor telepon atau nama
            $existsQuery = Customer::query()->where('name', $name);
            if (!empty($phone)) {
                $existsQuery->orWhere('phone', $phone);
            }

            if ($existsQuery->exists()) {
                $skipped++;
                continue;
            }

            Customer::create([
                'name'    => $name,
                'phone'   => $phone,
                'email'   => !empty($email) ? $email : null,
                'address' => $address,
            ]);

            $imported++;
        }

        return [
            'success'  => true,
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ];
    }

    /**
     * Membaca seluruh baris dari file Excel (.xlsx/.xls) atau .csv
     */
    protected function readRowsFromFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        // Jika CSV, kita bisa baca langsung atau via PhpSpreadsheet
        if ($extension === 'csv') {
            $rows = [];
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                // Skip BOM jika ada
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }
                while (($data = fgetcsv($handle, 2000, ',')) !== false) {
                    // Cek jika delimiter titik koma (;)
                    if (count($data) === 1 && str_contains($data[0], ';')) {
                        $data = str_getcsv($data[0], ';');
                    }
                    $rows[] = $data;
                }
                fclose($handle);
            }
            return $rows;
        }

        // File Excel .xlsx / .xls
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        
        return $sheet->toArray(null, true, true, false);
    }

    /**
     * Deteksi kolom produk berdasarkan header
     */
    protected function mapProductHeaders(array $headers): array
    {
        $map = [
            'barcode'       => 0,
            'name'          => 1,
            'category'      => 2,
            'unit'          => 3,
            'cost_price'    => 4,
            'selling_price' => 5,
            'stock'         => 6,
            'min_stock'     => 7,
            'description'   => 8,
        ];

        foreach ($headers as $index => $header) {
            $h = strtolower(trim((string)$header));
            if (str_contains($h, 'barcode') || str_contains($h, 'kode')) {
                $map['barcode'] = $index;
            } elseif (str_contains($h, 'nama') || str_contains($h, 'produk') || str_contains($h, 'barang')) {
                $map['name'] = $index;
            } elseif (str_contains($h, 'kategori')) {
                $map['category'] = $index;
            } elseif (str_contains($h, 'satuan') || str_contains($h, 'unit')) {
                $map['unit'] = $index;
            } elseif (str_contains($h, 'modal') || str_contains($h, 'beli') || str_contains($h, 'hpp') || str_contains($h, 'cost')) {
                $map['cost_price'] = $index;
            } elseif (str_contains($h, 'jual') || str_contains($h, 'selling') || str_contains($h, 'harga')) {
                $map['selling_price'] = $index;
            } elseif (str_contains($h, 'min') || str_contains($h, 'batas')) {
                $map['min_stock'] = $index;
            } elseif (str_contains($h, 'stok') || str_contains($h, 'stock') || str_contains($h, 'qty')) {
                $map['stock'] = $index;
            } elseif (str_contains($h, 'deskripsi') || str_contains($h, 'keterangan')) {
                $map['description'] = $index;
            }
        }

        return $map;
    }

    /**
     * Deteksi kolom pelanggan berdasarkan header
     */
    protected function mapCustomerHeaders(array $headers): array
    {
        $map = [
            'name'    => 0,
            'phone'   => 1,
            'email'   => 2,
            'address' => 3,
        ];

        foreach ($headers as $index => $header) {
            $h = strtolower(trim((string)$header));
            if (str_contains($h, 'nama') || str_contains($h, 'pelanggan') || str_contains($h, 'member')) {
                $map['name'] = $index;
            } elseif (str_contains($h, 'telp') || str_contains($h, 'telepon') || str_contains($h, 'hp') || str_contains($h, 'wa')) {
                $map['phone'] = $index;
            } elseif (str_contains($h, 'email') || str_contains($h, 'surel')) {
                $map['email'] = $index;
            } elseif (str_contains($h, 'alamat') || str_contains($h, 'address')) {
                $map['address'] = $index;
            }
        }

        return $map;
    }

    /**
     * Membersihkan angka dari karakter mata uang, titik, spasi, dsb.
     */
    protected function sanitizeNumeric($value): float
    {
        if (is_numeric($value)) {
            return (float)$value;
        }

        // Hapus "Rp", spasi, dan titik pemisah ribuan
        $cleaned = preg_replace('/[^\d,.-]/', '', (string)$value);
        // Jika ada format koma desimal Indonesia (misal: 10.000,50)
        if (str_contains($cleaned, ',') && str_contains($cleaned, '.')) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } elseif (str_contains($cleaned, ',')) {
            // Cek apakah koma sebagai desimal
            $parts = explode(',', $cleaned);
            if (count($parts) === 2 && strlen($parts[1]) <= 2) {
                $cleaned = str_replace(',', '.', $cleaned);
            } else {
                $cleaned = str_replace(',', '', $cleaned);
            }
        }

        return (float)($cleaned ?: 0);
    }

    /**
     * Helper menghasilkan StreamedResponse untuk Excel (.xlsx) atau CSV (.csv)
     */
    protected function generateDownloadResponse(string $filename, string $sheetTitle, array $headers, array $rows, string $format): StreamedResponse
    {
        if (strtolower($format) === 'csv') {
            return response()->streamDownload(function () use ($headers, $rows) {
                $file = fopen('php://output', 'w');
                // Tulis UTF-8 BOM
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($file, $headers);
                foreach ($rows as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            }, $filename, [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control'       => 'no-store, no-cache',
            ]);
        }

        // Generate Excel (.xlsx) via PhpSpreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(Str::limit($sheetTitle, 30, ''));

        // Baris Header
        $colIndex = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueExplicit([$colIndex, 1], $header, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $colIndex++;
        }

        // Style Header
        $highestColumn = $sheet->getHighestColumn();
        $headerRange = 'A1:' . $highestColumn . '1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '696CFF'], // Primary Sneat color
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // Isi Data
        $rowNum = 2;
        foreach ($rows as $row) {
            $cIndex = 1;
            foreach ($row as $val) {
                if (is_numeric($val) && !is_string($val)) {
                    $sheet->setCellValueExplicit([$cIndex, $rowNum], $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                } else {
                    $sheet->setCellValueExplicit([$cIndex, $rowNum], (string)$val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
                $cIndex++;
            }
            $rowNum++;
        }

        // Auto-size columns & borders
        $lastRow = max(1, $rowNum - 1);
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $allCellsRange = 'A1:' . $highestColumn . $lastRow;
        $sheet->getStyle($allCellsRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'D9DEE3'],
                ],
            ],
        ]);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-store, no-cache',
        ]);
    }
}
