<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Komplain extends Model
{
    use HasFactory;

    protected $table = 'komplains';

    protected $fillable = [
        'order_id',
        'user_id',
        'alasan',
        'bukti_foto',
        'status_penyelesaian',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Penyewaan::class, 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope untuk komplain/sengketa yang masih aktif belum tuntas diselesaikan.
     */
    public function scopeActiveDisputes($query)
    {
        return $query->whereNotIn('status_penyelesaian', ['Selesai', 'Ditolak']);
    }
}

