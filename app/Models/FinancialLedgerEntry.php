<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialLedgerEntry extends Model
{
    use HasFactory;

    protected $table = 'financial_ledger_entries';

    public const TYPE_DEBIT = 'debit';
    public const TYPE_CREDIT = 'credit';

    public const CATEGORY_PLATFORM_COMMISSION = 'platform_commission';
    public const CATEGORY_VENDOR_PAYOUT = 'vendor_payout';
    public const CATEGORY_PLATFORM_VOUCHER_EXPENSE = 'platform_voucher_expense';
    public const CATEGORY_VENDOR_VOUCHER_DEDUCTION = 'vendor_voucher_deduction';
    public const CATEGORY_VENDOR_AD_CPC_DEDUCTION = 'vendor_ad_cpc_deduction';
    public const CATEGORY_VENDOR_AD_PPB_COMMISSION = 'vendor_ad_ppb_commission';
    public const CATEGORY_VENDOR_AD_FLAT_FEE_CHARGE = 'vendor_ad_flat_fee_charge';
    public const CATEGORY_COD_COMMISSION_RECEIVABLE = 'cod_commission_receivable';
    public const CATEGORY_FLOOR_LIMIT_SURPLUS_ADJUSTMENT = 'floor_limit_surplus_adjustment';

    protected $fillable = [
        'entry_type',
        'category',
        'amount',
        'currency',
        'user_id',
        'order_id',
        'campaign_id',
        'pembayaran_id',
        'reference_id',
        'description',
        'running_balance',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'running_balance' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    /**
     * User profile linked to this ledger entry (vendor or customer).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Order linked to this financial transaction (null for direct ad clicks or system adjustments).
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Penyewaan::class, 'order_id');
    }

    /**
     * Associated payment record.
     */
    public function pembayaran(): BelongsTo
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id');
    }

    /**
     * Scope for debits.
     */
    public function scopeDebits($query)
    {
        return $query->where('entry_type', self::TYPE_DEBIT);
    }

    /**
     * Scope for credits.
     */
    public function scopeCredits($query)
    {
        return $query->where('entry_type', self::TYPE_CREDIT);
    }

    /**
     * Scope query by ledger category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope query by user ID.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope query by order ID.
     */
    public function scopeForOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope query by campaign ID.
     */
    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Balance helper: calculates net ledger balance for a user (credits - debits).
     */
    public static function calculateUserBalance(int $userId): float
    {
        $credits = (float) static::forUser($userId)->credits()->sum('amount');
        $debits = (float) static::forUser($userId)->debits()->sum('amount');

        return round($credits - $debits, 2);
    }

    /**
     * Balance helper: calculates total platform commission collected.
     */
    public static function calculatePlatformCommissionTotal(): float
    {
        return (float) static::byCategory(self::CATEGORY_PLATFORM_COMMISSION)->sum('amount');
    }

    /**
     * Balance helper: calculates cumulative ad spend for a given campaign.
     */
    public static function calculateCampaignSpend(int $campaignId): float
    {
        return (float) static::forCampaign($campaignId)->sum('amount');
    }
}
