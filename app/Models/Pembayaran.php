<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayarans';

    protected $fillable = [
        'order_id',
        'metode',
        'jumlah',
        'bukti_pembayaran',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'jumlah'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function getBuktiPembayaranUrlAttribute(): string
    {
        return \App\Services\StorageService::getPublicUrl($this->bukti_pembayaran, 'placeholders/placeholder-bukti.png');
    }

    public function getImageUrlAttribute(): string
    {
        return $this->bukti_pembayaran_url;
    }

    public function order()
    {
        return $this->belongsTo(Penyewaan::class, 'order_id');
    }
}
