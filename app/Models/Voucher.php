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

    protected $casts = [
        'is_active' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'nilai_diskon' => 'float',
        'maksimal_diskon' => 'float',
        'minimal_belanja' => 'float',
    ];

    // Relasi: Voucher ini milik siapa?
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    // Accessor aliases untuk kompatibilitas
    public function getCodeAttribute()
    {
        return $this->attributes['kode_voucher'] ?? null;
    }

    public function getDiscountTypeAttribute()
    {
        return ($this->attributes['tipe_diskon'] ?? '') === 'nominal' ? 'fixed' : 'persen';
    }

    public function getDiscountValueAttribute()
    {
        return $this->attributes['nilai_diskon'] ?? 0;
    }

    public function getUsedCountAttribute()
    {
        return $this->attributes['kuota_terpakai'] ?? 0;
    }

    public function getUsageLimitAttribute()
    {
        return $this->attributes['kuota_total'] ?? 0;
    }

    public function getValidUntilAttribute()
    {
        return $this->attributes['tanggal_selesai'] ?? null;
    }
}