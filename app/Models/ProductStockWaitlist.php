<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStockWaitlist extends Model
{
    public $timestamps = false;

    protected $table = 'product_stock_waitlists';

    protected $fillable = [
        'user_id',
        'product_id',
        'status',
        'created_at',
        'notified_at',
    ];

    protected $casts = [
        'created_at'  => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'product_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'product_id');
    }
}
