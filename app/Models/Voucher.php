<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $table = 'vouchers';

    protected $fillable = [
        'vendor_id',
        'kode_voucher',
        'tipe_voucher',
        'funding_source',
        'tipe_diskon',
        'nilai_diskon',
        'maksimal_diskon',
        'minimal_belanja',
        'kuota_total',
        'kuota_terpakai',
        'kuota_reserved',
        'kuota_klaim_total',
        'kuota_klaim_terpakai',
        'anggaran_maksimal_platform',
        'anggaran_reserved',
        'anggaran_terpakai',
        'visibilitas',
        'cakupan_produk',
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
        'anggaran_maksimal_platform' => 'decimal:2',
        'anggaran_reserved' => 'decimal:2',
        'anggaran_terpakai' => 'decimal:2',
        'kuota_total' => 'integer',
        'kuota_terpakai' => 'integer',
        'kuota_reserved' => 'integer',
        'kuota_klaim_total' => 'integer',
        'kuota_klaim_terpakai' => 'integer',
    ];

    // Relasi: Voucher ini milik siapa?
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Relasi ke Barang yang di-whitelist untuk voucher produk_spesifik
     */
    public function barangs()
    {
        return $this->belongsToMany(Barang::class, 'voucher_barangs', 'voucher_id', 'barang_id');
    }

    /**
     * Relasi ke Kategori yang di-whitelist untuk voucher kategori_spesifik
     */
    public function kategoris()
    {
        return $this->belongsToMany(Kategori::class, 'voucher_kategoris', 'voucher_id', 'kategori_id');
    }

    /**
     * Relasi ke Vendor (User) yang di-whitelist untuk voucher vendor spesifik (platform voucher)
     */
    public function vendors()
    {
        return $this->belongsToMany(User::class, 'voucher_vendors', 'voucher_id', 'vendor_id');
    }

    /**
     * Relasi ke klaim voucher pelanggan (CustomerVoucher)
     */
    public function customerVouchers()
    {
        return $this->hasMany(CustomerVoucher::class, 'voucher_id');
    }

    /**
     * Relasi ke riwayat penukaran pesanan (OrderVoucherRedemption)
     */
    public function redemptions()
    {
        return $this->hasMany(OrderVoucherRedemption::class, 'voucher_id');
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
