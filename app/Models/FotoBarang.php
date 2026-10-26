<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FotoBarang extends Model
{
    use HasFactory;

    protected $table = 'foto_barangs';
    protected $guarded = ['id'];

    public function getFotoUrlAttribute(): string
    {
        return \App\Services\StorageService::getPublicUrl($this->foto_path, 'placeholders/placeholder-barang.png');
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