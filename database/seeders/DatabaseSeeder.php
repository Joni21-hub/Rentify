<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Admin
        User::updateOrCreate(
            ['email' => 'rentify.id@gmail.com'],
            [
                'name' => 'Admin Rentify',
                'password' => Hash::make('Rentify21'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Akun Vendor
        User::updateOrCreate(
            ['email' => 'vendor@rentify.com'],
            [
                'name' => 'Vendor Sewa Kamera',
                'password' => Hash::make('password123'),
                'role' => 'vendor',
                'vendor_name' => 'Sewa Kamera Jakarta',
                'vendor_status' => 'approved',
                'email_verified_at' => now(),
            ]
        );

        // Akun Customer
        User::updateOrCreate(
            ['email' => 'customer@rentify.com'],
            [
                'name' => 'Customer Budi',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'email_verified_at' => now(),
            ]
        );

        // Seed Kategori Pilihan
        $this->call(KategoriSeeder::class);
    }
}
