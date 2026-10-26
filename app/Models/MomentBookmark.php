<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MomentBookmark extends Model
{
    public $timestamps = false;

    protected $table = 'moment_bookmarks';

    protected $fillable = [
        'user_id',
        'moment_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function moment(): BelongsTo
    {
        return $this->belongsTo(Moment::class, 'moment_id');
    }
}
