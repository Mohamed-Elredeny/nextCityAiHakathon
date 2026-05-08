<?php

namespace App\Services;

use App\Events\ScoreLocked;
use App\Models\AuditLog;
use App\Models\JudgeAssignment;
use App\Models\Phase;
use App\Models\Score;
use App\Models\Team;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ScoringService
{
    public function isJudgingOpen(int $editionId, string $round, ?CarbonInterface $now = null): bool
    {
        $now ??= Carbon::now();
        $key = $round === 'finals' ? Phase::KEY_FINALIST_PITCHING : Phase::KEY_ROUND1_PITCHING;
        $phase = Phase::where('edition_id', $editionId)->where('key', $key)->first();
        if (!$phase) return false;
        return $phase->state === Phase::STATE_ACTIVE
            && $now->between($phase->starts_at, $phase->ends_at);
    }

    public function saveDraft(User $judge, Team $team, string $round, array $scores, ?string $comment = null): Score
    {
        $assignment = JudgeAssignment::where([
            'judge_id' => $judge->id, 'team_id' => $team->id, 'round' => $round,
        ])->first();
        if (!$assignment || $assignment->recused) {
            throw new RuntimeException('You are not assigned to this team for this round.');
        }

        $score = Score::firstOrNew([
            'judge_id' => $judge->id, 'team_id' => $team->id, 'round' => $round,
        ]);
        if ($score->locked_at) {
            throw new RuntimeException('This score is already locked and cannot be edited.');
        }

        foreach (Score::WEIGHTS as $criterion => $_) {
            if (array_key_exists($criterion, $scores)) {
                $score->{$criterion} = $scores[$criterion] === null
                    ? null
                    : max(0, min(10, (float) $scores[$criterion]));
            }
        }
        $score->comment = $comment;
        $score->save();
        return $score;
    }

    public function lock(User $judge, Team $team, string $round, array $scores, ?string $comment = null, ?CarbonInterface $now = null): Score
    {
        $now ??= Carbon::now();

        $assignment = JudgeAssignment::where([
            'judge_id' => $judge->id, 'team_id' => $team->id, 'round' => $round,
        ])->first();
        if (!$assignment || $assignment->recused) {
            throw new RuntimeException('You are not assigned to this team for this round.');
        }

        return DB::transaction(function () use ($judge, $team, $round, $scores, $comment, $now) {
            $score = Score::firstOrNew([
                'judge_id' => $judge->id, 'team_id' => $team->id, 'round' => $round,
            ]);
            if ($score->locked_at) {
                throw new RuntimeException('This score is already locked.');
            }
            foreach (Score::WEIGHTS as $criterion => $_) {
                if (!array_key_exists($criterion, $scores) || $scores[$criterion] === null) {
                    throw new RuntimeException("Please rate every criterion (0–10) before locking. Missing: " . str_replace('_', ' ', $criterion) . '.');
                }
                $score->{$criterion} = max(0, min(10, (float) $scores[$criterion]));
            }
            $score->comment = $comment;
            $score->weighted_total = $score->calculateWeightedTotal();
            $score->locked_at = $now;
            $score->save();

            AuditLog::record('score.locked', $score, [
                'judge_id' => $judge->id,
                'team_id' => $team->id,
                'round' => $round,
                'weighted_total' => $score->weighted_total,
            ]);

            event(new ScoreLocked(
                $team->edition_id,
                $team->id,
                $round,
                (float) $score->weighted_total,
            ));

            return $score;
        });
    }

    public function teamAggregate(int $teamId, string $round): array
    {
        $rows = Score::where('team_id', $teamId)
            ->where('round', $round)
            ->whereNotNull('locked_at')
            ->get();
        if ($rows->isEmpty()) {
            return ['avg' => null, 'count' => 0];
        }
        return [
            'avg' => round((float) $rows->avg('weighted_total'), 2),
            'count' => $rows->count(),
        ];
    }
}
