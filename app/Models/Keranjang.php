<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Keranjang extends Model
{
    use HasFactory;

    protected $table = 'keranjangs';

    protected $fillable = [
        'user_id', 
        'barang_id', 
        'jumlah',
        'durasi_sewa'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}