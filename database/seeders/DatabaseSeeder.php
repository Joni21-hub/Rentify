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
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@rentify.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Akun Vendor
        User::create([
            'name' => 'Vendor Sewa Kamera',
            'email' => 'vendor@rentify.com',
            'password' => Hash::make('password123'),
            'role' => 'vendor',
            'vendor_name' => 'Sewa Kamera Jakarta',
            'vendor_status' => 'approved',
            'email_verified_at' => now(),
        ]);

        // Akun Customer
        User::create([
            'name' => 'Customer Budi',
            'email' => 'customer@rentify.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);
    }
}
