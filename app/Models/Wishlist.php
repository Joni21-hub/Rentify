<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $fillable = ['user_id', 'barang_id'];

    // Relasi ke tabel barang
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}