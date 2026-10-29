<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FotoBarang extends Model
{
    use HasFactory;

    protected $table = 'foto_barangs';
    protected $guarded = ['id'];

    protected $appends = [
        'foto_url',
        'is_video',
    ];

    /**
     * Membersihkan berkas fisik di Cloudinary / disk lokal saat foto galeri dihapus.
     */
    protected static function booted(): void
    {
        static::deleting(function (FotoBarang $foto) {
            if (!empty($foto->foto_path)) {
                \App\Services\StorageService::deleteFile($foto->foto_path);
            }
        });
    }

    public function getFotoUrlAttribute(): string
    {
        return \App\Services\StorageService::getPublicUrl($this->foto_path, 'placeholders/placeholder-barang.png');
    }

    public function getIsVideoAttribute(): bool
    {
        return \App\Services\StorageService::isVideoMedia($this->foto_path);
    }

    public function getImageUrlAttribute(): string
    {
        return $this->foto_url;
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}