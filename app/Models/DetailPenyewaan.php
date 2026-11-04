<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenyewaan extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'paket_sewa_id',
        'item_type',
        'product_name',
        'price',
        'quantity',
        'subtotal_sewa_asli',
        'subtotal_sewa_markup',
        'alokasi_diskon_toko',
        'alokasi_diskon_platform',
        'alokasi_diskon_koin',
        'subtotal_sewa_bersih',
        'status_item',
    ];

    protected $casts = [
        'price'                  => 'decimal:2',
        'quantity'               => 'integer',
        'subtotal_sewa_asli'     => 'decimal:2',
        'subtotal_sewa_markup'   => 'decimal:2',
        'alokasi_diskon_toko'    => 'decimal:2',
        'alokasi_diskon_platform'=> 'decimal:2',
        'alokasi_diskon_koin'    => 'decimal:2',
        'subtotal_sewa_bersih'   => 'decimal:2',
    ];

    /**
     * Relasi ke Penyewaan / Order
     */
    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'order_id');
    }

    /**
     * Alias relasi ke Order
     */
    public function order()
    {
        return $this->belongsTo(Penyewaan::class, 'order_id');
    }

    /**
     * Relasi ke Model Barang
     * Jembatan krusial yang digunakan oleh VendorDashboardController untuk mencari barang milik Vendor
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'product_id');
    }

    /**
     * Relasi ke Paket Sewa (jika item_type = 'paket')
     */
    public function paketSewa()
    {
        return $this->belongsTo(PaketSewa::class, 'paket_sewa_id');
    }

    /**
     * Relasi ke Komponen Detail Item Pesanan (tabel order_item_components)
     */
    public function components()
    {
        return $this->hasMany(OrderItemComponent::class, 'order_item_id');
    }
}
