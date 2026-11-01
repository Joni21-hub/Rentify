<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialProofReview extends Model
{
    use HasFactory;

    protected $table = 'social_proof_reviews';

    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_HELD_PENDING_MEDIATION = 'held_pending_mediation';
    public const STATUS_REVOKED = 'revoked';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'order_id',
        'barang_id',
        'user_id',
        'ulasan_id',
        'rating',
        'review_text',
        'media_urls',
        'sanitized_media_urls',
        'is_consent_granted',
        'consent_granted_at',
        'consent_revoked_at',
        'is_published',
        'status',
        'incentive_reward_amount',
        'incentive_voucher_id',
        'service_recovery_ticket_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'media_urls' => 'array',
            'sanitized_media_urls' => 'array',
            'is_consent_granted' => 'boolean',
            'consent_granted_at' => 'datetime',
            'consent_revoked_at' => 'datetime',
            'is_published' => 'boolean',
            'incentive_reward_amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    /**
     * Rental order associated with this review.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Penyewaan::class, 'order_id');
    }

    /**
     * Reviewed item.
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    /**
     * Customer who wrote the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Linked primary ulasan record.
     */
    public function ulasan(): BelongsTo
    {
        return $this->belongsTo(Ulasan::class, 'ulasan_id');
    }

    /**
     * Issued reward voucher for submitting the review.
     */
    public function incentiveVoucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'incentive_voucher_id');
    }

    /**
     * Scope for published social proof reviews.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope for high ratings (4-5 stars).
     */
    public function scopeHighRating($query)
    {
        return $query->where('rating', '>=', 4);
    }

    /**
     * Scope for reviews requiring service recovery (1-3 stars).
     */
    public function scopeServiceRecoveryRequired($query)
    {
        return $query->where('rating', '<=', 3);
    }

    /**
     * Scope for reviews with active customer consent.
     */
    public function scopeConsentGranted($query)
    {
        return $query->where('is_consent_granted', true);
    }

    /**
     * Check if review is eligible for public display.
     */
    public function isEligibleForPublishing(): bool
    {
        return $this->is_consent_granted && $this->is_published && $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Check if review requires mediation / service recovery hold.
     */
    public function requiresMediation(): bool
    {
        return $this->rating <= 3 || $this->status === self::STATUS_HELD_PENDING_MEDIATION;
    }
}
