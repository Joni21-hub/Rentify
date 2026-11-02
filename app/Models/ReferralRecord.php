<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralRecord extends Model
{
    use HasFactory;

    protected $table = 'referral_records';

    public const STATUS_PENDING_ESCROW = 'pending_escrow';
    public const STATUS_MATURED = 'matured';
    public const STATUS_REDEEMED = 'redeemed';
    public const STATUS_VOIDED = 'voided';
    public const STATUS_CLAWBACK = 'clawback';

    public const FRAUD_STATUS_PASSED = 'passed';
    public const FRAUD_STATUS_FLAGGED = 'flagged';
    public const FRAUD_STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'referrer_id',
        'referee_id',
        'referral_code',
        'order_id',
        'status',
        'gross_booking_amount',
        'reward_amount',
        'referee_voucher_id',
        'anti_fraud_score',
        'fraud_check_status',
        'fraud_reasons',
        'escrow_matures_at',
        'clawback_at',
        'is_payout_locked',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'gross_booking_amount' => 'decimal:2',
            'reward_amount' => 'decimal:2',
            'anti_fraud_score' => 'float',
            'is_payout_locked' => 'boolean',
            'escrow_matures_at' => 'datetime',
            'clawback_at' => 'datetime',
            'fraud_reasons' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Referrer user who shared the code.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Referee user who accepted and signed up with the code.
     */
    public function referee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    /**
     * The qualifying order that unlocked referral credits.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Penyewaan::class, 'order_id');
    }

    /**
     * Referee's welcome voucher issued upon referral.
     */
    public function refereeVoucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'referee_voucher_id');
    }

    /**
     * Scope for pending escrow records.
     */
    public function scopePendingEscrow($query)
    {
        return $query->where('status', self::STATUS_PENDING_ESCROW);
    }

    /**
     * Scope for matured credits ready for redemption.
     */
    public function scopeMatured($query)
    {
        return $query->where('status', self::STATUS_MATURED);
    }

    /**
     * Scope for redeemed credits.
     */
    public function scopeRedeemed($query)
    {
        return $query->where('status', self::STATUS_REDEEMED);
    }

    /**
     * Scope for clean non-fraudulent referrals.
     */
    public function scopePassedAntiFraud($query)
    {
        return $query->where('fraud_check_status', self::FRAUD_STATUS_PASSED);
    }

    /**
     * Check if record is currently held in escrow.
     */
    public function isHeldInEscrow(): bool
    {
        return $this->status === self::STATUS_PENDING_ESCROW;
    }

    /**
     * Check if record can be subjected to clawback.
     */
    public function canClawback(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING_ESCROW, self::STATUS_MATURED, self::STATUS_REDEEMED], true);
    }
}
