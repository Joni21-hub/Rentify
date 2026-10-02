<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = [
            'Peralatan Have Fun',
            'Outdoor',
            'Elektronik',
            'Kendaraan',
            'Kos / Kamar',
        ];

        foreach ($kategoris as $nama) {
            Kategori::updateOrCreate(
                ['nama' => $nama],
                ['is_active' => 1]
            );
        }
    }
}
