<?php

namespace App\Livewire;

use App\Models\Assignment;
use App\Models\AssignmentScore;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class JudgeAssignments extends Component
{
    public ?int $assignmentId = null;
    public ?int $submissionId = null;

    public string $score = '';
    public string $feedback = '';
    public ?string $error = null;
    public ?string $saved = null;

    public function selectAssignment(int $id): void
    {
        $this->assignmentId = $id;
        $this->submissionId = null;
        $this->resetForm();
    }

    public function selectSubmission(int $id): void
    {
        $this->submissionId = $id;
        $this->resetForm();

        $existing = AssignmentScore::where('assignment_submission_id', $id)
            ->where('judge_id', Auth::id())
            ->first();

        if ($existing) {
            $this->score = (string) $existing->score;
            $this->feedback = (string) $existing->feedback;
        }
    }

    public function saveScore(): void
    {
        $this->error = null;
        $this->saved = null;

        if (! $this->submissionId) {
            $this->error = 'Pick a submission first.';
            return;
        }

        $sub = AssignmentSubmission::with('assignment')->find($this->submissionId);
        if (! $sub || ! $sub->assignment) {
            $this->error = 'Submission not found.';
            return;
        }

        $max = (float) $sub->assignment->max_score;

        $this->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:' . $max],
            'feedback' => ['nullable', 'string', 'max:5000'],
        ], [
            'score.max' => 'Score cannot exceed ' . rtrim(rtrim(number_format($max, 2), '0'), '.') . '.',
        ]);

        AssignmentScore::updateOrCreate(
            [
                'assignment_submission_id' => $sub->id,
                'judge_id' => Auth::id(),
            ],
            [
                'score' => (float) $this->score,
                'feedback' => trim($this->feedback) ?: null,
                'graded_at' => now(),
            ],
        );

        $this->saved = 'Grade saved.';
    }

    private function resetForm(): void
    {
        $this->score = '';
        $this->feedback = '';
        $this->error = null;
        $this->saved = null;
        $this->resetErrorBag();
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        $assignments = Assignment::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->withCount('submissions')
            ->get();

        $assignment = $this->assignmentId ? $assignments->firstWhere('id', $this->assignmentId) : null;

        $submissions = $assignment
            ? AssignmentSubmission::with(['team', 'files', 'scores' => function ($q) {
                    $q->where('judge_id', Auth::id());
                }])
                ->withCount('files')
                ->where('assignment_id', $assignment->id)
                ->whereHas('files') // only show teams that actually submitted something
                ->get()
                ->sortBy(fn ($s) => $s->team->name)
                ->values()
            : collect();

        $submission = $this->submissionId ? AssignmentSubmission::with(['team', 'files.uploader', 'assignment'])->find($this->submissionId) : null;

        return view('livewire.judge-assignments', [
            'assignments' => $assignments,
            'assignment' => $assignment,
            'submissions' => $submissions,
            'submission' => $submission,
        ]);
    }
}
