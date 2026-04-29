<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    protected $fillable = ['edition_id', 'key', 'name', 'description', 'sort_order'];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }
}
