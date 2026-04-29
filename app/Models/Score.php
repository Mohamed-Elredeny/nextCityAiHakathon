<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Score extends Model
{
    protected $fillable = [
        'judge_id', 'team_id', 'round',
        'innovation', 'technical', 'impact', 'ux', 'pitch', 'business',
        'weighted_total', 'comment', 'locked_at',
    ];

    protected $casts = [
        'innovation' => 'decimal:2',
        'technical' => 'decimal:2',
        'impact' => 'decimal:2',
        'ux' => 'decimal:2',
        'pitch' => 'decimal:2',
        'business' => 'decimal:2',
        'weighted_total' => 'decimal:2',
        'locked_at' => 'datetime',
    ];

    public const WEIGHTS = [
        'innovation' => 0.20,
        'technical' => 0.25,
        'impact' => 0.20,
        'ux' => 0.15,
        'pitch' => 0.10,
        'business' => 0.10,
    ];

    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    public function calculateWeightedTotal(): float
    {
        $sum = 0.0;
        foreach (self::WEIGHTS as $criterion => $weight) {
            $sum += ((float) $this->{$criterion}) * $weight;
        }
        return round($sum, 2);
    }
}
