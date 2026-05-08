<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeoplesChoiceVote extends Model
{
    protected $fillable = [
        'user_id', 'team_id', 'voted_at',
        'voter_name', 'voter_email', 'voter_token', 'ip_address',
        'device_fingerprint',
    ];

    protected $casts = [
        'voted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
