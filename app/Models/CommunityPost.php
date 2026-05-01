<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityPost extends Model
{
    protected $fillable = [
        'user_id', 'title', 'body', 'category', 'likes_count', 'comments_count', 'is_pinned',
        'mentioned_user_ids', 'mentioned_team_ids', 'edited_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'mentioned_user_ids' => 'array',
        'mentioned_team_ids' => 'array',
        'edited_at' => 'datetime',
    ];

    public const CATEGORIES = [
        'idea' => 'Idea',
        'question' => 'Question',
        'discussion' => 'Discussion',
        'resource' => 'Resource',
        'looking_for_team' => 'Looking for a team',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CommunityComment::class)->orderBy('created_at');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CommunityPostLike::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CommunityPostAttachment::class)->orderBy('id');
    }

    public function isLikedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->likes()->where('user_id', $user->id)->exists();
    }
}
