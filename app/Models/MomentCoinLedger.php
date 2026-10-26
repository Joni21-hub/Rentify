<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MomentCoinLedger extends Model
{
    protected $table = 'moment_coin_ledgers';

    protected $fillable = [
        'user_id',
        'interactor_id',
        'moment_id',
        'order_id',
        'interaction_type',
        'coins_amount',
        'remaining_lot_balance',
        'minted_coin',
    ];

    protected $casts = [
        'coins_amount'          => 'integer',
        'remaining_lot_balance' => 'integer',
        'minted_coin'           => 'boolean',
        'created_at'            => 'datetime',
        'updated_at'            => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function interactor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interactor_id');
    }

    public function moment(): BelongsTo
    {
        return $this->belongsTo(Moment::class, 'moment_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Penyewaan::class, 'order_id');
    }
}
