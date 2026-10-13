<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Akun Admin / Owner (Akses Penuh)
        User::updateOrCreate(
            ['email' => 'admin@toko.com'],
            [
                'name' => 'Owner / Administrator',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'phone' => '081234567890',
                'is_active' => true,
            ]
        );

        // 2. Akun Kasir (POS, Penjualan, Pelanggan, Hutang)
        User::updateOrCreate(
            ['email' => 'kasir@toko.com'],
            [
                'name' => 'Kasir Toko',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CASHIER,
                'phone' => '081234567891',
                'is_active' => true,
            ]
        );

        // 3. Akun Gudang / Staf (Stok, Pembelian, Supplier, Master Produk)
        User::updateOrCreate(
            ['email' => 'gudang@toko.com'],
            [
                'name' => 'Staf Gudang',
                'password' => Hash::make('password'),
                'role' => User::ROLE_STAFF,
                'phone' => '081234567892',
                'is_active' => true,
            ]
        );
    }
}
