<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AttendanceSession extends Model
{
    public const TYPES = [
        'opening' => 'Opening Session',
        'day1' => 'Day 1 — May 7',
        'day2' => 'Day 2 — May 8',
        'closing' => 'Closing / Awards',
        'other' => 'Other',
    ];

    protected $fillable = [
        'edition_id', 'name', 'slug', 'type', 'token',
        'starts_at', 'ends_at', 'is_active', 'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (AttendanceSession $session) {
            if (empty($session->slug)) {
                $session->slug = Str::slug($session->name) . '-' . Str::random(4);
            }
            if (empty($session->token)) {
                $session->token = Str::random(40);
            }
        });
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function isOpenForCheckIn(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }
        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }
        return true;
    }

    public function getCheckInUrlAttribute(): string
    {
        return route('attendance.check-in', $this->token);
    }
}
