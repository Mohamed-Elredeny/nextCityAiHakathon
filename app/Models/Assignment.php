<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Assignment extends Model
{
    protected $fillable = [
        'edition_id', 'title', 'slug', 'description',
        'opens_at', 'deadline_at',
        'max_files', 'max_file_size_kb', 'accepted_extensions',
        'max_score', 'release_grades',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'opens_at' => 'datetime',
        'deadline_at' => 'datetime',
        'is_active' => 'boolean',
        'release_grades' => 'boolean',
        'accepted_extensions' => 'array',
        'max_score' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Assignment $a) {
            if (empty($a->slug)) {
                $a->slug = Str::slug($a->title) . '-' . Str::random(4);
            }
        });
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function submissionFor(Team $team): ?AssignmentSubmission
    {
        return $this->submissions()->where('team_id', $team->id)->first();
    }

    public function isOpen(): bool
    {
        if (! $this->is_active) return false;
        $now = now();
        if ($this->opens_at && $now->lt($this->opens_at)) return false;
        if ($this->deadline_at && $now->gt($this->deadline_at)) return false;
        return true;
    }

    public function isPastDeadline(): bool
    {
        return $this->deadline_at && now()->gt($this->deadline_at);
    }

    public function statusLabel(): string
    {
        if (! $this->is_active) return 'Inactive';
        if ($this->opens_at && now()->lt($this->opens_at)) return 'Opens ' . $this->opens_at->diffForHumans();
        if ($this->isPastDeadline()) return 'Closed';
        if ($this->deadline_at) return 'Due ' . $this->deadline_at->diffForHumans();
        return 'Open';
    }
}
