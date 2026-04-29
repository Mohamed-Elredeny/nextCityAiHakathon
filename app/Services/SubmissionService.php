<?php

namespace App\Services;

use App\Events\SubmissionReceived;
use App\Models\AuditLog;
use App\Models\Phase;
use App\Models\Submission;
use App\Models\Team;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SubmissionService
{
    public const REQUIRED_CHECKS = [
        'report_pdf'   => 'Solution Report PDF uploaded',
        'slides_url'   => 'Slide deck link provided',
        'repo_url'     => 'Code repository link provided',
        'video_url'    => 'Demo video link provided',
        'ai_disclosure'=> 'AI tools disclosure filled',
    ];

    private const PHASE_FOR_ROUND = [
        Submission::ROUND_ONE => Phase::KEY_SUBMISSION_WINDOW,
        Submission::ROUND_FINALS => Phase::KEY_FINALS_SUBMISSION_WINDOW,
    ];

    public function isWindowOpen(Team $team, string $round = Submission::ROUND_ONE, ?CarbonInterface $now = null): bool
    {
        $now ??= Carbon::now();
        $key = self::PHASE_FOR_ROUND[$round] ?? null;
        if (! $key) {
            return false;
        }
        $phase = Phase::where('edition_id', $team->edition_id)
            ->where('key', $key)
            ->first();
        if (! $phase) {
            return false;
        }
        if ($phase->state === Phase::STATE_ACTIVE) {
            return $now->between($phase->starts_at, $phase->ends_at);
        }
        return false;
    }

    public function checklist(Submission $submission): array
    {
        return [
            'report_pdf'    => filled($submission->report_pdf_path),
            'slides_url'    => filled($submission->slides_url),
            'repo_url'      => filled($submission->repo_url),
            'video_url'     => filled($submission->video_url),
            'ai_disclosure' => filled($submission->ai_disclosure_text),
        ];
    }

    public function submit(Team $team, int $userId, array $data, string $round = Submission::ROUND_ONE, ?CarbonInterface $now = null): Submission
    {
        $now ??= Carbon::now();

        if (! $this->isWindowOpen($team, $round, $now)) {
            throw new RuntimeException('The submission window for ' . Submission::ROUNDS[$round] . ' is not currently open.');
        }

        if ($round === Submission::ROUND_FINALS && ! $team->is_finalist) {
            throw new RuntimeException('Only finalist teams may submit for the Finals round.');
        }

        return DB::transaction(function () use ($team, $userId, $data, $round, $now) {
            $submission = Submission::firstOrNew([
                'team_id' => $team->id,
                'round' => $round,
            ]);

            foreach (['slides_url', 'repo_url', 'video_url', 'ai_disclosure_text'] as $field) {
                if (array_key_exists($field, $data)) {
                    $submission->{$field} = $data[$field];
                }
            }
            if (! empty($data['report_pdf_path'])) {
                $submission->report_pdf_path = $data['report_pdf_path'];
            }

            $checklist = $this->checklist($submission);
            $missing = array_keys(array_filter($checklist, fn ($ok) => ! $ok));
            if (! empty($missing)) {
                throw new RuntimeException(
                    'Submission is incomplete. Missing: ' . implode(', ', array_map(
                        fn ($k) => self::REQUIRED_CHECKS[$k], $missing
                    ))
                );
            }

            $submission->status = Submission::STATUS_SUBMITTED;
            $submission->submitted_at = $now;
            $submission->submitted_by = $userId;
            $submission->save();

            AuditLog::record('submission.submitted', $submission, [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'round' => $round,
                'submitted_at' => $now->toIso8601String(),
            ]);

            event(new SubmissionReceived($submission));

            return $submission;
        });
    }

    public function flag(Submission $submission, string $reason, int $userId): void
    {
        $submission->update([
            'status' => Submission::STATUS_FLAGGED,
            'reject_reason' => $reason,
        ]);
        AuditLog::record('submission.flagged', $submission, ['reason' => $reason]);
    }

    public function accept(Submission $submission, int $userId): void
    {
        $submission->update(['status' => Submission::STATUS_ACCEPTED]);
        AuditLog::record('submission.accepted', $submission);
    }

    public function reject(Submission $submission, string $reason, int $userId): void
    {
        $submission->update([
            'status' => Submission::STATUS_REJECTED,
            'reject_reason' => $reason,
        ]);
        AuditLog::record('submission.rejected', $submission, ['reason' => $reason]);
    }
}
