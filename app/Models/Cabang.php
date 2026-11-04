<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    use HasFactory;

    protected $table = 'cabangs';

    protected $fillable = [
        'vendor_id',
        'nama_cabang',
        'alamat_lengkap',
        'koordinat',
    ];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function barangs()
    {
        return $this->hasMany(Barang::class, 'cabang_id');
    }

    public function paketSewas()
    {
        return $this->hasMany(PaketSewa::class, 'cabang_id');
    }
}
