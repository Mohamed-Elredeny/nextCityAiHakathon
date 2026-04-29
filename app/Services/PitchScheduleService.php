<?php

namespace App\Services;

use App\Models\Edition;
use App\Models\PitchSchedule;
use App\Models\Score;
use App\Models\Team;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PitchScheduleService
{
    public const ROUND1_PITCH_MINUTES = 8;
    public const FINALS_PITCH_MINUTES = 13;

    public function generate(Edition $edition, string $round, string $order, ?CarbonInterface $startAt = null): int
    {
        $minutes = $round === 'finals' ? self::FINALS_PITCH_MINUTES : self::ROUND1_PITCH_MINUTES;
        $startAt ??= $round === 'finals'
            ? Carbon::parse('2026-05-08 13:00:00', 'Africa/Cairo')
            : Carbon::parse('2026-05-08 09:20:00', 'Africa/Cairo');

        $teams = Team::where('edition_id', $edition->id)
            ->where('status', 'active')
            ->when($round === 'finals', fn ($q) => $q->where('is_finalist', true))
            ->get();

        $teams = match ($order) {
            'random' => $teams->shuffle(),
            'name' => $teams->sortBy('name')->values(),
            'score_desc' => $this->sortByScore($teams, 'round1', 'desc'),
            default => $teams,
        };

        return DB::transaction(function () use ($teams, $round, $minutes, $startAt) {
            PitchSchedule::where('round', $round)->whereIn('team_id', $teams->pluck('id'))->delete();
            $count = 0;
            $current = $startAt->copy();
            foreach ($teams->values() as $i => $team) {
                PitchSchedule::create([
                    'team_id' => $team->id,
                    'round' => $round,
                    'slot_index' => $i + 1,
                    'scheduled_start' => $current->copy(),
                ]);
                $current = $current->copy()->addMinutes($minutes);
                $count++;
            }
            return $count;
        });
    }

    protected function sortByScore($teams, string $round, string $direction)
    {
        $aggregates = Score::whereIn('team_id', $teams->pluck('id'))
            ->where('round', $round)
            ->whereNotNull('locked_at')
            ->selectRaw('team_id, AVG(weighted_total) as avg_total')
            ->groupBy('team_id')
            ->pluck('avg_total', 'team_id');
        return $teams->sortBy(
            fn ($t) => (float) ($aggregates[$t->id] ?? 0),
            SORT_REGULAR,
            $direction === 'desc',
        )->values();
    }

    public function start(PitchSchedule $slot): void
    {
        PitchSchedule::where('round', $slot->round)
            ->whereNotNull('started_at')
            ->whereNull('ended_at')
            ->update(['ended_at' => Carbon::now()]);
        $slot->update(['started_at' => Carbon::now(), 'ended_at' => null]);
    }

    public function end(PitchSchedule $slot): void
    {
        $slot->update(['ended_at' => Carbon::now()]);
    }

    public function promoteFinalists(Edition $edition, int $count = 5): array
    {
        $aggregates = Score::query()
            ->whereHas('team', fn ($q) => $q->where('edition_id', $edition->id))
            ->where('round', 'round1')
            ->whereNotNull('locked_at')
            ->selectRaw('team_id, AVG(weighted_total) as avg_total')
            ->groupBy('team_id')
            ->orderByDesc('avg_total')
            ->limit($count)
            ->pluck('team_id')
            ->all();

        DB::transaction(function () use ($edition, $aggregates) {
            Team::where('edition_id', $edition->id)->update(['is_finalist' => false]);
            Team::whereIn('id', $aggregates)->update(['is_finalist' => true]);
        });

        return $aggregates;
    }
}
