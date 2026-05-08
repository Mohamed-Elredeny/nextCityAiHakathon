<?php

namespace App\Filament\Pages;

use App\Models\Edition;
use App\Models\PeoplesChoiceVote;
use App\Models\RestrictedAwardVote;
use App\Services\AwardsResolverService;
use Filament\Pages\Page;

class AwardsResolver extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationGroup = 'Judging';
    protected static ?string $navigationLabel = 'Awards Resolver';
    protected static ?string $title = 'Awards Resolver — 6 Winners (no repeats)';
    protected static ?int $navigationSort = 50;
    protected static string $view = 'filament.pages.awards-resolver';

    public string $round = 'finals';

    public function mount(): void
    {
        // Default to round1 if no team has been marked as finalist yet —
        // saves the admin from picking the round manually before flagging finalists.
        $hasFinalists = \App\Models\Team::where('is_finalist', true)->exists();
        $this->round = $hasFinalists ? 'finals' : 'round1';
    }

    public function setRound(string $round): void
    {
        if (in_array($round, ['round1', 'finals'], true)) {
            $this->round = $round;
        }
    }

    public function getViewData(): array
    {
        $edition = Edition::active();
        $winners = (new AwardsResolverService())->resolve($edition?->id, $this->round);

        // Detailed counts (so the admin can see ALL contenders, not just the winner)
        $editionTeamIds = $edition
            ? \App\Models\Team::where('edition_id', $edition->id)->where('status', 'active')->pluck('id')
            : collect();

        $publicCounts = PeoplesChoiceVote::query()
            ->whereIn('team_id', $editionTeamIds)
            ->selectRaw('team_id, COUNT(*) as c')
            ->groupBy('team_id')
            ->orderByDesc('c')
            ->with('team:id,name')
            ->get();

        $bestAiCounts = $edition ? RestrictedAwardVote::query()
            ->where('edition_id', $edition->id)
            ->where('award_key', RestrictedAwardVote::AWARD_BEST_AI)
            ->selectRaw('team_id, COUNT(*) as c')
            ->groupBy('team_id')
            ->orderByDesc('c')
            ->with('team:id,name')
            ->get() : collect();

        $impactCounts = $edition ? RestrictedAwardVote::query()
            ->where('edition_id', $edition->id)
            ->where('award_key', RestrictedAwardVote::AWARD_MOST_IMPACTFUL)
            ->selectRaw('team_id, COUNT(*) as c')
            ->groupBy('team_id')
            ->orderByDesc('c')
            ->with('team:id,name')
            ->get() : collect();

        return [
            'winners'      => $winners,
            'round'        => $this->round,
            'edition'      => $edition,
            'publicCounts' => $publicCounts,
            'bestAiCounts' => $bestAiCounts,
            'impactCounts' => $impactCounts,
        ];
    }
}
