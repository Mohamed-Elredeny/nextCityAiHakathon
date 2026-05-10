<?php

namespace App\Livewire;

use App\Models\AwardWinner;
use App\Models\Edition;
use App\Models\PeoplesChoiceVote;
use App\Models\Phase;
use App\Models\PitchSchedule;
use App\Models\Score;
use App\Models\Submission;
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

    public string $round = 'finals';
    public ?int $expandedTeamId = null;

    public function toggleExpanded(int $teamId): void
    {
        $this->expandedTeamId = ($this->expandedTeamId === $teamId) ? null : $teamId;
    }

    public function mount(): void
    {
        // Default landing tab is Finals — the event is now in/past finals.
        // Only fall back to Round 1 if no finalists have been promoted yet,
        // otherwise the finals tab would render an empty board which is
        // confusing for visitors landing on the home page.
        $edition = Edition::active();
        if ($edition) {
            $hasFinalists = Team::where('edition_id', $edition->id)
                ->where('is_finalist', true)
                ->exists();
            $this->round = $hasFinalists ? 'finals' : 'round1';
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

        // Recusal is per-team: a judge recused from team A must still count
        // for team B if they locked a score there. Filter pair-wise via
        // NOT EXISTS rather than excluding the judge globally.
        $allScores = Score::query()
            ->whereIn('team_id', $teams->pluck('id'))
            ->where('round', $this->round)
            ->whereNotNull('locked_at')
            ->whereNotExists(function ($q) {
                $q->select(\DB::raw(1))
                    ->from('judge_assignments')
                    ->whereColumn('judge_assignments.team_id', 'scores.team_id')
                    ->whereColumn('judge_assignments.judge_id', 'scores.judge_id')
                    ->whereColumn('judge_assignments.round', 'scores.round')
                    ->where('judge_assignments.recused', true);
            })
            ->with('judge:id,name,institution')
            ->get();

        // Collect every distinct judge, ordered by name. Used by the expanded
        // detail layout's per-judge column ordering reference.
        $judges = $allScores->pluck('judge')->filter()->unique('id')->sortBy('name')->values();

        // Build a fast lookup: scoreLookup[teamId][judgeId] = Score
        $scoreLookup = [];
        foreach ($allScores as $s) {
            $scoreLookup[$s->team_id][$s->judge_id] = $s;
        }

        // Per-team list of judges who have *locked* a score for THIS team.
        // The leaderboard renders only these for each team — judges who didn't
        // judge this team (or haven't locked yet) are hidden, since the
        // assignment matrix is sparse.
        $teamJudges = [];
        foreach ($allScores->groupBy('team_id') as $teamId => $teamScores) {
            $teamJudges[$teamId] = $teamScores
                ->pluck('judge')
                ->filter()
                ->unique('id')
                ->sortBy('name')
                ->values();
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

        // Partners shown in the marquee + landing footer.
        $partners = User::partners()
            ->where('registration_status', 'approved')
            ->orderBy('organization')
            ->get();

        // Pitch schedule slots keyed by team_id for the active round
        $teamSlots = PitchSchedule::query()
            ->where('round', $this->round)
            ->whereIn('team_id', $teams->pluck('id'))
            ->get()
            ->keyBy('team_id');

        // Round 1 submission status keyed by team_id
        $teamSubmissions = Submission::query()
            ->where('round', $this->round)
            ->whereIn('team_id', $teams->pluck('id'))
            ->get(['team_id', 'status', 'submitted_at'])
            ->keyBy('team_id');

        $winners = AwardWinner::forEditionKeyed($edition?->id);

        return view('livewire.public-leaderboard', [
            'teams' => $teams,
            'edition' => $edition,
            'winners' => $winners,
            'currentPhase' => $currentPhase,
            'nowPitching' => $nowPitching,
            'nextDeadline' => $nextDeadline,
            'serverNow' => Carbon::now(),
            'judges' => $judges,
            'teamJudges' => $teamJudges,
            'scoreLookup' => $scoreLookup,
            'showcaseTeams' => $showcaseTeams,
            'partners' => $partners,
            'teamSlots' => $teamSlots,
            'teamSubmissions' => $teamSubmissions,
            'criteria' => [
                'business_viability'    => ['label' => 'Business Viability',                'weight' => 0.20],
                'technical_feasibility' => ['label' => 'Technical Feasibility',             'weight' => 0.20],
                'impact_relevance'      => ['label' => 'Impact & Relevance',                'weight' => 0.20],
                'team_skills'           => ['label' => 'Team Business & Technical Skills',  'weight' => 0.20],
                'presentation_skills'   => ['label' => 'Presentation & Communication Skills', 'weight' => 0.20],
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

        // Exclude scores from judges who were recused FROM THIS team for
        // this round (per-pair, not global).
        $aggregates = Score::query()
            ->whereIn('team_id', $teamIds)
            ->where('round', $this->round)
            ->whereNotNull('locked_at')
            ->whereNotExists(function ($q) {
                $q->select(\DB::raw(1))
                    ->from('judge_assignments')
                    ->whereColumn('judge_assignments.team_id', 'scores.team_id')
                    ->whereColumn('judge_assignments.judge_id', 'scores.judge_id')
                    ->whereColumn('judge_assignments.round', 'scores.round')
                    ->where('judge_assignments.recused', true);
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

        // Hacker-flagged teams contribute 0 votes for the popularity
        // calculation (and for the max-vote normalisation baseline).
        $effectiveVoteCounts = $teams->mapWithKeys(function (Team $team) use ($rawVoteCounts) {
            $raw = (int) ($rawVoteCounts[$team->id] ?? 0);
            return [$team->id => $team->is_hacker ? 0 : $raw];
        });
        $maxVotes = $effectiveVoteCounts->max() ?: 0;

        return $teams
            ->map(function (Team $team) use ($aggregates, $rawVoteCounts, $effectiveVoteCounts, $maxVotes) {
                $agg = $aggregates->get($team->id);
                $judgesAvg = $agg ? round((float) $agg->avg_total, 2) : null;

                // Original (raw) vote count is kept for transparency in the UI,
                // even when the team is penalised.
                $rawVotes = (int) ($rawVoteCounts[$team->id] ?? 0);
                $effectiveVotes = (int) ($effectiveVoteCounts[$team->id] ?? 0);

                // Normalize EFFECTIVE votes (hacker = 0) to a 0–10 popularity score
                $popularity = $maxVotes > 0 ? round(($effectiveVotes / $maxVotes) * 10, 2) : 0.0;

                // Apply judge-score penalty for hackers (10% reduction)
                $penalisedJudgesAvg = $judgesAvg;
                if ($judgesAvg !== null && $team->is_hacker) {
                    $penalisedJudgesAvg = round($judgesAvg * (1 - Team::HACKER_JUDGE_PENALTY), 2);
                }

                $finalScore = $penalisedJudgesAvg !== null
                    ? round($penalisedJudgesAvg * self::JUDGES_WEIGHT + $popularity * self::POPULARITY_WEIGHT, 2)
                    : null;

                $team->setAttribute('avg_total', $judgesAvg);                  // raw judges' avg
                $team->setAttribute('avg_total_effective', $penalisedJudgesAvg); // after penalty
                $team->setAttribute('judge_count', $agg ? (int) $agg->judge_count : 0);
                $team->setAttribute('vote_count', $rawVotes);                  // raw count (display)
                $team->setAttribute('vote_count_effective', $effectiveVotes);  // 0 if hacker
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
