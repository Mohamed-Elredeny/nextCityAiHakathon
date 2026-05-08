<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    public const ROLE_CATEGORIES = [
        'designer' => 'Designer',
        'developer' => 'Developer',
        'business' => 'Business',
    ];

    public const CATEGORY_BOARD = 'board';
    public const CATEGORY_PARTNER = 'partner';

    public const USER_CATEGORIES = [
        self::CATEGORY_BOARD => 'Board Member',
        self::CATEGORY_PARTNER => 'Partner',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'institution',
        'organization',
        'national_id',
        'bio',
        'avatar_path',
        'org_logo_path',
        'org_url',
        'headline',
        'primary_role',
        'user_category',
        'social_links',
        'registration_status',
        'requested_role',
        'approved_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'password' => 'hashed',
            'social_links' => 'array',
        ];
    }

    public function isApproved(): bool
    {
        return $this->registration_status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->registration_status === 'pending';
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path ? asset('storage/' . $this->avatar_path) : null;
    }

    public function getOrgLogoUrlAttribute(): ?string
    {
        return $this->org_logo_path ? asset('storage/' . $this->org_logo_path) : null;
    }

    public function isBoardMember(): bool
    {
        return $this->user_category === self::CATEGORY_BOARD;
    }

    public function isPartner(): bool
    {
        return $this->user_category === self::CATEGORY_PARTNER;
    }

    public function scopeBoardMembers($q)
    {
        return $q->where('user_category', self::CATEGORY_BOARD);
    }

    public function scopePartners($q)
    {
        return $q->where('user_category', self::CATEGORY_PARTNER);
    }

    /**
     * Display label for board/partner: prefer organization name, fall back to institution then full name.
     */
    public function getDisplayOrgAttribute(): ?string
    {
        return $this->organization ?: ($this->institution ?: null);
    }

    /**
     * Two-letter initials for the org/person — used as a logo placeholder.
     */
    public function getOrgInitialsAttribute(): string
    {
        $source = $this->organization ?: $this->name;
        return collect(explode(' ', trim($source ?? '?')))
            ->take(2)
            ->map(fn ($p) => mb_substr($p, 0, 1))
            ->implode('') ?: '?';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', trim($this->name ?? '?')))
            ->take(2)
            ->map(fn ($p) => mb_substr($p, 0, 1))
            ->implode('') ?: '?';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Restricted-award voting eligibility (Best AI / Most Impactful).
     * Open to: registered users who are part of a team, OR judges, OR mentors,
     * OR super_admins. A registered student with no team is excluded.
     */
    public function canVoteRestrictedAwards(): bool
    {
        if ($this->hasAnyRole(['judge', 'mentor', 'super_admin'])) {
            return true;
        }
        return \App\Models\TeamMember::where('user_id', $this->id)->exists();
    }

    /**
     * Audit label describing why this user is eligible. Stored on the vote
     * row so admins can verify the population without re-deriving it later.
     */
    public function restrictedAwardVoterRole(): ?string
    {
        if ($this->hasRole('super_admin')) return 'super_admin';
        if ($this->hasRole('judge'))       return 'judge';
        if ($this->hasRole('mentor'))      return 'mentor';
        if (\App\Models\TeamMember::where('user_id', $this->id)->exists()) {
            return 'team_member';
        }
        return null;
    }

    public function teamMembership()
    {
        return $this->hasOne(\App\Models\TeamMember::class);
    }

    public function teams()
    {
        return $this->belongsToMany(\App\Models\Team::class, 'team_members')
            ->withPivot(['role_in_team', 'role_category', 'is_leader'])
            ->withTimestamps();
    }

    public function currentTeam(): ?\App\Models\Team
    {
        return $this->teams()->first();
    }

    public function ledTeams()
    {
        return $this->hasMany(\App\Models\Team::class, 'leader_id');
    }

    public function communityPosts()
    {
        return $this->hasMany(\App\Models\CommunityPost::class);
    }

    public function teamApplications()
    {
        return $this->hasMany(\App\Models\TeamApplication::class);
    }

    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class);
    }

    /**
     * Profile must be complete (basic public fields filled) AND have a profile
     * photo before a participant is allowed to check in for attendance.
     * National ID is intentionally NOT checked here — it's not part of the
     * public profile form.
     */
    public function isProfileComplete(): bool
    {
        return filled($this->name)
            && filled($this->phone)
            && filled($this->institution)
            && filled($this->avatar_path);
    }

    /**
     * Returns the list of fields that are still missing for attendance gating.
     */
    public function missingProfileFields(): array
    {
        $required = [
            'name' => 'Full name',
            'phone' => 'Phone number',
            'institution' => 'Institution / University',
            'avatar_path' => 'Profile photo',
        ];

        $missing = [];
        foreach ($required as $field => $label) {
            if (blank($this->{$field})) {
                $missing[$field] = $label;
            }
        }
        return $missing;
    }
}
