<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MomentTaggedProduct extends Model
{
    protected $table = 'moment_tagged_products';

    protected $fillable = [
        'moment_id',
        'product_id',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function moment(): BelongsTo
    {
        return $this->belongsTo(Moment::class, 'moment_id');
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
