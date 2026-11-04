<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketSewaItem extends Model
{
    use HasFactory;

    protected $table = 'paket_sewa_items';

    protected $fillable = [
        'paket_id',
        'paket_sewa_id',
        'barang_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Relasi ke Paket (Barang dengan is_paket = 1)
     */
    public function paket()
    {
        return $this->belongsTo(Barang::class, 'paket_id');
    }

    /**
     * Relasi ke Paket Sewa Induk
     */
    public function paketSewa()
    {
        return $this->belongsTo(PaketSewa::class, 'paket_sewa_id');
    }

    /**
     * Relasi ke Barang Fisik Komponen Paket
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
