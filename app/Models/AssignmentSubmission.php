<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_id', 'team_id', 'notes',
        'first_submitted_at', 'last_activity_at', 'last_activity_by',
    ];

    protected $casts = [
        'first_submitted_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function lastActivityBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_activity_by');
    }

    public function files(): HasMany
    {
        return $this->hasMany(AssignmentFile::class)->latest();
    }

    public function scores(): HasMany
    {
        return $this->hasMany(AssignmentScore::class);
    }

    public function averageScore(): ?float
    {
        $count = $this->scores->count();
        if ($count === 0) return null;
        return round((float) $this->scores->avg('score'), 2);
    }
}
