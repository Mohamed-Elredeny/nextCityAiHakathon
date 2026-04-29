<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamComment extends Model
{
    protected $fillable = ['team_id', 'user_id', 'channel', 'body'];

    public const CHANNEL_TEAM = 'team';
    public const CHANNEL_MENTOR = 'mentor';
    public const CHANNEL_JUDGE = 'judge';

    public const CHANNELS = [
        self::CHANNEL_TEAM => 'Team Internal',
        self::CHANNEL_MENTOR => 'With Mentor',
        self::CHANNEL_JUDGE => 'With Judges',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
