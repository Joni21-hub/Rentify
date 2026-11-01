<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerVoucher extends Model
{
    use HasFactory;

    protected $table = 'customer_vouchers';

    protected $fillable = [
        'user_id',
        'voucher_id',
        'status',
        'claimed_at',
        'used_at',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Relasi ke Pengguna yang mengklaim voucher
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Voucher yang diklaim
     */
    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    /**
     * Relasi ke Catatan Pemakaian / Penukaran Voucher (OrderVoucherRedemption)
     */
    public function redemptions()
    {
        return $this->hasMany(OrderVoucherRedemption::class, 'customer_voucher_id');
    }

    /**
     * Scope untuk voucher yang berstatus aktif (siap digunakan)
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope untuk voucher yang telah terpakai
     */
    public function scopeTerpakai($query)
    {
        return $query->where('status', 'terpakai');
    }

    /**
     * Scope untuk voucher yang sudah kedaluwarsa
     */
    public function scopeKedaluwarsa($query)
    {
        return $query->where('status', 'kedaluwarsa');
    }
}
