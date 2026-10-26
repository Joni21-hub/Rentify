<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MomentModerationQueue extends Model
{
    protected $table = 'moment_moderation_queue';

    protected $fillable = [
        'moment_id',
        'status',
        'total_reports',
        'last_reported_at',
        'reviewed_by',
        'resolution_notes',
    ];

    protected $casts = [
        'total_reports'    => 'integer',
        'last_reported_at' => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    public function moment(): BelongsTo
    {
        return $this->belongsTo(Moment::class, 'moment_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
