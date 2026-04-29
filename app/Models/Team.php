<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Team extends Model
{
    protected $fillable = [
        'edition_id', 'theme_id', 'leader_id', 'name', 'slug',
        'tagline', 'logo_path', 'banner_path',
        'status', 'is_finalist', 'all_first_timers',
    ];

    protected $casts = [
        'is_finalist' => 'boolean',
        'all_first_timers' => 'boolean',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->banner_path ? asset('storage/' . $this->banner_path) : null;
    }

    protected static function booted(): void
    {
        static::creating(function (Team $team) {
            if (empty($team->slug)) {
                $team->slug = Str::slug($team->name) . '-' . Str::random(4);
            }
        });
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withPivot(['role_in_team', 'is_leader'])
            ->withTimestamps();
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function submission(): HasOne
    {
        return $this->hasOne(Submission::class)->where('round', Submission::ROUND_ONE);
    }

    public function finalsSubmission(): HasOne
    {
        return $this->hasOne(Submission::class)->where('round', Submission::ROUND_FINALS);
    }

    public function workspaceDrafts(): HasMany
    {
        return $this->hasMany(TeamWorkspaceDraft::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TeamComment::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function judgeAssignments(): HasMany
    {
        return $this->hasMany(JudgeAssignment::class);
    }
}
