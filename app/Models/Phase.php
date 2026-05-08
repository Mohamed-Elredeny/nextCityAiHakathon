<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Phase extends Model
{
    protected $fillable = [
        'edition_id', 'key', 'label', 'starts_at', 'ends_at',
        'state', 'auto_transition', 'sort_order',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'auto_transition' => 'boolean',
    ];

    public const STATE_PENDING = 'pending';
    public const STATE_ACTIVE = 'active';
    public const STATE_CLOSED = 'closed';

    public const KEY_REGISTRATION = 'registration';
    public const KEY_THEME_LOCK_WINDOW = 'theme_lock_window';
    public const KEY_SPRINT_1 = 'sprint_1';
    public const KEY_MENTOR_SPEED = 'mentor_speed';
    public const KEY_SPRINT_2 = 'sprint_2';
    public const KEY_SUBMISSION_WINDOW = 'submission_window';
    public const KEY_SUBMISSION_CLOSED = 'submission_closed';
    public const KEY_ROUND1_PITCHING = 'round1_pitching';
    public const KEY_JUDGING_BREAK = 'judging_break';
    public const KEY_FINALIST_ANNOUNCE = 'finalist_announce';
    public const KEY_FINALS_SUBMISSION_WINDOW = 'finals_submission_window';
    public const KEY_FINALIST_PITCHING = 'finalist_pitching';
    public const KEY_RESTRICTED_AWARD_VOTING = 'restricted_award_voting';
    public const KEY_AWARDS = 'awards';

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function isActive(): bool
    {
        return $this->state === self::STATE_ACTIVE;
    }
}
