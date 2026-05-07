<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Edition;
use App\Models\PitchSchedule;
use App\Models\Submission;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Round 1 pitch schedule seeder.
 *
 * Distributes every team that submitted Assignment 1 across the 3 parallel
 * pitching rooms on Day 2 (Friday May 8, 2026). Starts at 11:10 with a
 * 15-minute slot per team and no break — the actual schedule used on the
 * day was compressed because earlier sessions overran.
 *
 * Per-team slot: 15 minutes (pitch + Q&A + changeover).
 *
 * Re-running this seeder wipes the existing round1 pitch schedule and
 * regenerates it — safe to run as many times as needed.
 *
 * Usage:
 *   php artisan db:seed --class=Round1PitchScheduleSeeder
 */
class Round1PitchScheduleSeeder extends Seeder
{
    /** Pitching day kick-off */
    private const PITCH_DATE = '2026-05-08';
    private const START_TIME = '11:10';
    private const SLOT_DURATION_MIN = 15;
    /** Set to a non-null 'HH:MM' to insert a break at that time (e.g. '12:30'). */
    private const BREAK_START = null;
    private const BREAK_DURATION_MIN = 15;

    public function run(): void
    {
        $teams = $this->teamsThatSubmittedAssignmentOne();

        if ($teams->isEmpty()) {
            $this->command?->warn('No teams have submitted Assignment 1 yet — nothing to schedule.');
            return;
        }

        $this->command?->info('Scheduling ' . $teams->count() . ' teams across 3 parallel rooms…');

        // Wipe existing round1 schedule for a clean slate
        DB::table('pitch_schedule')->where('round', 'round1')->delete();

        // Round-robin distribution across rooms A, B, C
        $rooms = PitchSchedule::ROOMS;          // ['A', 'B', 'C']
        $perRoom = collect($rooms)->mapWithKeys(fn ($r) => [$r => collect()])->all();

        foreach ($teams->values() as $i => $team) {
            $room = $rooms[$i % count($rooms)];
            $perRoom[$room][] = $team;
        }

        $totalCreated = 0;
        $summary = [];

        foreach ($perRoom as $room => $list) {
            $list = collect($list)->values();
            $this->scheduleRoom($room, $list);
            $summary[] = [
                'room' => 'Room ' . $room,
                'teams' => $list->count(),
                'first' => $list->first()?->name,
                'last' => $list->last()?->name,
            ];
            $totalCreated += $list->count();
        }

        $this->command?->table(['Room', 'Teams', 'First', 'Last'], $summary);
        $this->command?->info("Created {$totalCreated} pitch slots for round 1.");
        $this->command?->newLine();
        $this->command?->info('View the schedule in Admin → Hackathon → Pitch Schedule.');
    }

    /**
     * Teams whose AssignmentSubmission has at least one uploaded file
     * for the first active assignment in the active edition.
     */
    private function teamsThatSubmittedAssignmentOne(): \Illuminate\Support\Collection
    {
        $edition = Edition::active();
        if (! $edition) {
            $this->command?->warn('No active edition found.');
            return collect();
        }

        // "Assignment 1" = the first active assignment in the active edition,
        // ordered by sort_order then id (the same way the workspace lists them).
        $assignment = Assignment::query()
            ->where('is_active', true)
            ->where(function ($q) use ($edition) {
                $q->whereNull('edition_id')->orWhere('edition_id', $edition->id);
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if (! $assignment) {
            $this->command?->warn('No active assignment found.');
            return collect();
        }

        $this->command?->info('Using assignment: "' . $assignment->title . '" (id=' . $assignment->id . ')');

        // Teams that have a submission with at least one file
        $teamIds = AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->whereHas('files')
            ->pluck('team_id');

        return Team::query()
            ->where('edition_id', $edition->id)
            ->where('status', 'active')
            ->whereIn('id', $teamIds)
            ->orderBy('name')
            ->get();
    }

    /**
     * Schedule a single room: walk forward 10-minute slots from 9:45,
     * skip over the 11:00–11:15 break window if a slot would overlap it.
     */
    private function scheduleRoom(string $room, \Illuminate\Support\Collection $teams): void
    {
        $current = CarbonImmutable::parse(self::PITCH_DATE . ' ' . self::START_TIME);
        $breakStart = self::BREAK_START
            ? CarbonImmutable::parse(self::PITCH_DATE . ' ' . self::BREAK_START)
            : null;
        $breakEnd = $breakStart?->addMinutes(self::BREAK_DURATION_MIN);

        foreach ($teams as $index => $team) {
            $slotEnd = $current->addMinutes(self::SLOT_DURATION_MIN);

            // If a break is configured and this slot would overlap it, push
            // the slot to start after the break.
            if ($breakStart && $breakEnd && $current->lt($breakEnd) && $slotEnd->gt($breakStart)) {
                $current = $breakEnd;
                $slotEnd = $current->addMinutes(self::SLOT_DURATION_MIN);
            }

            PitchSchedule::create([
                'team_id' => $team->id,
                'round' => 'round1',
                'room' => $room,
                'slot_index' => $index + 1,
                'scheduled_start' => $current,
            ]);

            $current = $slotEnd;
        }
    }
}
