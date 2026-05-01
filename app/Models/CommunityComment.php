<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityComment extends Model
{
    protected $fillable = [
        'community_post_id', 'user_id', 'body',
        'mentioned_user_ids', 'mentioned_team_ids', 'edited_at',
    ];

    protected $casts = [
        'mentioned_user_ids' => 'array',
        'mentioned_team_ids' => 'array',
        'edited_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'community_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
