<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingAttribution extends Model
{
    use HasFactory;

    protected $table = 'marketing_attributions';

    protected $fillable = [
        'chat_session_id',
        'user_id',
        'order_id',
        'touchpoint_type',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'gclid',
        'fbclid',
        'fbp',
        'fbc',
        'gbraid',
        'wbraid',
        'referral_code',
        'landing_page_url',
        'referrer_url',
        'client_ip_address',
        'client_user_agent',
        'conversion_value',
        'occurred_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'conversion_value' => 'decimal:2',
            'occurred_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Parent chat session if originating from web/whatsapp chat.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }

    /**
     * User profile linked to this touchpoint.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Order linked upon successful conversion.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Penyewaan::class, 'order_id');
    }

    /**
     * Scope for first-touch attribution touchpoints.
     */
    public function scopeFirstTouch($query)
    {
        return $query->where('touchpoint_type', 'first_touch');
    }

    /**
     * Scope for last-click attribution touchpoints.
     */
    public function scopeLastClick($query)
    {
        return $query->where('touchpoint_type', 'last_click');
    }

    /**
     * Scope for conversions.
     */
    public function scopeConverted($query)
    {
        return $query->whereNotNull('order_id');
    }

    /**
     * Scope for filtering by session ID.
     */
    public function scopeBySession($query, int $sessionId)
    {
        return $query->where('chat_session_id', $sessionId);
    }

    /**
     * Scope for filtering by order ID.
     */
    public function scopeByOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }
}
