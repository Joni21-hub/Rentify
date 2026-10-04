<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'whatsapp',
        'whatsapp_verified_at',
        'password',
        'password_changed_at',
        'role',
        'vendor_name',
        'whatsapp_vendor',
        'vendor_status',
        'latitude',
        'longitude',
        'alamat_lengkap',
        'foto_profil',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'whatsapp_verified_at' => 'datetime',
            'password_changed_at'  => 'datetime',
            'password'             => 'hashed',
        ];
    }

    /**
     * Cek apakah user memiliki email asli (bukan dummy nomor@rentify.local)
     */
    public function hasRealEmail(): bool
    {
        return !empty($this->email) && !str_ends_with($this->email, '@rentify.local');
    }

    /**
     * Kontak utama untuk ditampilkan di UI (Email asli jika ada, jika tidak nomor WhatsApp)
     */
    public function getDisplayContactAttribute(): string
    {
        if ($this->hasRealEmail()) {
            return $this->email;
        }
        if (!empty($this->whatsapp)) {
            // Format nomor telepon menjadi 0831-2592-2565 jika memungkinkan
            return preg_replace('/(\d{4})(\d{4})(\d+)/', '$1-$2-$3', $this->whatsapp);
        }
        return '-';
    }

    /**
     * Email asli untuk UI. Mengembalikan null jika masih dummy @rentify.local
     */
    public function getDisplayEmailAttribute(): ?string
    {
        return $this->hasRealEmail() ? $this->email : null;
    }

    /**
     * Cek apakah user memiliki profil toko / terdaftar sebagai Vendor
     */
    public function isVendor(): bool
    {
        if ($this->role === 'admin') {
            return false;
        }

        return !empty($this->vendor_name) || $this->role === 'vendor';
    }

    /**
     * Cek apakah user berhak berbelanja sebagai Customer (semua akun non-admin)
     */
    public function isCustomer(): bool
    {
        return $this->role !== 'admin';
    }

    /**
     * Cek apakah user memiliki hak akses ganda (Customer + Vendor)
     */
    public function hasDualRole(): bool
    {
        return $this->role !== 'admin' && !empty($this->vendor_name);
    }

    /**
     * URL Foto Profil / Avatar Toko
     */
    public function getFotoProfilUrlAttribute(): ?string
    {
        if (empty($this->foto_profil)) {
            return null;
        }

        if (str_starts_with($this->foto_profil, 'http://') || str_starts_with($this->foto_profil, 'https://')) {
            return $this->foto_profil;
        }

        $cleanPath = str_replace('public/', '', $this->foto_profil);
        if (file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }

        return asset('storage/' . $cleanPath);
    }

    /**
     * Relasi ke Barang Vendor
     */
    public function barangs()
    {
        return $this->hasMany(Barang::class, 'vendor_id');
    }

    /**
     * Relasi ke Voucher Vendor
     */
    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'vendor_id');
    }

    /**
     * Mendapatkan role aktif yang sedang digunakan saat ini
     */
    public function getActiveRoleAttribute(): string
    {
        if (session()->has('active_role')) {
            return session('active_role');
        }

        if ($this->role === 'admin') {
            return 'admin';
        }

        return $this->role;
    }
}

