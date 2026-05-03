<?php

namespace App\Livewire;

use App\Models\MentorAssignment;
use App\Models\MentorNote;
use App\Models\MentorRotationSlot;
use App\Models\Team;
use App\Models\TeamComment;
use App\Models\TeamWorkspaceDraft;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MentorDashboard extends Component
{
    public ?int $selectedTeamId = null;
    public string $newNote = '';
    public string $newMessage = '';

    public function selectTeam(int $teamId): void
    {
        $this->selectedTeamId = $teamId;
        $this->newNote = '';
        $this->newMessage = '';
    }

    public function postNote(): void
    {
        $this->newNote = trim($this->newNote);
        if (!$this->selectedTeamId || $this->newNote === '') return;
        MentorNote::create([
            'mentor_id' => Auth::id(),
            'team_id' => $this->selectedTeamId,
            'body' => $this->newNote,
        ]);
        $this->newNote = '';
    }

    public function sendMessage(): void
    {
        $body = trim($this->newMessage);
        if (!$this->selectedTeamId || $body === '') return;

        $assigned = MentorAssignment::where('mentor_id', Auth::id())
            ->where('team_id', $this->selectedTeamId)
            ->exists();
        if (!$assigned) return;

        TeamComment::create([
            'team_id' => $this->selectedTeamId,
            'user_id' => Auth::id(),
            'channel' => TeamComment::CHANNEL_MENTOR,
            'body' => $body,
        ]);
        $this->newMessage = '';
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        $mentorId = Auth::id();
        $teamIds = MentorAssignment::where('mentor_id', $mentorId)->pluck('team_id');
        $teams = Team::whereIn('id', $teamIds)->with(['theme', 'submission', 'teamMembers.user'])->orderBy('name')->get();
        $selectedTeam = $this->selectedTeamId ? $teams->firstWhere('id', $this->selectedTeamId) : null;
        $notes = $selectedTeam
            ? MentorNote::where('mentor_id', $mentorId)->where('team_id', $selectedTeam->id)->latest()->get()
            : collect();
        $rotation = MentorRotationSlot::where('mentor_id', $mentorId)
            ->orderBy('slot_start')
            ->with('team')
            ->get();

        $selectedTeamDrafts = $selectedTeam
            ? TeamWorkspaceDraft::where('team_id', $selectedTeam->id)->get()->keyBy('section_key')
            : collect();

        $messages = $selectedTeam
            ? TeamComment::where('team_id', $selectedTeam->id)
                ->where('channel', TeamComment::CHANNEL_MENTOR)
                ->with('user.roles')
                ->latest()
                ->limit(50)
                ->get()
            : collect();

        return view('livewire.mentor-dashboard', [
            'teams' => $teams,
            'selectedTeam' => $selectedTeam,
            'notes' => $notes,
            'rotation' => $rotation,
            'selectedTeamDrafts' => $selectedTeamDrafts,
            'messages' => $messages,
        ]);
    }
}
