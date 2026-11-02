<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorAdCampaign extends Model
{
    use HasFactory;

    protected $table = 'vendor_ad_campaigns';

    public const MODEL_CPC = 'cpc';
    public const MODEL_PPB = 'ppb';
    public const MODEL_FLAT_DAILY = 'flat_daily';
    public const MODEL_FLAT_WEEKLY = 'flat_weekly';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_DEPLETED_SUSPENDED = 'depleted_suspended';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'vendor_id',
        'barang_id',
        'campaign_name',
        'billing_model',
        'bid_amount',
        'ppb_commission_rate',
        'daily_budget',
        'total_budget',
        'current_daily_spend',
        'total_spend',
        'start_date',
        'end_date',
        'status',
        'target_keywords',
        'target_categories',
        'impressions_count',
        'clicks_count',
        'bookings_count',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'bid_amount' => 'decimal:2',
            'ppb_commission_rate' => 'decimal:2',
            'daily_budget' => 'decimal:2',
            'total_budget' => 'decimal:2',
            'current_daily_spend' => 'decimal:2',
            'total_spend' => 'decimal:2',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'target_keywords' => 'array',
            'target_categories' => 'array',
            'impressions_count' => 'integer',
            'clicks_count' => 'integer',
            'bookings_count' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * Vendor owner of this campaign.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Promoted item.
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    /**
     * Ad clicks recorded under this campaign.
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(VendorAdClick::class, 'campaign_id');
    }

    /**
     * Financial ledger entries tied to this campaign.
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(FinancialLedgerEntry::class, 'campaign_id');
    }

    /**
     * Scope query for active campaigns.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope query for CPC campaigns.
     */
    public function scopeCpc($query)
    {
        return $query->where('billing_model', self::MODEL_CPC);
    }

    /**
     * Scope query for Pay-per-Booking (PPB) campaigns.
     */
    public function scopePpb($query)
    {
        return $query->where('billing_model', self::MODEL_PPB);
    }

    /**
     * Scope query for Flat-Daily campaigns.
     */
    public function scopeFlatDaily($query)
    {
        return $query->where('billing_model', self::MODEL_FLAT_DAILY);
    }

    /**
     * Scope query for Flat-Weekly campaigns.
     */
    public function scopeFlatWeekly($query)
    {
        return $query->where('billing_model', self::MODEL_FLAT_WEEKLY);
    }

    /**
     * Scope query for specific vendor.
     */
    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Check if campaign is active and eligible for auction ranking.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if campaign has reached budget limits.
     */
    public function hasDepletedBudget(): bool
    {
        if ($this->daily_budget !== null && (float) $this->current_daily_spend >= (float) $this->daily_budget) {
            return true;
        }

        if ($this->total_budget !== null && (float) $this->total_spend >= (float) $this->total_budget) {
            return true;
        }

        return false;
    }
}
