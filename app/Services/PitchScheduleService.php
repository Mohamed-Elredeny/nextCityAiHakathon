<?php

namespace App\Services;

use App\Models\Edition;
use App\Models\JudgeAssignment;
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

    /**
     * Marks the top N teams from Round 1 as finalists. Optionally carries
     * over each finalist's Round 1 judge assignments to the Finals round so
     * the same judges score the same teams in the second pitch.
     *
     * Returns a structured summary the caller can display to the admin.
     */
    public function promoteFinalists(
        Edition $edition,
        int $count = 8,
        bool $carryJudgesToFinals = true,
    ): array {
        $finalistIds = Score::query()
            ->whereHas('team', fn ($q) => $q->where('edition_id', $edition->id))
            ->where('round', 'round1')
            ->whereNotNull('locked_at')
            ->selectRaw('team_id, AVG(weighted_total) as avg_total')
            ->groupBy('team_id')
            ->orderByDesc('avg_total')
            ->limit($count)
            ->pluck('team_id')
            ->all();

        $assignmentsCopied = 0;

        DB::transaction(function () use ($edition, $finalistIds, $carryJudgesToFinals, &$assignmentsCopied) {
            // Reset, then mark new finalists. Keeping this two-step so a
            // re-run with a different count doesn't leave stale flags.
            Team::where('edition_id', $edition->id)->update(['is_finalist' => false]);
            Team::whereIn('id', $finalistIds)->update(['is_finalist' => true]);

            if (!$carryJudgesToFinals || empty($finalistIds)) {
                return;
            }

            // Carry each finalist's round1 assignments forward to finals.
            // We pull the existing pairs (judge_id, team_id) and upsert
            // them as round=finals, defaulting recused=false on the new row.
            $round1 = JudgeAssignment::query()
                ->whereIn('team_id', $finalistIds)
                ->where('round', 'round1')
                ->where('recused', false)
                ->get(['judge_id', 'team_id']);

            foreach ($round1 as $row) {
                $exists = JudgeAssignment::where([
                    'judge_id' => $row->judge_id,
                    'team_id'  => $row->team_id,
                    'round'    => 'finals',
                ])->exists();
                if ($exists) continue;

                JudgeAssignment::create([
                    'judge_id' => $row->judge_id,
                    'team_id'  => $row->team_id,
                    'round'    => 'finals',
                    'recused'  => false,
                ]);
                $assignmentsCopied++;
            }
        });

        return [
            'finalist_ids'       => $finalistIds,
            'count'              => count($finalistIds),
            'assignments_copied' => $assignmentsCopied,
        ];
    }
}
