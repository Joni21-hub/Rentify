<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingConsent extends Model
{
    use HasFactory;

    protected $table = 'marketing_consents';

    protected $fillable = [
        'user_id',
        'phone_number',
        'consent_type',
        'is_opted_in',
        'opted_in_at',
        'opted_out_at',
        'source',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_opted_in' => 'boolean',
            'opted_in_at' => 'datetime',
            'opted_out_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * User associated with this marketing consent.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope query for opted-in records.
     */
    public function scopeOptedIn($query)
    {
        return $query->where('is_opted_in', true);
    }

    /**
     * Scope query for opted-out records.
     */
    public function scopeOptedOut($query)
    {
        return $query->where('is_opted_in', false);
    }

    /**
     * Scope query for promotional consent.
     */
    public function scopePromotional($query)
    {
        return $query->where('consent_type', 'promotional');
    }

    /**
     * Scope query for transactional consent.
     */
    public function scopeTransactional($query)
    {
        return $query->where('consent_type', 'transactional');
    }

    /**
     * Scope query for social proof consent.
     */
    public function scopeSocialProof($query)
    {
        return $query->where('consent_type', 'social_proof');
    }
}
