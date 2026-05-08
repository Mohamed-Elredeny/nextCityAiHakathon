<?php

namespace App\Services;

use App\Models\Edition;
use App\Models\JudgeAssignment;
use App\Models\PeoplesChoiceVote;
use App\Models\RestrictedAwardVote;
use App\Models\Score;
use App\Models\Team;
use Illuminate\Support\Collection;

/**
 * Resolves the six award winners under a no-double-winner rule.
 *
 * Priority (a team that wins one award is removed from contention for the rest):
 *   1. 1st place           — highest leaderboard final_score
 *   2. 2nd place           — next highest final_score
 *   3. 3rd place           — next highest final_score
 *   4. People's Choice     — highest public vote count among remaining teams
 *   5. Best AI Innovation  — highest restricted-vote count among remaining teams
 *   6. Most Impactful      — highest restricted-vote count among remaining teams
 *
 * Ties are broken by highest final_score, then by team id (stable).
 */
class AwardsResolverService
{
    /** Same weighting as PublicLeaderboard so 1st/2nd/3rd align with the public board. */
    public const JUDGES_WEIGHT = 0.90;
    public const POPULARITY_WEIGHT = 0.10;

    /**
     * Returns an array keyed by award slot containing:
     *   ['team' => Team|null, 'metric' => float|int, 'metric_label' => string]
     */
    public function resolve(?int $editionId = null, string $round = 'finals'): array
    {
        $edition = $editionId
            ? Edition::find($editionId)
            : Edition::active();
        if (!$edition) {
            return $this->emptyResult();
        }

        $teams = $this->buildLeaderboard($edition->id, $round);
        $byId = $teams->keyBy('id');

        // Track which team_ids are already locked into a winner slot.
        $taken = [];

        $top1 = $teams->first();
        $top2 = $teams->skip(1)->first();
        $top3 = $teams->skip(2)->first();
        foreach ([$top1, $top2, $top3] as $t) {
            if ($t) $taken[$t->id] = true;
        }

        // 2) People's Choice — most public votes among remaining.
        // peoples_choice_votes has no edition_id column; scope by team_id.
        $editionTeamIds = $byId->keys()->all();
        $voteCounts = PeoplesChoiceVote::query()
            ->whereIn('team_id', $editionTeamIds)
            ->selectRaw('team_id, COUNT(*) as c')
            ->groupBy('team_id')
            ->pluck('c', 'team_id'); // [team_id => count]

        $peoplesChoice = $this->pickHighest($voteCounts, $taken, $byId);
        if ($peoplesChoice) $taken[$peoplesChoice['team']->id] = true;

        // 3+4) Restricted awards — Best AI, then Most Impactful.
        $bestAiCounts = RestrictedAwardVote::query()
            ->where('edition_id', $edition->id)
            ->where('award_key', RestrictedAwardVote::AWARD_BEST_AI)
            ->selectRaw('team_id, COUNT(*) as c')
            ->groupBy('team_id')
            ->pluck('c', 'team_id');
        $bestAi = $this->pickHighest($bestAiCounts, $taken, $byId);
        if ($bestAi) $taken[$bestAi['team']->id] = true;

        $impactCounts = RestrictedAwardVote::query()
            ->where('edition_id', $edition->id)
            ->where('award_key', RestrictedAwardVote::AWARD_MOST_IMPACTFUL)
            ->selectRaw('team_id, COUNT(*) as c')
            ->groupBy('team_id')
            ->pluck('c', 'team_id');
        $mostImpactful = $this->pickHighest($impactCounts, $taken, $byId);

        return [
            'first_place' => [
                'team'         => $top1,
                'metric'       => $top1?->final_score,
                'metric_label' => 'Final score',
            ],
            'second_place' => [
                'team'         => $top2,
                'metric'       => $top2?->final_score,
                'metric_label' => 'Final score',
            ],
            'third_place' => [
                'team'         => $top3,
                'metric'       => $top3?->final_score,
                'metric_label' => 'Final score',
            ],
            'peoples_choice' => [
                'team'         => $peoplesChoice['team'] ?? null,
                'metric'       => $peoplesChoice['metric'] ?? 0,
                'metric_label' => 'Public votes',
            ],
            'best_ai_innovation' => [
                'team'         => $bestAi['team'] ?? null,
                'metric'       => $bestAi['metric'] ?? 0,
                'metric_label' => 'Restricted votes',
            ],
            'most_impactful_solution' => [
                'team'         => $mostImpactful['team'] ?? null,
                'metric'       => $mostImpactful['metric'] ?? 0,
                'metric_label' => 'Restricted votes',
            ],
        ];
    }

