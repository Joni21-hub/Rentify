<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorAdClick extends Model
{
    use HasFactory;

    protected $table = 'vendor_ad_clicks';

    protected $fillable = [
        'campaign_id',
        'barang_id',
        'user_id',
        'chat_session_id',
        'session_token',
        'ip_hash',
        'user_agent',
        'interaction_type',
        'is_billable',
        'deduplication_reason',
        'charged_amount',
        'attributed_order_id',
        'attributed_at',
        'clicked_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_billable' => 'boolean',
            'charged_amount' => 'decimal:2',
            'attributed_at' => 'datetime',
            'clicked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Parent ad campaign.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(VendorAdCampaign::class, 'campaign_id');
    }

    /**
     * Clicked catalog item.
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    /**
     * User who performed the click (if authenticated).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Chat session where click occurred.
     */
    public function chatSession(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }

    /**
     * Order attributed to this click (e.g. PPB 7-day attribution).
     */
    public function attributedOrder(): BelongsTo
    {
        return $this->belongsTo(Penyewaan::class, 'attributed_order_id');
    }

    /**
     * Scope for billable clicks.
     */
    public function scopeBillable($query)
    {
        return $query->where('is_billable', true);
    }

    /**
     * Scope for non-billable / deduplicated clicks.
     */
    public function scopeNonBillable($query)
    {
        return $query->where('is_billable', false);
    }

    /**
     * Scope for clicks with successfully attributed orders.
     */
    public function scopeAttributed($query)
    {
        return $query->whereNotNull('attributed_order_id');
    }

    /**
     * Scope for clicks within the last 24 hours.
     */
    public function scopeWithin24Hours($query)
    {
        return $query->where('clicked_at', '>=', now()->subHours(24));
    }
}
