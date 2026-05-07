<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Score extends Model
{
    protected $fillable = [
        'judge_id', 'team_id', 'round',
        'business_viability', 'technical_feasibility', 'impact_relevance',
        'team_skills', 'presentation_skills',
        'weighted_total', 'comment', 'locked_at',
    ];

    protected $casts = [
        'business_viability' => 'decimal:2',
        'technical_feasibility' => 'decimal:2',
        'impact_relevance' => 'decimal:2',
        'team_skills' => 'decimal:2',
        'presentation_skills' => 'decimal:2',
        'weighted_total' => 'decimal:2',
        'locked_at' => 'datetime',
    ];

    /**
     * Day-2 pitching rubric — 5 criteria, equal 20% weights each.
     */
    public const WEIGHTS = [
        'business_viability' => 0.20,
        'technical_feasibility' => 0.20,
        'impact_relevance' => 0.20,
        'team_skills' => 0.20,
        'presentation_skills' => 0.20,
    ];

    /**
     * Human-readable labels for the rubric — used by judge dashboard,
     * leaderboard, and any score breakdowns.
     */
    public const LABELS = [
        'business_viability' => 'Business Viability',
        'technical_feasibility' => 'Technical Feasibility',
        'impact_relevance' => 'Impact & Relevance',
        'team_skills' => 'Team Business & Technical Skills',
        'presentation_skills' => 'Presentation & Communication Skills',
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
