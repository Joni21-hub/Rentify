<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Admin (Keamanan Tinggi: Wajib Google SSO, tanpa password)
        User::updateOrCreate(
            ['email' => 'rentify.id@gmail.com'],
            [
                'name' => 'Administrator Rentify',
                'password' => Hash::make(\Illuminate\Support\Str::random(64)),
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

        // Akun Vendor & Customer (Dual Role): Joni Utama
        User::updateOrCreate(
            ['email' => 'jutama937@gmail.com'],
            [
                'name' => 'Joni Utama',
                'password' => Hash::make('Joni21...'),
                'role' => 'vendor',
                'vendor_name' => 'Rentify',
                'vendor_status' => 'approved',
                'whatsapp' => '082183394549',
                'whatsapp_vendor' => '082183394549',
                'email_verified_at' => now(),
                'whatsapp_verified_at' => now(),
                'alamat_lengkap' => 'Jalan Raya Medan Banda Aceh, Palohlada, Dewantara, Aceh Utara, Aceh, 24354, Indonesia',
                'latitude' => 5.2375573,
                'longitude' => 97.019691,
                'foto_profil' => 'https://res.cloudinary.com/fnf8f1pm/image/upload/v1790882079/rentify/customer_profil/cmaidb11w8haofmtax0w.png',
            ]
        );

        // Seed Kategori Pilihan
        $this->call(KategoriSeeder::class);
    }
}
