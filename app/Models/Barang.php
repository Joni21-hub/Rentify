<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';

   protected $fillable = [
    'vendor_id', 'kategori_id', 'nama', 'slug', 'deskripsi', 
    'harga_sewa_harian', 'deposit', 'denda_per_hari', 'kondisi', 
    'stok_total', 'status', 'is_approved', 'cover_photo',
    'is_delivery_supported', 'alamat', 'latitude', 'longitude', 'spesifikasi'
    ];

    protected $casts = [
        'spesifikasi'       => 'array',
        'harga_sewa_harian' => 'decimal:2',
        'deposit'           => 'decimal:2',
        'denda_per_hari'    => 'decimal:2',
        'is_approved'       => 'boolean',
        'approved_at'       => 'datetime',
    ];

    // ─── Accessors ────────────────────────────────────

    /**
     * Accessor untuk Harga Sewa Markup (ditambah 5% untuk Customer)
     * Cara panggil di View/Controller: $barang->harga_sewa_customer
     */
    public function getHargaSewaCustomerAttribute()
    {
        return $this->harga_sewa_harian + ($this->harga_sewa_harian * 0.05);
    }

    /**
     * Accessor untuk Tarif Bulanan (Khusus Kos / Kamar)
     */
    public function getHargaBulananAttribute()
    {
        return !empty($this->spesifikasi['harga_bulanan']) ? (float)$this->spesifikasi['harga_bulanan'] : null;
    }

    /**
     * Accessor untuk Tarif Bulanan Markup Customer (ditambah 5%)
     */
    public function getHargaBulananCustomerAttribute()
    {
        $harga = $this->harga_bulanan;
        return $harga ? ($harga + ($harga * 0.05)) : null;
    }

    /**
     * Helper Cek Kategori
     */
    public function getIsKosAttribute(): bool
    {
        $namaKat = strtolower($this->kategori->nama ?? '');
        return str_contains($namaKat, 'kos') || str_contains($namaKat, 'kamar');
    }

    public function getIsKendaraanAttribute(): bool
    {
        return str_contains(strtolower($this->kategori->nama ?? ''), 'kendaraan');
    }

    public function getIsElektronikAttribute(): bool
    {
        return str_contains(strtolower($this->kategori->nama ?? ''), 'elektronik');
    }

    public function getIsOutdoorAttribute(): bool
    {
        return str_contains(strtolower($this->kategori->nama ?? ''), 'outdoor');
    }

    public function getIsHaveFunAttribute(): bool
    {
        return str_contains(strtolower($this->kategori->nama ?? ''), 'have fun');
    }

    /**
     * Helper Ambil Fasilitas Kos / Kamar
     */
    public function getFasilitasKosAttribute(): array
    {
        return (array) ($this->spesifikasi['fasilitas'] ?? []);
    }

    // ─── Relationships ────────────────────────────────
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function fotos()
    {
        return $this->hasMany(FotoBarang::class);
    }

    /**
     * KODE BARU: Relasi Tambahan untuk Galeri Foto Detail Customer
     */
    public function fotoBarangs()
    {
        return $this->hasMany(FotoBarang::class, 'barang_id', 'id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function details()
    {
        return $this->hasMany(DetailPenyewaan::class, 'product_id');
    }

    public function detailPenyewaan()
    {
        return $this->hasMany(DetailPenyewaan::class, 'product_id');
    }

    // ─── Scopes ───────────────────────────────────────
    public function scopeApproved($query)
    {
        return $query->where('is_approved', 1)
                     ->where('status', 'tersedia');
    }

    public function scopeByKategori($query, $slug)
    {
        return $query->whereHas('kategori', fn($q) => $q->where('slug', $slug));
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('nama', 'LIKE', "%{$term}%")
                     ->orWhere('deskripsi', 'LIKE', "%{$term}%");
    }

    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('harga_sewa_harian', [$min, $max]);
    }

    // ─── Helpers ──────────────────────────────────────
    public function stokDiCabang(int $cabangId): int
    {
        return $this->stokCabang
                    ->where('cabang_id', $cabangId)
                    ->first()?->stok_tersedia ?? 0;
    }
}