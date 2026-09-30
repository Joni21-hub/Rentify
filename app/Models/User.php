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
}
