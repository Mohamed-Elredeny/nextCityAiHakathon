<?php

namespace App\Filament\Widgets;

use App\Models\Edition;
use App\Models\JudgeAssignment;
use App\Models\Score;
use App\Models\Submission;
use App\Models\Team;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HackathonStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $edition = Edition::active();
        $editionId = $edition?->id;

        $teamsQuery = Team::query()->when($editionId, fn ($q) => $q->where('edition_id', $editionId));
        $teamsTotal = (clone $teamsQuery)->count();
        $finalists = (clone $teamsQuery)->where('is_finalist', true)->count();

        $submissionsQuery = Submission::query()
            ->when($editionId, fn ($q) => $q->whereIn('team_id', Team::where('edition_id', $editionId)->select('id')));
        $submitted = (clone $submissionsQuery)->whereIn('status', [
            Submission::STATUS_SUBMITTED,
            Submission::STATUS_VALIDATED,
            Submission::STATUS_ACCEPTED,
        ])->count();
        $flagged = (clone $submissionsQuery)->where('status', Submission::STATUS_FLAGGED)->count();

        $assignmentsQuery = JudgeAssignment::query()
            ->when($editionId, fn ($q) => $q->whereIn('team_id', Team::where('edition_id', $editionId)->select('id')))
            ->where('recused', false);
        $assignmentsTotal = (clone $assignmentsQuery)->count();
        $distinctJudges = (clone $assignmentsQuery)->distinct('judge_id')->count('judge_id');

        $scoresQuery = Score::query()
            ->when($editionId, fn ($q) => $q->whereIn('team_id', Team::where('edition_id', $editionId)->select('id')));
        $scoresLocked = (clone $scoresQuery)->whereNotNull('locked_at')->count();
        $scorePct = $assignmentsTotal > 0 ? round(($scoresLocked / $assignmentsTotal) * 100) : 0;

        return [
            Stat::make('Teams', $teamsTotal)
                ->description($finalists > 0 ? "{$finalists} finalists" : 'No finalists yet')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('primary'),

            Stat::make('Submissions', "{$submitted} / {$teamsTotal}")
                ->description($flagged > 0 ? "{$flagged} flagged for review" : 'All clean')
                ->descriptionIcon($flagged > 0 ? 'heroicon-m-flag' : 'heroicon-m-check-badge')
                ->color($flagged > 0 ? 'warning' : 'success'),

            Stat::make('Judges', $distinctJudges)
                ->description("{$assignmentsTotal} active assignments")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Scoring progress', "{$scorePct}%")
                ->description("{$scoresLocked} / {$assignmentsTotal} scores locked")
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color($scorePct >= 100 ? 'success' : ($scorePct > 0 ? 'warning' : 'gray')),
        ];
    }
}
