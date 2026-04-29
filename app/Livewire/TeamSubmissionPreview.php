<?php

namespace App\Livewire;

use App\Models\Submission;
use App\Models\Team;
use App\Models\TeamWorkspaceDraft;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TeamSubmissionPreview extends Component
{
    public string $slug;
    public string $activeRound = Submission::ROUND_ONE;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function setRound(string $round): void
    {
        if (in_array($round, [Submission::ROUND_ONE, Submission::ROUND_FINALS], true)) {
            $this->activeRound = $round;
        }
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        $team = Team::with(['theme', 'leader', 'members', 'submissions'])
            ->where('slug', $this->slug)
            ->first();

        abort_if(!$team, 404);

        $drafts = TeamWorkspaceDraft::where('team_id', $team->id)
            ->get()
            ->keyBy('section_key');

        $submissionsByRound = $team->submissions->keyBy('round');
        $submission = $submissionsByRound->get($this->activeRound);

        return view('livewire.team-submission-preview', [
            'team' => $team,
            'drafts' => $drafts,
            'submission' => $submission,
            'submissionsByRound' => $submissionsByRound,
            'rounds' => Submission::ROUNDS,
            'sections' => TeamWorkspaceDraft::SECTIONS,
            'sectionMeta' => TeamWorkspace::SECTION_META,
        ]);
    }
}
