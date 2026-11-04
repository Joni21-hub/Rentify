<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemComponent extends Model
{
    use HasFactory;

    protected $table = 'order_item_components';

    protected $fillable = [
        'order_item_id',
        'barang_id',
        'quantity',
        'harga_komponen_asli',
        'subtotal_sewa_asli',
        'subtotal_sewa_markup',
        'alokasi_diskon_toko',
        'alokasi_diskon_platform',
        'alokasi_diskon_koin',
        'subtotal_sewa_bersih',
        'deposit_komponen',
        'denda_komponen',
        'status_komponen',
        'returned_at',
    ];

    protected $casts = [
        'quantity'                => 'integer',
        'harga_komponen_asli'     => 'decimal:2',
        'subtotal_sewa_asli'      => 'decimal:2',
        'subtotal_sewa_markup'    => 'decimal:2',
        'alokasi_diskon_toko'     => 'decimal:2',
        'alokasi_diskon_platform' => 'decimal:2',
        'alokasi_diskon_koin'     => 'decimal:2',
        'subtotal_sewa_bersih'    => 'decimal:2',
        'deposit_komponen'        => 'decimal:2',
        'denda_komponen'          => 'decimal:2',
        'returned_at'             => 'datetime',
    ];

    /**
     * Relasi ke Line Item Pesanan Induk (DetailPenyewaan)
     */
    public function orderItem()
    {
        return $this->belongsTo(DetailPenyewaan::class, 'order_item_id');
    }

    /**
     * Relasi ke Barang Fisik Komponen
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
