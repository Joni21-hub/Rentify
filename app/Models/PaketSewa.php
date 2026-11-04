<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketSewa extends Model
{
    use HasFactory;

    protected $table = 'paket_sewa';

    protected $fillable = [
        'vendor_id',
        'cabang_id',
        'nama',
        'slug',
        'deskripsi',
        'cover_photo',
        'harga_sewa_harian_paket',
        'status',
        'is_delivery_supported',
    ];

    protected $casts = [
        'harga_sewa_harian_paket' => 'decimal:2',
        'is_delivery_supported'   => 'boolean',
    ];

    /**
     * Relasi ke Vendor (User)
     */
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Relasi ke Cabang Vendor
     */
    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    /**
     * Relasi ke daftar item/komponen penyusun paket sewa
     */
    public function items()
    {
        return $this->hasMany(PaketSewaItem::class, 'paket_sewa_id');
    }

    /**
     * Relasi ke line items pesanan yang memesan paket ini
     */
    public function orderItems()
    {
        return $this->hasMany(DetailPenyewaan::class, 'paket_sewa_id');
    }

    /**
     * Relasi ke ulasan pelanggan untuk paket ini
     */
    public function ulasans()
    {
        return $this->hasMany(Ulasan::class, 'paket_sewa_id');
    }

    /**
     * Relasi Many-to-Many ke model Barang melalui tabel pivot paket_sewa_items
     */
    public function barangs()
    {
        return $this->belongsToMany(Barang::class, 'paket_sewa_items', 'paket_sewa_id', 'barang_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Accessor Harga Sewa Customer (Markup 5%)
     */
    public function getHargaSewaCustomerAttribute(): float
    {
        return round((float)$this->harga_sewa_harian_paket * 1.05, 2);
    }

    /**
     * Requirement 3.2:
     * Accessor deposit agregat paket sewa (total deposit dari seluruh item penyusun x kuantitas).
     */
    public function getDepositAttribute(): float
    {
        return (float) $this->items()->with('barang')->get()->sum(function ($item) {
            return (float) ($item->barang?->deposit ?? 0) * (int) $item->quantity;
        });
    }

    /**
     * Requirement 3.2:
     * Accessor denda harian agregat paket sewa (total denda per hari dari seluruh item penyusun x kuantitas).
     */
    public function getDendaPerHariAttribute(): float
    {
        return (float) $this->items()->with('barang')->get()->sum(function ($item) {
            return (float) ($item->barang?->denda_per_hari ?? 0) * (int) $item->quantity;
        });
    }

    public function getTotalDepositAttribute(): float
    {
        return $this->deposit;
    }

    public function getTotalDendaPerHariAttribute(): float
    {
        return $this->denda_per_hari;
    }

    /**
     * Requirement 3.2:
     * Menghitung ulang deposit dan denda paket sewa berdasarkan barang penyusun.
     */
    public function recalculateDepositAndDenda(): array
    {
        return [
            'deposit'        => $this->deposit,
            'denda_per_hari' => $this->denda_per_hari,
        ];
    }
}
