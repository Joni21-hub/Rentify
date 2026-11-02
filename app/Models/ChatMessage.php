<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';

    protected $fillable = [
        'chat_session_id',
        'sender_type',
        'sender_id',
        'direction',
        'message_type',
        'message',
        'media_url',
        'whatsapp_message_id',
        'status',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'raw_payload' => 'array',
        ];
    }

    /**
     * Parent chat session.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }

    /**
     * Sender user if sent by an authenticated user.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Scope for inbound messages.
     */
    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    /**
     * Scope for outbound messages.
     */
    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }
}
