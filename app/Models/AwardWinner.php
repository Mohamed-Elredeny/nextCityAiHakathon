<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AwardWinner extends Model
{
    public const SLOT_FIRST                  = 'first_place';
    public const SLOT_SECOND                 = 'second_place';
    public const SLOT_THIRD                  = 'third_place';
    public const SLOT_PEOPLES_CHOICE         = 'peoples_choice';
    public const SLOT_BEST_AI                = 'best_ai_innovation';
    public const SLOT_MOST_IMPACTFUL         = 'most_impactful_solution';

    /**
     * Display metadata for each slot, in podium-then-special order.
     * Used by the public Winners hero and the /winners page so the
     * blade templates don't repeat hard-coded labels.
     */
    public const SLOTS = [
        self::SLOT_FIRST          => ['label' => 'First Place',       'medal' => '🥇', 'order' => 1, 'group' => 'podium'],
        self::SLOT_SECOND         => ['label' => 'Second Place',      'medal' => '🥈', 'order' => 2, 'group' => 'podium'],
        self::SLOT_THIRD          => ['label' => 'Third Place',       'medal' => '🥉', 'order' => 3, 'group' => 'podium'],
        self::SLOT_BEST_AI        => ['label' => 'AI Innovation',     'medal' => '🤖', 'order' => 4, 'group' => 'special'],
        self::SLOT_MOST_IMPACTFUL => ['label' => 'Impact Award',      'medal' => '💡', 'order' => 5, 'group' => 'special'],
        self::SLOT_PEOPLES_CHOICE => ["label" => "People's Choice",   'medal' => '❤️', 'order' => 6, 'group' => 'special'],
    ];

    protected $fillable = ['edition_id', 'team_id', 'slot', 'display_order'];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function scopeForEdition(Builder $q, int $editionId): Builder
    {
        return $q->where('edition_id', $editionId);
    }

    /**
     * Returns the winners for an edition, keyed by slot, eager-loading the
     * team + members so the hero can render avatars without N+1.
     * Missing slots are filled with `null` so the view can render placeholders.
     */
    public static function forEditionKeyed(?int $editionId): \Illuminate\Support\Collection
    {
        $rows = $editionId
            ? static::with(['team.members', 'team.theme'])->forEdition($editionId)->get()->keyBy('slot')
            : collect();

        return collect(array_keys(self::SLOTS))
            ->mapWithKeys(fn (string $slot) => [$slot => $rows->get($slot)]);
    }
}