    /**
     * Pick the team with the highest count, excluding any team_id in $taken.
     * Tie-break: higher final_score (from $byId), then smaller team id.
     */
    private function pickHighest(Collection $counts, array $taken, Collection $byId): ?array
    {
        $candidates = [];
        foreach ($counts as $teamId => $c) {
            if (isset($taken[$teamId])) continue;
            $team = $byId->get($teamId);
            if (!$team) continue; // count belongs to a non-active team — skip
            $candidates[] = [
                'team'   => $team,
                'metric' => (int) $c,
                'tieFinal' => (float) ($team->final_score ?? 0),
            ];
        }
        if (empty($candidates)) return null;

        usort($candidates, function ($a, $b) {
            if ($a['metric'] !== $b['metric']) return $b['metric'] <=> $a['metric'];
            if ($a['tieFinal'] !== $b['tieFinal']) return $b['tieFinal'] <=> $a['tieFinal'];
            return $a['team']->id <=> $b['team']->id;
        });

        return ['team' => $candidates[0]['team'], 'metric' => $candidates[0]['metric']];
    }

    /**
     * Computes the leaderboard for the given edition + round.
     * Mirrors PublicLeaderboard::teamsRanked() so 1st/2nd/3rd are consistent
     * with the public-facing board.
     */
    private function buildLeaderboard(int $editionId, string $round): Collection
    {
        $teams = Team::query()
            ->where('edition_id', $editionId)
            ->where('status', 'active')
            ->when($round === 'finals', fn ($q) => $q->where('is_finalist', true))
            ->get();

        $teamIds = $teams->pluck('id');

        $recusedJudgeIds = JudgeAssignment::query()
            ->whereIn('team_id', $teamIds)
            ->where('round', $round)
            ->where('recused', true)
            ->pluck('judge_id', 'team_id');

        $aggregates = Score::query()
            ->whereIn('team_id', $teamIds)
            ->where('round', $round)
            ->whereNotNull('locked_at')
            ->when($recusedJudgeIds->isNotEmpty(), function ($q) use ($recusedJudgeIds) {
                $q->whereNotIn('judge_id', $recusedJudgeIds->values()->unique()->all());
            })
            ->selectRaw('team_id, AVG(weighted_total) as avg_total, COUNT(*) as judge_count')
            ->groupBy('team_id')
            ->get()
            ->keyBy('team_id');

        $rawVoteCounts = PeoplesChoiceVote::query()
            ->whereIn('team_id', $teamIds)
            ->selectRaw('team_id, COUNT(*) as votes')
            ->groupBy('team_id')
            ->pluck('votes', 'team_id');

        $effectiveVoteCounts = $teams->mapWithKeys(function (Team $team) use ($rawVoteCounts) {
            $raw = (int) ($rawVoteCounts[$team->id] ?? 0);
            return [$team->id => $team->is_hacker ? 0 : $raw];
        });
        $maxVotes = $effectiveVoteCounts->max() ?: 0;

        return $teams
            ->map(function (Team $team) use ($aggregates, $effectiveVoteCounts, $maxVotes) {
                $agg = $aggregates->get($team->id);
                $judgesAvg = $agg ? round((float) $agg->avg_total, 2) : null;
                $effective = (int) ($effectiveVoteCounts[$team->id] ?? 0);
                $popularity = $maxVotes > 0 ? round(($effective / $maxVotes) * 10, 2) : 0.0;

                $penalised = $judgesAvg;
                if ($judgesAvg !== null && $team->is_hacker) {
                    $penalised = round($judgesAvg * (1 - Team::HACKER_JUDGE_PENALTY), 2);
                }

                $finalScore = $penalised !== null
                    ? round($penalised * self::JUDGES_WEIGHT + $popularity * self::POPULARITY_WEIGHT, 2)
                    : null;

                $team->setAttribute('avg_total', $judgesAvg);
                $team->setAttribute('judge_count', $agg ? (int) $agg->judge_count : 0);
                $team->setAttribute('final_score', $finalScore);
                return $team;
            })
            ->sortByDesc(fn ($t) => $t->final_score ?? $t->avg_total ?? -1)
            ->values();
    }

    private function emptyResult(): array
    {
        return [
            'first_place'             => ['team' => null, 'metric' => null, 'metric_label' => 'Final score'],
            'second_place'            => ['team' => null, 'metric' => null, 'metric_label' => 'Final score'],
            'third_place'             => ['team' => null, 'metric' => null, 'metric_label' => 'Final score'],
            'peoples_choice'          => ['team' => null, 'metric' => 0,    'metric_label' => 'Public votes'],
            'best_ai_innovation'      => ['team' => null, 'metric' => 0,    'metric_label' => 'Restricted votes'],
            'most_impactful_solution' => ['team' => null, 'metric' => 0,    'metric_label' => 'Restricted votes'],
        ];
    }
}
