<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Penyewaan extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'user_id', 'vendor_id', 'customer_id', 'customer_name', 'customer_whatsapp', 'kode_booking',
        'payment_method', 'metode_pengambilan', 'alamat_pengiriman', 'shipping_address', 'shipping_method',
        'shipping_fee', 'kode_unik', 'voucher_id', 'potongan_voucher',
        'cabang_id', 'total_biaya', 'total_price', 'total_deposit', 'total_denda',
        'vendor_earning', 'rentify_fee', 'completed_at',
        'status', 'tanggal_mulai', 'tanggal_selesai', 'tanggal_kembali_aktual',
        'payment_expired_at', 'paid_at',
    ];

    protected $casts = [
        'tanggal_mulai'          => 'date',
        'tanggal_selesai'        => 'date',
        'tanggal_kembali_aktual' => 'date',
        'total_biaya'            => 'decimal:2',
        'total_price'            => 'decimal:2',
        'vendor_earning'         => 'decimal:2',
        'rentify_fee'            => 'decimal:2',
        'total_deposit'          => 'decimal:2',
        'total_denda'            => 'decimal:2',
        'potongan_voucher'       => 'decimal:2',
        'shipping_fee'           => 'decimal:2',
        'kode_unik'              => 'integer',
        'completed_at'           => 'datetime',
        'payment_expired_at'     => 'datetime',
        'paid_at'                => 'datetime',
    ];

    public static function generateKodeBooking(): string
    {
        return 'RNT' . strtoupper(uniqid());
    }

    public function customer() { return $this->belongsTo(User::class, 'user_id'); }
    public function vendor()   { return $this->belongsTo(User::class, 'vendor_id'); }
    public function cabang()   { return $this->belongsTo(Cabang::class); }
    public function details()  { return $this->hasMany(DetailPenyewaan::class, 'order_id'); }
    public function pembayaran() { return $this->hasOne(Pembayaran::class, 'order_id'); }
    public function pengembalian() { return $this->hasOne(Pengembalian::class, 'penyewaan_id'); }
    public function ulasans()  { return $this->hasMany(Ulasan::class, 'order_id'); }
    public function chats()   { return $this->hasMany(Chat::class, 'order_id'); }
    public function komplain() { return $this->hasOne(Komplain::class, 'order_id'); }

    public function isTerlambat(): bool
    {
        return $this->tanggal_kembali_aktual &&
               $this->tanggal_kembali_aktual->isAfter($this->tanggal_selesai);
    }

    public function hitungHariTerlambat(): int
    {
        if (!$this->isTerlambat()) return 0;
        return $this->tanggal_selesai->diffInDays($this->tanggal_kembali_aktual);
    }

    /**
     * Accessor alias untuk total_price -> total_biaya
     */
    public function getTotalPriceAttribute(): float
    {
        return (float) ($this->attributes['total_biaya'] ?? $this->attributes['total_price'] ?? 0);
    }

    public function getShippingAddressAttribute(): ?string
    {
        return $this->attributes['alamat_pengiriman'] ?? null;
    }

    public function getShippingMethodAttribute(): ?string
    {
        return $this->attributes['metode_pengambilan'] ?? null;
    }

    public function getStartRentAttribute()
    {
        return $this->tanggal_mulai;
    }

    public function getEndRentAttribute()
    {
        return $this->tanggal_selesai;
    }

    public function getCustomerNameAttribute(): string
    {
        return $this->customer->name ?? 'Pelanggan';
    }
}
