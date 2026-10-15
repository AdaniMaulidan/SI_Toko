<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Services\DatabaseBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected User $staff;
    protected DatabaseBackupService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'      => 'Admin Sistem',
            'role'      => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'name'      => 'Kasir Toko',
            'role'      => User::ROLE_CASHIER,
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'name'      => 'Staff Gudang',
            'role'      => User::ROLE_STAFF,
            'is_active' => true,
        ]);

        $this->service = app(DatabaseBackupService::class);
    }

    protected function tearDown(): void
    {
        // Bersihkan file backup uji coba di direktori backups
        $backupDir = $this->service->getBackupDirectory();
        if (File::isDirectory($backupDir)) {
            $files = File::files($backupDir);
            foreach ($files as $file) {
                if (str_starts_with($file->getFilename(), 'test-') || str_starts_with($file->getFilename(), 'backup-sitoko-')) {
                    File::delete($file->getRealPath());
                }
            }
        }

        parent::tearDown();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('backup.index'))->assertRedirect(route('login'));
        $this->get(route('backup.download'))->assertRedirect(route('login'));
        $this->post(route('backup.store'))->assertRedirect(route('login'));
    }

    public function test_cashier_and_staff_cannot_access_backup(): void
    {
        $this->actingAs($this->cashier)->get(route('backup.index'))->assertForbidden();
        $this->actingAs($this->cashier)->get(route('backup.download'))->assertForbidden();
        $this->actingAs($this->staff)->post(route('backup.store'))->assertForbidden();
        $this->actingAs($this->cashier)->post(route('backup.restore'))->assertForbidden();
    }

    public function test_admin_can_view_backup_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('backup.index'));

        $response->assertOk();
        $response->assertViewIs('backup.index');
        $response->assertViewHas('dbStats');
        $response->assertViewHas('backups');
        $response->assertSee('Backup & Restore Database');
        $response->assertSee('Unduh Backup .SQL (Sekali Klik)');
    }

    public function test_admin_can_download_database_backup_sql_one_click(): void
    {
        $cat = Category::create(['name' => 'Kategori Backup', 'slug' => 'kategori-backup']);
        $unit = Unit::create(['name' => 'Pcs', 'short_name' => 'Pcs']);

        Product::create([
            'category_id'   => $cat->id,
            'unit_id'       => $unit->id,
            'barcode'       => 'TEST-BACKUP-01',
            'name'          => 'Produk Uji Backup',
            'cost_price'    => 5000,
            'selling_price' => 7500,
            'stock'         => 20,
            'min_stock'     => 5,
            'is_active'     => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('backup.download'));

        $response->assertOk();
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('.sql', $response->headers->get('content-disposition'));

        // Cek isi file SQL
        $content = file_get_contents($response->getFile()->getPathname());
        $this->assertStringContainsString('SI Toko - Database SQL Dump Backup', $content);
        $this->assertStringContainsString('Produk Uji Backup', $content);
    }

    public function test_admin_can_store_backup_to_server(): void
    {
        $response = $this->actingAs($this->admin)->post(route('backup.store'));

        $response->assertRedirect(route('backup.index'));
        $response->assertSessionHas('success');

        $backups = $this->service->getBackupFiles();
        $this->assertNotEmpty($backups);
    }

    public function test_admin_can_download_and_delete_saved_backup_file(): void
    {
        $testFilename = 'test-saved-backup-' . time() . '.sql';
        $testPath = $this->service->createBackupFile($testFilename);

        $this->assertFileExists($testPath);

        // Unduh
        $downloadResponse = $this->actingAs($this->admin)->get(route('backup.download-file', $testFilename));
        $downloadResponse->assertOk();

        // Hapus
        $deleteResponse = $this->actingAs($this->admin)->delete(route('backup.destroy', $testFilename));
        $deleteResponse->assertRedirect(route('backup.index'));
        $deleteResponse->assertSessionHas('success');

        $this->assertFileDoesNotExist($testPath);
    }

    public function test_admin_can_restore_database_from_uploaded_sql(): void
    {
        $sqlDump = $this->service->dumpSql();
        $fakeFile = UploadedFile::fake()->createWithContent('restore_test.sql', $sqlDump);

        $response = $this->actingAs($this->admin)->post(route('backup.restore'), [
            'file_restore' => $fakeFile,
        ]);

        $response->assertRedirect(route('backup.index'));
        $response->assertSessionHas('success');
    }

    public function test_admin_can_restore_database_from_server_file(): void
    {
        $testFilename = 'test-server-restore-' . time() . '.sql';
        $testPath = $this->service->createBackupFile($testFilename);

        $response = $this->actingAs($this->admin)->post(route('backup.restore'), [
            'backup_filename' => $testFilename,
        ]);

        $response->assertRedirect(route('backup.index'));
        $response->assertSessionHas('success');
    }

    public function test_restore_rejects_non_sql_file(): void
    {
        $fakeFile = UploadedFile::fake()->createWithContent('malicious.txt', 'Hacked data');

        $response = $this->actingAs($this->admin)->post(route('backup.restore'), [
            'file_restore' => $fakeFile,
        ]);

        $response->assertRedirect(route('backup.index'));
        $response->assertSessionHas('error');
    }
}
