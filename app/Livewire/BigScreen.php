<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Edition;
use App\Models\PeoplesChoiceVote;
use App\Models\Phase;
use App\Models\PitchSchedule;
use App\Models\Score;
use App\Models\Team;
use App\Models\User;
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

        $partners = User::partners()
            ->where('registration_status', 'approved')
            ->orderBy('organization')
            ->get();

        // Live attendance — current open session(s) and counts.
        $now = Carbon::now();
        $activeAttendanceSessions = AttendanceSession::query()
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->withCount('attendances')
            ->orderBy('starts_at')
            ->get();

        // Total checked-in across all sessions today (or recent if no time-bound sessions)
        $todayCheckIns = Attendance::query()
            ->whereDate('checked_in_at', $now->toDateString())
            ->count();

        return view('livewire.big-screen', [
            'teams' => $teams,
            'edition' => $edition,
            'currentPhase' => $currentPhase,
            'nowPitching' => $nowPitching,
            'serverNow' => Carbon::now(),
            'showcaseTeams' => $showcaseTeams,
            'partners' => $partners,
            'activeAttendanceSessions' => $activeAttendanceSessions,
            'todayCheckIns' => $todayCheckIns,
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

        $rawVoteCounts = PeoplesChoiceVote::query()
            ->whereIn('team_id', $teamIds)
            ->selectRaw('team_id, COUNT(*) as votes')
            ->groupBy('team_id')
            ->pluck('votes', 'team_id');

        // Hacker-flagged teams contribute 0 votes for the popularity calc
        $effectiveVoteCounts = $teams->mapWithKeys(function (Team $team) use ($rawVoteCounts) {
            $raw = (int) ($rawVoteCounts[$team->id] ?? 0);
            return [$team->id => $team->is_hacker ? 0 : $raw];
        });
        $maxVotes = $effectiveVoteCounts->max() ?: 0;

        return $teams
            ->map(function (Team $team) use ($aggregates, $rawVoteCounts, $effectiveVoteCounts, $maxVotes) {
                $agg = $aggregates->get($team->id);
                $judgesAvg = $agg ? round((float) $agg->avg_total, 2) : null;
                $rawVotes = (int) ($rawVoteCounts[$team->id] ?? 0);
                $effectiveVotes = (int) ($effectiveVoteCounts[$team->id] ?? 0);
                $popularity = $maxVotes > 0 ? round(($effectiveVotes / $maxVotes) * 10, 2) : 0.0;

                // 10% judges' avg penalty when team is flagged as hacker
                $penalisedJudgesAvg = $judgesAvg;
                if ($judgesAvg !== null && $team->is_hacker) {
                    $penalisedJudgesAvg = round($judgesAvg * (1 - Team::HACKER_JUDGE_PENALTY), 2);
                }

                $finalScore = $penalisedJudgesAvg !== null
                    ? round($penalisedJudgesAvg * self::JUDGES_WEIGHT + $popularity * self::POPULARITY_WEIGHT, 2)
                    : null;

                $team->setAttribute('avg_total', $judgesAvg);
                $team->setAttribute('avg_total_effective', $penalisedJudgesAvg);
                $team->setAttribute('judge_count', $agg ? (int) $agg->judge_count : 0);
                $team->setAttribute('vote_count', $rawVotes);
                $team->setAttribute('vote_count_effective', $effectiveVotes);
                $team->setAttribute('final_score', $finalScore);
                return $team;
            })
            ->sortByDesc(fn ($team) => $team->final_score ?? $team->avg_total ?? -1)
            ->values()
            ->take(10);
    }
}
