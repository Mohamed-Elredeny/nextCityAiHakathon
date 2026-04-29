<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialAwardNomination extends Model
{
    protected $fillable = ['judge_id', 'award_key', 'team_id', 'round', 'justification'];

    public const AWARD_BEST_AI = 'best_ai_innovation';
    public const AWARD_MOST_IMPACTFUL = 'most_impactful_solution';
    public const AWARD_BEST_NEWCOMER = 'best_newcomer';

    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
