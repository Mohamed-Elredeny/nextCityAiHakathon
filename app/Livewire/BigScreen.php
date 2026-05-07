<?php

namespace App\Livewire;

use App\Models\Edition;
use App\Models\PeoplesChoiceVote;
use App\Models\Phase;
use App\Models\PitchSchedule;
use App\Models\Score;
use App\Models\Team;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class BigScreen extends Component
{
    public const JUDGES_WEIGHT = PublicLeaderboard::JUDGES_WEIGHT;
    public const POPULARITY_WEIGHT = PublicLeaderboard::POPULARITY_WEIGHT;

    public string $round = 'round1';

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

    public function render()
    {
        $edition = Edition::active();
        $teams = $edition ? $this->teamsRanked($edition) : collect();
        $currentPhase = $edition
            ? Phase::where('edition_id', $edition->id)->where('state', Phase::STATE_ACTIVE)->orderBy('sort_order')->first()
            : null;
        $nowPitching = PitchSchedule::query()
            ->where('round', $this->round)
            ->whereNotNull('started_at')
            ->whereNull('ended_at')
            ->with('team')
            ->orderByDesc('started_at')
            ->first();

        // Showcase ribbon — every active team in the active edition with their
        // members. Teams without a logo render an initials placeholder.
        $showcaseTeams = $edition
            ? Team::query()
                ->where('edition_id', $edition->id)
                ->where('status', 'active')
                ->with(['members' => fn ($q) => $q->orderBy('users.name')])
                ->orderBy('name')
                ->get()
            : collect();

        return view('livewire.big-screen', [
            'teams' => $teams,
            'edition' => $edition,
            'currentPhase' => $currentPhase,
            'nowPitching' => $nowPitching,
            'serverNow' => Carbon::now(),
            'showcaseTeams' => $showcaseTeams,
        ])->layout('components.layouts.bigscreen');
    }

    protected function teamsRanked(Edition $edition): Collection
    {
        $teams = Team::query()
            ->where('edition_id', $edition->id)
            ->where('status', 'active')
            ->when($this->round === 'finals', fn ($q) => $q->where('is_finalist', true))
            ->with('theme')
            ->get();

        $teamIds = $teams->pluck('id');

        $aggregates = Score::query()
            ->whereIn('team_id', $teamIds)
            ->where('round', $this->round)
            ->whereNotNull('locked_at')
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
                $popularity = $maxVotes > 0 ? round(($votes / $maxVotes) * 10, 2) : 0.0;
                $finalScore = $judgesAvg !== null
                    ? round($judgesAvg * self::JUDGES_WEIGHT + $popularity * self::POPULARITY_WEIGHT, 2)
                    : null;

                $team->setAttribute('avg_total', $judgesAvg);
                $team->setAttribute('judge_count', $agg ? (int) $agg->judge_count : 0);
                $team->setAttribute('vote_count', $votes);
                $team->setAttribute('final_score', $finalScore);
                return $team;
            })
            ->sortByDesc(fn ($team) => $team->final_score ?? $team->avg_total ?? -1)
            ->values()
            ->take(10);
    }
}
