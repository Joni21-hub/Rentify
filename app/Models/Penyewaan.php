<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Tambahan: Memanggil model User

class Penyewaan extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'customer_id', 'kode_booking', 'metode_pengambilan',
        'alamat_pengiriman', 'cabang_id', 'total_biaya',
        'total_deposit', 'total_denda', 'status',
        'tanggal_mulai', 'tanggal_selesai', 'tanggal_kembali_aktual',
    ];

    protected $casts = [
        'tanggal_mulai'          => 'date',
        'tanggal_selesai'        => 'date',
        'tanggal_kembali_aktual' => 'date',
        'total_biaya'            => 'decimal:2',
        'total_deposit'          => 'decimal:2',
        'total_denda'            => 'decimal:2',
    ];

    public static function generateKodeBooking(): string
    {
        return 'RNT' . strtoupper(uniqid());
    }

    // PERBAIKAN: Diubah dari Customer::class menjadi User::class
    public function customer() { return $this->belongsTo(User::class, 'user_id'); }
    
    public function cabang()   { return $this->belongsTo(Cabang::class); }
    public function details()  { return $this->hasMany(DetailPenyewaan::class, 'order_id'); }
    public function pembayaran() { return $this->hasOne(Pembayaran::class); }
    public function pengembalian() { return $this->hasOne(Pengembalian::class); }
    public function ulasans()  { return $this->hasMany(Ulasan::class); }
    public function chats()   { return $this->hasMany(Chat::class); }
    public function komplain() { return $this->hasOne(Komplain::class); }

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
}