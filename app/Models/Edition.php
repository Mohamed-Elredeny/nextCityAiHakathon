<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Edition extends Model
{
    protected $fillable = ['year', 'name', 'starts_at', 'ends_at', 'is_active'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function themes(): HasMany
    {
        return $this->hasMany(Theme::class);
    }

    public function phases(): HasMany
    {
        return $this->hasMany(Phase::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
