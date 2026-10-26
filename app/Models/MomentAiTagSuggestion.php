<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MomentAiTagSuggestion extends Model
{
    public $timestamps = false;

    protected $table = 'moment_ai_tag_suggestions';

    protected $fillable = [
        'moment_id',
        'product_id',
        'confidence_score',
        'created_at',
        'expires_at',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:4',
        'created_at'       => 'datetime',
        'expires_at'       => 'datetime',
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
