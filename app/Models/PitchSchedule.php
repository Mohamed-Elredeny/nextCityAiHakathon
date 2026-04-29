<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PitchSchedule extends Model
{
    protected $table = 'pitch_schedule';

    protected $fillable = ['team_id', 'round', 'slot_index', 'scheduled_start', 'started_at', 'ended_at'];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
