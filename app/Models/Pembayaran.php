<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayarans';

    protected $fillable = [
        'order_id',
        'metode',
        'doku_payment_id',
        'doku_channel',
        'jumlah',
        'bukti_pembayaran',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'jumlah'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Penyewaan::class, 'order_id');
    }
}
