<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Moment extends Model
{
    use HasFactory;

    protected $table = 'moments';

    protected $fillable = [
        'user_id',
        'media_url',
        'thumbnail_url',
        'media_type',
        'video_duration_seconds',
        'caption',
        'likes_count',
        'comments_count',
        'lifetime_minted_count',
        'bookmarks_count',
        'reports_count',
        'ranking_score',
        'status',
    ];

    protected $casts = [
        'video_duration_seconds' => 'decimal:2',
        'likes_count'            => 'integer',
        'comments_count'         => 'integer',
        'lifetime_minted_count'  => 'integer',
        'bookmarks_count'        => 'integer',
        'reports_count'          => 'integer',
        'ranking_score'          => 'decimal:4',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(MomentLike::class, 'moment_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(MomentComment::class, 'moment_id')->latest();
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(MomentBookmark::class, 'moment_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(MomentReport::class, 'moment_id');
    }

    public function moderationQueue(): HasOne
    {
        return $this->hasOne(MomentModerationQueue::class, 'moment_id');
    }

    public function taggedProducts(): HasMany
    {
        return $this->hasMany(MomentTaggedProduct::class, 'moment_id')->orderBy('display_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Barang::class, 'moment_tagged_products', 'moment_id', 'product_id')
            ->withPivot('display_order')
            ->withTimestamps()
            ->orderByPivot('display_order');
    }

    public function aiTagSuggestions(): HasMany
    {
        return $this->hasMany(MomentAiTagSuggestion::class, 'moment_id');
    }

    public function coinLedgers(): HasMany
    {
        return $this->hasMany(MomentCoinLedger::class, 'moment_id');
    }

    public function isLikedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function isBookmarkedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->bookmarks()->where('user_id', $user->id)->exists();
    }

    /**
     * Hitung skor ranking gravitasi berbasis interaksi dan peluruhan waktu (recency decay).
     * Formula: ((likes_count * 0.6) + (bookmarks_count * 0.4)) / POW((TIMESTAMPDIFF(HOUR, created_at, NOW()) + 2), 1.5)
     */
    public function calculateRankingScore(): float
    {
        $hours = $this->created_at ? $this->created_at->diffInHours(now()) : 0;
        $gravity = pow(($hours + 2), 1.5);
        $score = (($this->likes_count * 0.6) + ($this->bookmarks_count * 0.4)) / $gravity;

        return round($score, 4);
    }

    /**
     * Perbarui dan simpan skor ranking gravitasi saat ini.
     */
    public function updateRankingScore(): self
    {
        $this->ranking_score = $this->calculateRankingScore();
        $this->save();

        return $this;
    }
}
