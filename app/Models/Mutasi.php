<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mutasi extends Model
{
    use HasFactory;

    protected $table = 'mutasis';

    protected $fillable = [
        'order_id',
        'app_name',
        'package_name',
        'tipe',
        'nominal',
        'sender',
        'title',
        'content',
        'status',
        'catatan',
        'raw_payload',
    ];

    protected $casts = [
        'nominal'     => 'decimal:2',
        'raw_payload' => 'array',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Penyewaan::class, 'order_id');
    }
}
