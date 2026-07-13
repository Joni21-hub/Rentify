<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    // Beritahu Laravel nama tabel yang benar di database Kakak
    // (Ubah 'kategori' menjadi 'categories' atau 'kategoris' jika nama tabel di database Kakak berbeda)
    protected $table = 'kategoris'; 

    protected $guarded = ['id'];

    // Relasi balik ke Barang (1 Kategori memiliki banyak Barang)
    public function barangs()
    {
        return $this->hasMany(Barang::class, 'kategori_id');
    }
}