<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPenyewaan extends Model
{

    protected $table = 'order_items';
    protected $guarded = ['id'];

    public function penyewaan()
    {
        // Sesuaikan 'penyewaan_id' atau 'order_id' dengan nama foreign key di tabel order_items Anda
        return $this->belongsTo(Penyewaan::class, 'order_id');
    }

    /**
     * Relasi ke Model Barang
     * Jembatan krusial yang digunakan oleh VendorDashboardController untuk mencari barang milik Vendor
     */
    public function barang()
    {
        // Sesuaikan 'barang_id' dengan nama kolom foreign key barang di tabel order_items Anda
        return $this->belongsTo(Barang::class, 'product_id');
    }
}