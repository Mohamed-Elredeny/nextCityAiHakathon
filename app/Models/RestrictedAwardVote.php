<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vote cast by a registered, affiliated user (team member, judge, mentor,
 * or admin) on one of the two restricted awards. Unlike People's Choice
 * (open public vote), only one vote per user per award per edition is
 * allowed; users may change it while the voting window is open.
 */
class RestrictedAwardVote extends Model
{
    protected $fillable = [
        'user_id',
        'team_id',
        'edition_id',
        'award_key',
        'voter_role',
    ];

    public const AWARD_BEST_AI = 'best_ai_innovation';
    public const AWARD_MOST_IMPACTFUL = 'most_impactful_solution';

    public const AWARDS = [
        self::AWARD_BEST_AI       => 'Best AI Innovation',
        self::AWARD_MOST_IMPACTFUL => 'Most Impactful Solution',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }
}
