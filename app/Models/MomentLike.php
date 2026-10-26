<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MomentLike extends Model
{
    public $timestamps = false;

    protected $table = 'moment_likes';

    protected $fillable = [
        'moment_id',
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function moment(): BelongsTo
    {
        return $this->belongsTo(Moment::class, 'moment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
