<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'kode_voucher',
        'tipe_diskon',
        'nilai_diskon',
        'maksimal_diskon',
        'minimal_belanja',
        'kuota_total',
        'kuota_terpakai',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    // Relasi: Voucher ini milik siapa?
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}