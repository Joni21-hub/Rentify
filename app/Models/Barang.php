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
        'stok_total', 'status', 'status_barang', 'is_approved', 'approved_at', 'cover_photo',
        'is_delivery_supported', 'alamat', 'latitude', 'longitude', 'spesifikasi'
    ];

    protected $appends = [
        'cover_photo_url',
        'is_cover_video',
        'harga_sewa_customer',
    ];

    protected $casts = [
        'spesifikasi'       => 'array',
        'harga_sewa_harian' => 'decimal:2',
        'deposit'           => 'decimal:2',
        'denda_per_hari'    => 'decimal:2',
        'is_approved'       => 'boolean',
        'approved_at'       => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     * Membersihkan berkas media di Cloudinary dan penyimpanan lokal saat produk dihapus.
     */
    protected static function booted(): void
    {
        static::deleting(function (Barang $barang) {
            // 1. Hapus cover photo produk dari Cloudinary atau disk lokal
            if (!empty($barang->cover_photo)) {
                \App\Services\StorageService::deleteFile($barang->cover_photo);
            }

            // 2. Hapus seluruh foto galeri produk dari Cloudinary dan disk lokal
            foreach ($barang->fotos as $foto) {
                if (!empty($foto->foto_path)) {
                    \App\Services\StorageService::deleteFile($foto->foto_path);
                }
                $foto->delete();
            }
        });
    }

    // ─── Accessors ────────────────────────────────────

    /**
     * Accessor untuk URL Cover Photo dengan Fallback Media
     */
    public function getCoverPhotoUrlAttribute(): string
    {
        return \App\Services\StorageService::getBarangImageUrl($this);
    }

    /**
     * Memeriksa apakah berkas cover berupa video
     */
    public function getIsCoverVideoAttribute(): bool
    {
        return \App\Services\StorageService::isVideoMedia($this->cover_photo);
    }

    /**
     * Alias getImageUrlAttribute untuk standardisasi template & controller
     */
    public function getImageUrlAttribute(): string
    {
        return $this->cover_photo_url;
    }

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

    public function momentTaggedProducts()
    {
        return $this->hasMany(MomentTaggedProduct::class, 'product_id');
    }

    public function moments()
    {
        return $this->belongsToMany(Moment::class, 'moment_tagged_products', 'product_id', 'moment_id')
            ->withPivot('display_order')
            ->withTimestamps();
    }

    public function stockWaitlists()
    {
        return $this->hasMany(ProductStockWaitlist::class, 'product_id');
    }

    public function details()
    {
        return $this->hasMany(DetailPenyewaan::class, 'product_id');
    }

    public function detailPenyewaan()
    {
        return $this->hasMany(DetailPenyewaan::class, 'product_id');
    }

    public function ulasans()
    {
        return $this->hasMany(Ulasan::class, 'barang_id');
    }

    /**
     * Rata-rata rating bintang produk (1 - 5)
     */
    public function getRatingRataRataAttribute(): float
    {
        if ($this->relationLoaded('ulasans')) {
            $avg = $this->ulasans->avg('rating');
            return $avg ? round((float) $avg, 1) : 0.0;
        }
        return round((float) ($this->ulasans()->avg('rating') ?? 0), 1);
    }

    /**
     * Alias getAverageRatingAttribute
     */
    public function getAverageRatingAttribute(): float
    {
        return $this->rating_rata_rata;
    }

    /**
     * Total ulasan produk
     */
    public function getTotalUlasanAttribute(): int
    {
        if ($this->relationLoaded('ulasans')) {
            return $this->ulasans->count();
        }
        return (int) $this->ulasans()->count();
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

    /**
     * Menghitung tanggal-tanggal di mana stok barang ini sudah habis / penuh dibooking.
     * Sesuai aturan: jika total akumulasi unit yang disewa pada tanggal tersebut >= stok_total,
     * maka tanggal tersebut berstatus FULL BOOKED (ditampilkan dengan efek glass di kalender).
     *
     * @param int $horizonDays Jumlah hari ke depan yang dicek (default 120 hari)
     * @return array List tanggal 'YYYY-MM-DD' yang penuh dibooking
     */
    public function getFullyBookedDates(int $horizonDays = 120): array
    {
        $stokTotal = (int) ($this->stok_total ?? 0);
        if ($stokTotal <= 0) {
            return [];
        }

        $today = now()->startOfDay();
        $horizon = now()->copy()->addDays($horizonDays)->endOfDay();

        $activeBookings = \Illuminate\Support\Facades\DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.product_id', $this->id)
            ->whereNotIn('orders.status', ['Selesai', 'Batal', 'Dibatalkan', 'Ditolak', 'CANCELLED', 'EXPIRED'])
            ->where(function ($query) use ($today) {
                $todayDate = $today->toDateString();
                $query->whereDate('orders.tanggal_selesai', '>=', $todayDate)
                      ->orWhereDate('orders.end_rent', '>=', $todayDate);
            })
            ->select([
                'order_items.quantity',
                \Illuminate\Support\Facades\DB::raw('COALESCE(orders.start_rent, orders.tanggal_mulai) as start_rent_time'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(orders.end_rent, orders.tanggal_selesai) as end_rent_time'),
            ])
            ->get();

        $dailyUsage = [];

        foreach ($activeBookings as $booking) {
            if (!$booking->start_rent_time || !$booking->end_rent_time) {
                continue;
            }

            $orderStart = \Carbon\Carbon::parse($booking->start_rent_time)->startOfDay();
            $orderEnd = \Carbon\Carbon::parse($booking->end_rent_time)->startOfDay();
            $qty = max(1, (int) $booking->quantity);

            $curr = $orderStart->copy();
            while ($curr->lte($orderEnd)) {
                if ($curr->gte($today) && $curr->lte($horizon)) {
                    $key = $curr->format('Y-m-d');
                    $dailyUsage[$key] = ($dailyUsage[$key] ?? 0) + $qty;
                }
                $curr->addDay();
            }
        }

        $fullyBooked = [];
        foreach ($dailyUsage as $dateKey => $usedQty) {
            if ($usedQty >= $stokTotal) {
                $fullyBooked[] = $dateKey;
            }
        }

        return array_values(array_unique($fullyBooked));
    }

    /**
     * Ringkasan stok harian (terpakai, sisa, status) untuk rentang waktu ke depan.
     */
    public function getBookedDatesInfo(int $horizonDays = 120): array
    {
        $stokTotal = (int) ($this->stok_total ?? 0);
        $today = now()->startOfDay();
        $horizon = now()->copy()->addDays($horizonDays)->endOfDay();

        $activeBookings = \Illuminate\Support\Facades\DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.product_id', $this->id)
            ->whereNotIn('orders.status', ['Selesai', 'Batal', 'Dibatalkan', 'Ditolak', 'CANCELLED', 'EXPIRED'])
            ->where(function ($query) use ($today) {
                $todayDate = $today->toDateString();
                $query->whereDate('orders.tanggal_selesai', '>=', $todayDate)
                      ->orWhereDate('orders.end_rent', '>=', $todayDate);
            })
            ->select([
                'order_items.quantity',
                \Illuminate\Support\Facades\DB::raw('COALESCE(orders.start_rent, orders.tanggal_mulai) as start_rent_time'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(orders.end_rent, orders.tanggal_selesai) as end_rent_time'),
            ])
            ->get();

        $dailyUsage = [];
        foreach ($activeBookings as $booking) {
            if (!$booking->start_rent_time || !$booking->end_rent_time) {
                continue;
            }
            $orderStart = \Carbon\Carbon::parse($booking->start_rent_time)->startOfDay();
            $orderEnd = \Carbon\Carbon::parse($booking->end_rent_time)->startOfDay();
            $qty = max(1, (int) $booking->quantity);

            $curr = $orderStart->copy();
            while ($curr->lte($orderEnd)) {
                if ($curr->gte($today) && $curr->lte($horizon)) {
                    $key = $curr->format('Y-m-d');
                    $dailyUsage[$key] = ($dailyUsage[$key] ?? 0) + $qty;
                }
                $curr->addDay();
            }
        }

        $info = [];
        foreach ($dailyUsage as $dateKey => $usedQty) {
            $remaining = max(0, $stokTotal - $usedQty);
            $info[$dateKey] = [
                'used' => $usedQty,
                'total' => $stokTotal,
                'remaining' => $remaining,
                'is_full' => $usedQty >= $stokTotal,
            ];
        }

        return $info;
    }
}