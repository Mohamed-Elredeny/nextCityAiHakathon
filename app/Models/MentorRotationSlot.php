<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorRotationSlot extends Model
{
    protected $fillable = ['mentor_id', 'team_id', 'slot_start', 'slot_end'];

    protected $casts = [
        'slot_start' => 'datetime',
        'slot_end' => 'datetime',
    ];

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
