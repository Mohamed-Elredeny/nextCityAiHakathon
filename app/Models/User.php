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

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'institution',
        'national_id',
        'bio',
        'avatar_path',
        'headline',
        'primary_role',
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

    public function teamMembership()
    {
        return $this->hasOne(\App\Models\TeamMember::class);
    }

    public function team()
    {
        return $this->belongsToMany(\App\Models\Team::class, 'team_members')
            ->withPivot(['role_in_team', 'role_category', 'is_leader'])
            ->withTimestamps();
    }

    public function currentTeam(): ?\App\Models\Team
    {
        return $this->team()->first();
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
}
