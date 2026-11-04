<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';

    protected $fillable = [
        'vendor_id', 'cabang_id', 'kategori_id', 'nama', 'slug', 'deskripsi', 
        'harga_sewa_harian', 'deposit', 'denda_per_hari', 'kondisi', 
        'is_paket', 'stok_total', 'berat_gram', 'status', 'status_barang', 'is_approved', 'approved_at', 'cover_photo',
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
        'berat_gram'        => 'integer',
        'is_approved'       => 'boolean',
        'is_paket'          => 'boolean',
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

    /**
     * Accessor stok_total dinamis: jika produk adalah paket (is_paket = 1),
     * stok total dihitung dari kapasitas terendah komponen pembentuknya.
     */
    public function getStokTotalAttribute($value)
    {
        if (!empty($this->attributes['is_paket'])) {
            $items = $this->relationLoaded('paketItems')
                ? $this->paketItems
                : $this->paketItems()->with('barang')->get();

            if ($items->isEmpty()) {
                return 0;
            }

            $minKapasitas = null;
            foreach ($items as $item) {
                $barang = $item->barang;
                $qtyPerPaket = max(1, (int) $item->quantity);
                $stokKomponen = $barang ? (int) ($barang->getRawOriginal('stok_total') ?? $barang->stok_total ?? 0) : 0;
                $kapasitas = (int) floor($stokKomponen / $qtyPerPaket);
                $minKapasitas = ($minKapasitas === null) ? $kapasitas : min($minKapasitas, $kapasitas);
            }

            return max(0, (int) ($minKapasitas ?? 0));
        }

        return (int) ($value ?? 0);
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

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function paketItems()
    {
        return $this->hasMany(PaketSewaItem::class, 'paket_id');
    }

    public function paketSewaItems()
    {
        return $this->hasMany(PaketSewaItem::class, 'barang_id');
    }

    public function paketSewas()
    {
        return $this->belongsToMany(PaketSewa::class, 'paket_sewa_items', 'barang_id', 'paket_sewa_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function orderItemComponents()
    {
        return $this->hasMany(OrderItemComponent::class, 'barang_id');
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

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'voucher_barangs', 'barang_id', 'voucher_id');
    }

    public function momentTaggedProducts()
    {
        return $this->hasMany(MomentTaggedProduct::class, 'product_id');
    }

    /**
     * Kampanye Iklan Promoted Listing untuk Produk Ini
     */
    public function adCampaigns()
    {
        return $this->hasMany(VendorAdCampaign::class, 'barang_id');
    }

    /**
     * Catatan Klik Iklan untuk Produk Ini
     */
    public function adClicks()
    {
        return $this->hasMany(VendorAdClick::class, 'barang_id');
    }

    /**
     * Ulasan Social Proof Terverifikasi untuk Produk Ini
     */
    public function socialProofReviews()
    {
        return $this->hasMany(SocialProofReview::class, 'barang_id');
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
     * Jam operasional sewa harian Rentify.
     */
    public const OPERATIONAL_HOURS = [
        '08:00', '09:00', '10:00', '11:00', '12:00', '13:00',
        '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'
    ];

    /**
     * Menghitung ketersediaan stok sewa harian dan jam operasional untuk rentang horizon hari ke depan.
     * Menggunakan kalkulasi interval overlap exact:
     * - Suatu jam pada tanggal tertentu bentrok jika: start_order < jam_selesai_sewa && end_order > jam_mulai_sewa.
     * - Suatu tanggal berstatus FULL BOOKED (ditampilkan efek glass) jika SELURUH jam operasional pada hari tersebut penuh.
     * - Jika pesanan sebelumnya selesai pada jam 08:00, maka jam setelahnya (misal 09:00) pada hari tersebut tetap TERSEDIA.
     *
     * @param int $horizonDays Jumlah hari ke depan yang dicek (default 120 hari)
     * @return array [fully_booked_dates, booked_slots, booked_info]
     */
    public function getAvailabilityData(int $horizonDays = 120): array
    {
        $stokTotal = (int) ($this->stok_total ?? 0);
        if ($stokTotal <= 0) {
            return [
                'fully_booked_dates' => [],
                'booked_slots' => [],
                'booked_info' => [],
            ];
        }

        $today = now()->startOfDay();
        $horizon = now()->copy()->addDays($horizonDays)->endOfDay();

        $activeBookings = \Illuminate\Support\Facades\DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.product_id', $this->id)
            ->whereNotIn('orders.status', ['Selesai', 'Batal', 'Dibatalkan', 'Ditolak', 'CANCELLED', 'EXPIRED', 'Menunggu Ulasan'])
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

        $parsedBookings = [];
        $relevantDates = [];

        foreach ($activeBookings as $booking) {
            if (!$booking->start_rent_time || !$booking->end_rent_time) {
                continue;
            }

            $start = \Carbon\Carbon::parse($booking->start_rent_time);
            $end = \Carbon\Carbon::parse($booking->end_rent_time);
            $qty = max(1, (int) $booking->quantity);

            $parsedBookings[] = [
                'start' => $start,
                'end'   => $end,
                'qty'   => $qty,
            ];

            $curr = $start->copy()->startOfDay();
            $endDay = $end->copy()->startOfDay();
            while ($curr->lte($endDay)) {
                if ($curr->gte($today) && $curr->lte($horizon)) {
                    $relevantDates[$curr->format('Y-m-d')] = true;
                }
                $curr->addDay();
            }
        }

        $fullyBookedDates = [];
        $bookedSlots = [];
        $bookedInfo = [];

        foreach (array_keys($relevantDates) as $dateKey) {
            $slotsBookedOnThisDate = [];
            $maxUsedInAnySlot = 0;

            foreach (self::OPERATIONAL_HOURS as $slot) {
                $slotStart = \Carbon\Carbon::parse($dateKey . ' ' . $slot);
                $slotEnd = $slotStart->copy()->addDay(); // Standar peminjaman minimal 1 hari (24 jam)

                $usedQty = 0;
                foreach ($parsedBookings as $pb) {
                    // Formula overlap interval: pb.start < slotEnd AND pb.end > slotStart
                    if ($pb['start']->lt($slotEnd) && $pb['end']->gt($slotStart)) {
                        $usedQty += $pb['qty'];
                    }
                }

                if ($usedQty > $maxUsedInAnySlot) {
                    $maxUsedInAnySlot = $usedQty;
                }

                if ($usedQty >= $stokTotal) {
                    $slotsBookedOnThisDate[] = $slot;
                }
            }

            $isFull = (count($slotsBookedOnThisDate) === count(self::OPERATIONAL_HOURS));
            if ($isFull) {
                $fullyBookedDates[] = $dateKey;
            }

            if (!empty($slotsBookedOnThisDate)) {
                $bookedSlots[$dateKey] = $slotsBookedOnThisDate;
            }

            $bookedInfo[$dateKey] = [
                'used' => $maxUsedInAnySlot,
                'total' => $stokTotal,
                'remaining' => max(0, $stokTotal - $maxUsedInAnySlot),
                'is_full' => $isFull,
                'booked_slots' => $slotsBookedOnThisDate,
            ];
        }

        sort($fullyBookedDates);

        return [
            'fully_booked_dates' => array_values(array_unique($fullyBookedDates)),
            'booked_slots' => $bookedSlots,
            'booked_info' => $bookedInfo,
        ];
    }

    /**
     * Menghitung tanggal-tanggal di mana stok barang ini sudah habis / penuh dibooking di semua slot jam.
     * Sesuai aturan: tanggal berstatus FULL BOOKED (ditampilkan dengan efek glass di kalender)
     * hanya jika seluruh slot jam pada tanggal tersebut sudah penuh terisi.
     *
     * @param int $horizonDays Jumlah hari ke depan yang dicek (default 120 hari)
     * @return array List tanggal 'YYYY-MM-DD' yang penuh dibooking
     */
    public function getFullyBookedDates(int $horizonDays = 120): array
    {
        return $this->getAvailabilityData($horizonDays)['fully_booked_dates'];
    }

    /**
     * Mengambil daftar slot jam yang sudah penuh per tanggal.
     * Contoh: ['2026-11-04' => ['08:00', '09:00', ...]]
     */
    public function getBookedSlots(int $horizonDays = 120): array
    {
        return $this->getAvailabilityData($horizonDays)['booked_slots'];
    }

    /**
     * Ringkasan stok harian (terpakai, sisa, status, slot terisi) untuk rentang waktu ke depan.
     */
    public function getBookedDatesInfo(int $horizonDays = 120): array
    {
        return $this->getAvailabilityData($horizonDays)['booked_info'];
    }

    /**
     * Menghitung total kuantitas barang ini yang sedang dibooking / disewa pada rentang waktu tertentu.
     * Memperhitungkan pesanan langsung maupun pesanan paket bundling yang mengandung barang ini.
     *
     * @param mixed $start Waktu mulai sewa (Carbon|string)
     * @param mixed $end Waktu selesai sewa (Carbon|string)
     * @return int
     */
    public function getBookedQuantityInPeriod($start, $end): int
    {
        $startRent = \Carbon\Carbon::parse($start);
        $endRent   = \Carbon\Carbon::parse($end);

        $inactiveStatuses = ['Batal', 'Dibatalkan', 'Ditolak', 'CANCELLED', 'EXPIRED'];

        // 1. Pesanan langsung untuk barang ini
        $directBookings = \Illuminate\Support\Facades\DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.product_id', $this->id)
            ->whereNotIn('orders.status', $inactiveStatuses)
            ->select([
                'order_items.quantity',
                \Illuminate\Support\Facades\DB::raw('COALESCE(orders.start_rent, orders.tanggal_mulai) as start_rent_time'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(orders.end_rent, orders.tanggal_selesai) as end_rent_time'),
            ])
            ->get();

        $totalBooked = 0;
        foreach ($directBookings as $b) {
            if (!$b->start_rent_time || !$b->end_rent_time) continue;
            $bStart = \Carbon\Carbon::parse($b->start_rent_time);
            $bEnd   = \Carbon\Carbon::parse($b->end_rent_time);
            if ($bStart->lt($endRent) && $bEnd->gt($startRent)) {
                $totalBooked += max(0, (int) $b->quantity);
            }
        }

        // 2. Pesanan paket bundling yang mengandung barang ini sebagai komponen
        if (\Illuminate\Support\Facades\Schema::hasTable('paket_sewa_items')) {
            $column = \Illuminate\Support\Facades\Schema::hasColumn('paket_sewa_items', 'paket_id') ? 'paket_id' : 'paket_sewa_id';
            $packageItems = \Illuminate\Support\Facades\DB::table('paket_sewa_items')
                ->where('barang_id', $this->id)
                ->get();

            foreach ($packageItems as $pi) {
                $packageId = $pi->paket_id ?? $pi->paket_sewa_id ?? null;
                if (!$packageId) continue;
                $qtyPerPaket = max(1, (int) $pi->quantity);

                $packageBookings = \Illuminate\Support\Facades\DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('order_items.product_id', $packageId)
                    ->whereNotIn('orders.status', $inactiveStatuses)
                    ->select([
                        'order_items.quantity',
                        \Illuminate\Support\Facades\DB::raw('COALESCE(orders.start_rent, orders.tanggal_mulai) as start_rent_time'),
                        \Illuminate\Support\Facades\DB::raw('COALESCE(orders.end_rent, orders.tanggal_selesai) as end_rent_time'),
                    ])
                    ->get();

                foreach ($packageBookings as $pb) {
                    if (!$pb->start_rent_time || !$pb->end_rent_time) continue;
                    $pbStart = \Carbon\Carbon::parse($pb->start_rent_time);
                    $pbEnd   = \Carbon\Carbon::parse($pb->end_rent_time);
                    if ($pbStart->lt($endRent) && $pbEnd->gt($startRent)) {
                        $totalBooked += max(0, (int) $pb->quantity) * $qtyPerPaket;
                    }
                }
            }
        }

        return $totalBooked;
    }

    /**
     * Menghitung sisa stok barang yang tersedia untuk disewa pada rentang waktu tertentu.
     * Jika produk adalah paket bundling (is_paket = 1), ketersediaan dihitung dari
     * ketersediaan seluruh komponennya pada rentang waktu yang sama.
     *
     * @param mixed $start Waktu mulai sewa (Carbon|string)
     * @param mixed $end Waktu selesai sewa (Carbon|string)
     * @return int
     */
    public function getAvailableStockInPeriod($start, $end): int
    {
        $startRent = \Carbon\Carbon::parse($start);
        $endRent   = \Carbon\Carbon::parse($end);

        if (!empty($this->is_paket)) {
            $components = $this->relationLoaded('paketItems')
                ? $this->paketItems
                : $this->paketItems()->with('barang')->get();

            if ($components->isEmpty()) {
                return 0;
            }

            $minAvailable = null;
            foreach ($components as $component) {
                $barangKomponen = $component->barang;
                if (!$barangKomponen) {
                    return 0;
                }
                $qtyNeeded = max(1, (int) $component->quantity);
                $availableKomponen = $barangKomponen->getAvailableStockInPeriod($startRent, $endRent);
                $possible = (int) floor($availableKomponen / $qtyNeeded);

                $minAvailable = ($minAvailable === null) ? $possible : min($minAvailable, $possible);
            }

            return max(0, (int) ($minAvailable ?? 0));
        }

        $booked = $this->getBookedQuantityInPeriod($startRent, $endRent);
        $stokTotal = (int) ($this->getRawOriginal('stok_total') ?? $this->stok_total ?? 0);

        return max(0, $stokTotal - $booked);
    }
}