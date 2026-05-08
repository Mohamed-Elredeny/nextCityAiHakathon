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
    public const MAX_MEMBERS = 5;

    /**
     * When a team is flagged as hacker (vote manipulation), their People's
     * Choice votes are zeroed AND their judges' average is reduced by this
     * percentage on the leaderboard (10% by default).
     */
    public const HACKER_JUDGE_PENALTY = 0.10;

    protected $fillable = [
        'edition_id', 'theme_id', 'leader_id', 'name', 'slug',
        'tagline', 'logo_path', 'banner_path',
        'status', 'is_finalist', 'all_first_timers',
        'is_recruiting', 'recruitment_message', 'looking_for_skills', 'needed_roles',
        'is_hacker', 'hacker_reason', 'hacker_marked_at', 'hacker_marked_by',
    ];

    protected $casts = [
        'is_finalist' => 'boolean',
        'all_first_timers' => 'boolean',
        'is_recruiting' => 'boolean',
        'is_hacker' => 'boolean',
        'hacker_marked_at' => 'datetime',
        'needed_roles' => 'array',
    ];

    public function getRoleCoverageAttribute(): array
    {
        $required = array_keys(\App\Models\User::ROLE_CATEGORIES);
        $filled = $this->teamMembers->pluck('role_category')->filter()->unique()->values()->all();
        return [
            'filled' => $filled,
            'missing' => array_values(array_diff($required, $filled)),
        ];
    }

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

        // When a new team is created, auto-assign every board member and
        // partner as a judge (round1 + finals) and as a mentor — they
        // have full visibility on every team by design.
        static::created(function (Team $team) {
            $people = \App\Models\User::query()
                ->whereIn('user_category', [
                    \App\Models\User::CATEGORY_BOARD,
                    \App\Models\User::CATEGORY_PARTNER,
                ])
                ->pluck('id');

            foreach ($people as $personId) {
                \App\Models\JudgeAssignment::firstOrCreate(
                    ['judge_id' => $personId, 'team_id' => $team->id, 'round' => \App\Models\JudgeAssignment::ROUND_ONE],
                    ['recused' => false],
                );
                \App\Models\JudgeAssignment::firstOrCreate(
                    ['judge_id' => $personId, 'team_id' => $team->id, 'round' => \App\Models\JudgeAssignment::ROUND_FINALS],
                    ['recused' => false],
                );
                \App\Models\MentorAssignment::firstOrCreate(
                    ['mentor_id' => $personId, 'team_id' => $team->id],
                );
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
            ->withPivot(['role_in_team', 'role_category', 'is_leader'])
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

    public function applications(): HasMany
    {
        return $this->hasMany(TeamApplication::class);
    }

    public function pendingApplications(): HasMany
    {
        return $this->hasMany(TeamApplication::class)->where('status', TeamApplication::STATUS_PENDING);
    }
}
