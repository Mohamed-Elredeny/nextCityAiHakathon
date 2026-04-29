<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JudgeAssignment extends Model
{
    protected $fillable = ['judge_id', 'team_id', 'round', 'recused', 'recused_reason'];

    protected $casts = [
        'recused' => 'boolean',
    ];

    public const ROUND_ONE = 'round1';
    public const ROUND_FINALS = 'finals';

    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
