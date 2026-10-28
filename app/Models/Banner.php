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

    /**
     * Membersihkan berkas banner di Cloudinary saat banner dihapus.
     */
    protected static function booted(): void
    {
        static::deleting(function (Banner $banner) {
            if (!empty($banner->gambar_url)) {
                \App\Services\StorageService::deleteFile($banner->gambar_url);
            }
        });
    }
}