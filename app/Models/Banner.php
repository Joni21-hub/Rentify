<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit (opsional, karena Laravel otomatis mendeteksi jamak 'banners')
    protected $table = 'banners';

    // Daftarkan kolom yang boleh diisi secara massal (Mass Assignment)
    protected $fillable = [
        'judul_promo',
        'gambar_url',
    ];
}