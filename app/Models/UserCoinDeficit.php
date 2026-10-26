<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCoinDeficit extends Model
{
    protected $table = 'user_coin_deficits';

    protected $fillable = [
        'user_id',
        'deficit_coins',
        'last_settled_at',
    ];

    protected $casts = [
        'deficit_coins'   => 'integer',
        'last_settled_at' => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
