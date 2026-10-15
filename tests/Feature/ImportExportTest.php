<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'role'      => 'staff',
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'role'      => 'cashier',
            'is_active' => true,
        ]);
    }

    public function test_import_export_page_is_accessible_by_admin(): void
    {
        $response = $this->actingAs($this->admin)->get(route('import_export.index'));
        $response->assertStatus(200);
        $response->assertSee('Data Master /');
        $response->assertSee('Import &amp; Export Data', false);
    }

    public function test_import_export_page_is_accessible_by_staff(): void
    {
        $response = $this->actingAs($this->staff)->get(route('import_export.index'));
        $response->assertStatus(200);
    }

    public function test_import_export_page_is_forbidden_for_cashier(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('import_export.index'));
        $response->assertStatus(403);
    }

    public function test_download_template_products(): void
    {
        $response = $this->actingAs($this->admin)->get(route('import_export.template', ['type' => 'products']));
        $response->assertStatus(200);
        $this->assertStringContainsString('template_import_produk.xlsx', $response->headers->get('Content-Disposition'));
    }

    public function test_download_template_customers(): void
    {
        $response = $this->actingAs($this->admin)->get(route('import_export.template', ['type' => 'customers']));
        $response->assertStatus(200);
        $this->assertStringContainsString('template_import_pelanggan.xlsx', $response->headers->get('Content-Disposition'));
    }

    public function test_export_products_as_csv(): void
    {
        $cat = Category::create(['name' => 'Minuman', 'slug' => 'minuman']);
        $unit = Unit::create(['name' => 'Botol', 'symbol' => 'Btl']);

        Product::create([
            'barcode'       => 'TEST-BRC-01',
            'name'          => 'Teh Botol Sosro',
            'category_id'   => $cat->id,
            'unit_id'       => $unit->id,
            'cost_price'    => 3000,
            'selling_price' => 5000,
            'stock'         => 24,
            'min_stock'     => 5,
            'is_active'     => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('import_export.export'), [
            'export_type'   => 'products',
            'export_format' => 'csv',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_export_products_as_xlsx(): void
    {
        $cat = Category::create(['name' => 'Snack', 'slug' => 'snack']);
        $unit = Unit::create(['name' => 'Bungkus', 'symbol' => 'Bks']);

        Product::create([
            'barcode'       => 'TEST-BRC-02',
            'name'          => 'Chitato Sapi Panggang',
            'category_id'   => $cat->id,
            'unit_id'       => $unit->id,
            'cost_price'    => 8000,
            'selling_price' => 11000,
            'stock'         => 15,
            'min_stock'     => 3,
            'is_active'     => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('import_export.export'), [
            'export_type'   => 'products',
            'export_format' => 'xlsx',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_customers_as_csv(): void
    {
        Customer::create([
            'name'    => 'Pak Joko',
            'phone'   => '08123456789',
            'email'   => 'joko@test.com',
            'address' => 'Surabaya',
        ]);

        $response = $this->actingAs($this->admin)->post(route('import_export.export'), [
            'export_type'   => 'customers',
            'export_format' => 'csv',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_export_suppliers_as_xlsx(): void
    {
        Supplier::create([
            'name'           => 'PT Distribusi Pangan',
            'contact_person' => 'Hendra',
            'phone'          => '08234567890',
            'address'        => 'Jakarta',
        ]);

        $response = $this->actingAs($this->admin)->post(route('import_export.export'), [
            'export_type'   => 'suppliers',
            'export_format' => 'xlsx',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_import_products_from_csv(): void
    {
        $csvContent = "Barcode,Nama Produk,Kategori,Satuan,Harga Modal,Harga Jual,Stok Awal,Stok Minimum,Deskripsi\n" .
                      "BRC-IMP-01,Kopi Kapal Api 65g,Minuman,Sachet,2000,3000,100,10,Kopi hitam bubuk\n" .
                      "BRC-IMP-02,Indomie Goreng,Makanan,Pcs,2800,3500,50,15,Mie instan goreng\n";

        $file = UploadedFile::fake()->createWithContent('products.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('import_export.import'), [
            'import_type'     => 'products',
            'import_file'     => $file,
            'update_existing' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'barcode' => 'BRC-IMP-01',
            'name'    => 'Kopi Kapal Api 65g',
            'stock'   => 100,
        ]);

        $this->assertDatabaseHas('products', [
            'barcode' => 'BRC-IMP-02',
            'name'    => 'Indomie Goreng',
            'stock'   => 50,
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Minuman',
        ]);
    }

    public function test_import_products_updates_existing_product_when_enabled(): void
    {
        $prod = Product::create([
            'barcode'       => 'BRC-UPDATE-01',
            'name'          => 'Gula Pasir Gulaku 1kg',
            'cost_price'    => 14000,
            'selling_price' => 17000,
            'stock'         => 10,
            'min_stock'     => 5,
            'is_active'     => true,
        ]);

        $csvContent = "Barcode,Nama Produk,Kategori,Satuan,Harga Modal,Harga Jual,Stok Awal,Stok Minimum,Deskripsi\n" .
                      "BRC-UPDATE-01,Gula Pasir Gulaku 1kg,Bahan Pokok,Pcs,15000,18500,45,10,Kemasan hijau 1kg\n";

        $file = UploadedFile::fake()->createWithContent('update_product.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('import_export.import'), [
            'import_type'     => 'products',
            'import_file'     => $file,
            'update_existing' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id'            => $prod->id,
            'barcode'       => 'BRC-UPDATE-01',
            'selling_price' => 18500,
            'stock'         => 45,
        ]);
    }

    public function test_import_customers_from_csv(): void
    {
        $csvContent = "Nama Pelanggan,No WhatsApp,Email,Alamat\n" .
                      "Ibu Fatimah,08567890123,fatimah@example.com,Jl. Kenanga 12\n" .
                      "Mas Agus,08789012345,,Perum Indah Blok C3\n";

        $file = UploadedFile::fake()->createWithContent('customers.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('import_export.import'), [
            'import_type' => 'customers',
            'import_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'name'  => 'Ibu Fatimah',
            'phone' => '08567890123',
        ]);

        $this->assertDatabaseHas('customers', [
            'name'  => 'Mas Agus',
            'phone' => '08789012345',
        ]);
    }

    public function test_import_validation_fails_for_invalid_file_extension(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('import_export.import'), [
            'import_type' => 'products',
            'import_file' => $file,
        ]);

        $response->assertSessionHasErrors('import_file');
    }
}
