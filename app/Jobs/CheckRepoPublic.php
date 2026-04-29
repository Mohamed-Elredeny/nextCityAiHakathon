<?php

namespace App\Jobs;

use App\Models\Submission;
use App\Models\SubmissionValidation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class CheckRepoPublic implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $submissionId) {}

    public function handle(): void
    {
        $submission = Submission::find($this->submissionId);
        if (!$submission || empty($submission->repo_url)) return;

        $check = SubmissionValidation::firstOrNew([
            'submission_id' => $submission->id,
            'check_key' => 'repo_public',
        ]);

        try {
            $response = Http::timeout(10)->withHeaders(['User-Agent' => 'ACIE-Hackathon/1.0'])->get($submission->repo_url);
            if ($response->successful()) {
                $check->status = 'pass';
                $check->message = 'Repository is publicly reachable.';
            } else {
                $check->status = 'fail';
                $check->message = "HTTP {$response->status()} — repository may not be public.";
                $submission->update(['status' => Submission::STATUS_FLAGGED]);
            }
        } catch (\Throwable $e) {
            $check->status = 'fail';
            $check->message = 'Could not reach repository: ' . $e->getMessage();
            $submission->update(['status' => Submission::STATUS_FLAGGED]);
        }

        $check->checked_at = Carbon::now();
        $check->save();
    }
}
