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
        // 1. Akun Owner (Administrator)
        User::updateOrCreate(
            ['email' => 'ownertoko@email.com'],
            [
                'name'              => 'Owner',
                'password'          => Hash::make('password'),
                'role'              => User::ROLE_ADMIN,
                'phone'             => '081234567890',
                'image'             => 'users/LRrnStnR3vgBNhuJAqLcj3sEmgcn9UqVQufVAPsa.png',
                'avatar'            => 'users/LRrnStnR3vgBNhuJAqLcj3sEmgcn9UqVQufVAPsa.png',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. Akun Admin (Administrator Tambahan)
        User::updateOrCreate(
            ['email' => 'admintoko@email.com'],
            [
                'name'              => 'Admin',
                'password'          => Hash::make('password'),
                'role'              => User::ROLE_ADMIN,
                'phone'             => '08987654321',
                'image'             => 'users/0WSkcLHxsNFWHDZP5RG4WuMD60nnVkP79SRgrq4s.jpg',
                'avatar'            => 'users/0WSkcLHxsNFWHDZP5RG4WuMD60nnVkP79SRgrq4s.jpg',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        // 3. Akun Kasir (POS, Penjualan, Pelanggan, Hutang)
        User::updateOrCreate(
            ['email' => 'kasirtoko@email.com'],
            [
                'name'              => 'Kasir',
                'password'          => Hash::make('password'),
                'role'              => User::ROLE_CASHIER,
                'phone'             => '085647382910',
                'image'             => 'users/7OmuYllNeMEpIZl4nIuXzF6CtBBLVclGTYW2SajM.png',
                'avatar'            => 'users/7OmuYllNeMEpIZl4nIuXzF6CtBBLVclGTYW2SajM.png',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        // 4. Akun Gudang (Stok, Pembelian, Supplier, Master Produk)
        User::updateOrCreate(
            ['email' => 'gudangtoko@email.com'],
            [
                'name'              => 'Gudang',
                'password'          => Hash::make('password'),
                'role'              => User::ROLE_STAFF,
                'phone'             => '086574839201',
                'image'             => 'users/P79hHRwmkLrltZL1mKf5eIZ0GHL84tJo0RngSD1o.png',
                'avatar'            => 'users/P79hHRwmkLrltZL1mKf5eIZ0GHL84tJo0RngSD1o.png',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
