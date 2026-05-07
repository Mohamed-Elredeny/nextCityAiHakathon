<?php

namespace App\Livewire;

use App\Models\Edition;
use App\Models\JudgeAssignment;
use App\Models\PeoplesChoiceVote;
use App\Models\Phase;
use App\Models\PitchSchedule;
use App\Models\Score;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PublicLeaderboard extends Component
{
    /** Weight given to judges' average vs. People's Choice popularity in the FINAL score */
    public const JUDGES_WEIGHT = 0.90;
    public const POPULARITY_WEIGHT = 0.10;

    public string $round = 'round1';
    public ?int $expandedTeamId = null;

    public function toggleExpanded(int $teamId): void
    {
        $this->expandedTeamId = ($this->expandedTeamId === $teamId) ? null : $teamId;
    }

    public function mount(): void
    {
        $edition = Edition::active();
        if ($edition) {
            $finalsActive = Phase::where('edition_id', $edition->id)
                ->whereIn('key', [Phase::KEY_FINALIST_PITCHING, Phase::KEY_AWARDS])
                ->where('state', Phase::STATE_ACTIVE)
                ->exists();
            $this->round = $finalsActive ? 'finals' : 'round1';
        }
    }

    public function setRound(string $round): void
    {
        $this->round = in_array($round, ['round1', 'finals'], true) ? $round : 'round1';
        $this->expandedTeamId = null;
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        $edition = Edition::active();
        $teams = $edition ? $this->teamsRanked($edition) : collect();
        $currentPhase = $edition
            ? Phase::where('edition_id', $edition->id)
                ->where('state', Phase::STATE_ACTIVE)
                ->orderBy('sort_order')
                ->first()
            : null;
        $nowPitching = $edition ? $this->nowPitching() : null;
        $nextDeadline = $currentPhase?->ends_at;

        $recusedJudgeIds = JudgeAssignment::query()
            ->whereIn('team_id', $teams->pluck('id'))
            ->where('round', $this->round)
            ->where('recused', true)
            ->pluck('judge_id')
            ->unique()
            ->values();

        $allScores = Score::query()
            ->whereIn('team_id', $teams->pluck('id'))
            ->where('round', $this->round)
            ->whereNotNull('locked_at')
            ->when($recusedJudgeIds->isNotEmpty(), fn ($q) => $q->whereNotIn('judge_id', $recusedJudgeIds))
            ->with('judge:id,name,institution')
            ->get();

        // Collect every distinct judge, ordered by name
        $judges = $allScores->pluck('judge')->filter()->unique('id')->sortBy('name')->values();

        // Build a fast lookup: scoreLookup[teamId][judgeId] = Score
        $scoreLookup = [];
        foreach ($allScores as $s) {
            $scoreLookup[$s->team_id][$s->judge_id] = $s;
        }

        // Showcase ribbon — every active team in the edition with their members.
        $showcaseTeams = $edition
            ? Team::query()
                ->where('edition_id', $edition->id)
                ->where('status', 'active')
                ->with(['members' => fn ($q) => $q->orderBy('users.name')])
                ->orderBy('name')
                ->get()
            : collect();

        return view('livewire.public-leaderboard', [
            'teams' => $teams,
            'edition' => $edition,
            'currentPhase' => $currentPhase,
            'nowPitching' => $nowPitching,
            'nextDeadline' => $nextDeadline,
            'serverNow' => Carbon::now(),
            'judges' => $judges,
            'scoreLookup' => $scoreLookup,
            'showcaseTeams' => $showcaseTeams,
            'criteria' => [
                'innovation' => ['label' => 'Innovation', 'weight' => 0.20],
                'technical'  => ['label' => 'Technical',  'weight' => 0.25],
                'impact'     => ['label' => 'Impact',     'weight' => 0.20],
                'ux'         => ['label' => 'UX',         'weight' => 0.15],
                'pitch'      => ['label' => 'Pitch',      'weight' => 0.10],
                'business'   => ['label' => 'Business',   'weight' => 0.10],
            ],
            'judgesWeight' => self::JUDGES_WEIGHT,
            'popularityWeight' => self::POPULARITY_WEIGHT,
        ]);
    }

    protected function teamsRanked(Edition $edition): Collection
    {
        $teams = Team::query()
            ->where('edition_id', $edition->id)
            ->where('status', 'active')
            ->when($this->round === 'finals', fn ($q) => $q->where('is_finalist', true))
            ->with(['theme', 'teamMembers'])
            ->get();

        $teamIds = $teams->pluck('id');

        // Exclude scores from judges who were later recused for this round.
        $recusedJudgeIds = JudgeAssignment::query()
            ->whereIn('team_id', $teamIds)
            ->where('round', $this->round)
            ->where('recused', true)
            ->pluck('judge_id', 'team_id');

        $aggregates = Score::query()
            ->whereIn('team_id', $teamIds)
            ->where('round', $this->round)
            ->whereNotNull('locked_at')
            ->when($recusedJudgeIds->isNotEmpty(), function ($q) use ($recusedJudgeIds) {
                $q->whereNotIn('judge_id', $recusedJudgeIds->values()->unique()->all());
            })
            ->selectRaw('team_id, AVG(weighted_total) as avg_total, COUNT(*) as judge_count')
            ->groupBy('team_id')
            ->get()
            ->keyBy('team_id');

        $voteCounts = PeoplesChoiceVote::query()
            ->whereIn('team_id', $teamIds)
            ->selectRaw('team_id, COUNT(*) as votes')
            ->groupBy('team_id')
            ->pluck('votes', 'team_id');

        $maxVotes = $voteCounts->max() ?: 0;

        return $teams
            ->map(function (Team $team) use ($aggregates, $voteCounts, $maxVotes) {
                $agg = $aggregates->get($team->id);
                $judgesAvg = $agg ? round((float) $agg->avg_total, 2) : null;
                $votes = (int) ($voteCounts[$team->id] ?? 0);
                // Normalize votes to a 0–10 popularity score (linear, top team = 10)
                $popularity = $maxVotes > 0 ? round(($votes / $maxVotes) * 10, 2) : 0.0;

                $finalScore = $judgesAvg !== null
                    ? round($judgesAvg * self::JUDGES_WEIGHT + $popularity * self::POPULARITY_WEIGHT, 2)
                    : null;

                $team->setAttribute('avg_total', $judgesAvg);
                $team->setAttribute('judge_count', $agg ? (int) $agg->judge_count : 0);
                $team->setAttribute('vote_count', $votes);
                $team->setAttribute('popularity', $popularity);
                $team->setAttribute('final_score', $finalScore);
                return $team;
            })
            // Sort by FINAL score (judges 90% + popularity 10%); fall back to judges-only if no final
            ->sortByDesc(fn ($team) => $team->final_score ?? $team->avg_total ?? -1)
            ->values();
    }

    protected function nowPitching(): ?PitchSchedule
    {
        return PitchSchedule::query()
            ->where('round', $this->round)
            ->whereNotNull('started_at')
            ->whereNull('ended_at')
            ->with('team')
            ->orderByDesc('started_at')
            ->first();
    }
}
