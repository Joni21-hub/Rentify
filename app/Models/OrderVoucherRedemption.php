<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderVoucherRedemption extends Model
{
    use HasFactory;

    protected $table = 'order_voucher_redemptions';

    protected $fillable = [
        'payment_group_id',
        'order_id',
        'penyewaan_id',
        'voucher_id',
        'customer_voucher_id',
        'tipe_voucher',
        'alokasi_diskon',
        'status',
    ];

    protected $casts = [
        'alokasi_diskon' => 'decimal:2',
    ];

    /**
     * Relasi ke Pesanan / Penyewaan
     */
    public function order()
    {
        return $this->belongsTo(Penyewaan::class, 'order_id');
    }

    /**
     * Alias relasi ke Penyewaan
     */
    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'order_id');
    }

    /**
     * Relasi ke Voucher yang digunakan
     */
    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    /**
     * Relasi ke Klaim Voucher Pelanggan
     */
    public function customerVoucher()
    {
        return $this->belongsTo(CustomerVoucher::class, 'customer_voucher_id');
    }
}
