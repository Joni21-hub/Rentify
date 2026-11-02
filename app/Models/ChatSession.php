<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatSession extends Model
{
    use HasFactory;

    protected $table = 'chat_sessions';

    protected $fillable = [
        'session_token',
        'user_id',
        'channel',
        'phone_number',
        'customer_name',
        'status',
        'metadata',
        'last_interaction_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'last_interaction_at' => 'datetime',
        ];
    }

    /**
     * User associated with this chat session (if authenticated).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Messages belonging to this chat session.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'chat_session_id');
    }

    /**
     * Marketing attributions tied to this chat session.
     */
    public function marketingAttributions(): HasMany
    {
        return $this->hasMany(MarketingAttribution::class, 'chat_session_id');
    }

    /**
     * Scope query for active sessions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope query for finding by session token.
     */
    public function scopeByToken($query, string $token)
    {
        return $query->where('session_token', $token);
    }

    /**
     * Scope query for finding by phone number.
     */
    public function scopeByPhone($query, string $phone)
    {
        return $query->where('phone_number', $phone);
    }
}
