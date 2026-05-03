<?php

namespace App\Livewire;

use App\Models\Edition;
use App\Models\Team;
use App\Models\TeamApplication;
use App\Models\TeamMember;
use App\Services\CommunityNotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class RecruitingTeams extends Component
{
    public ?int $applyToTeamId = null;
    public string $applicationMessage = '';
    public string $applicationSkills = '';
    public ?string $applyError = null;
    public ?string $applySuccess = null;

    public function openApplyForm(int $teamId): void
    {
        $this->applyError = null;
        $this->applySuccess = null;
        $this->applicationMessage = '';
        $this->applicationSkills = '';
        $this->applyToTeamId = $teamId;
    }

    public function cancelApply(): void
    {
        $this->applyToTeamId = null;
        $this->applicationMessage = '';
        $this->applicationSkills = '';
        $this->applyError = null;
    }

    public function submitApplication(): void
    {
        $this->applyError = null;
        $this->applySuccess = null;

        $user = Auth::user();
        if (!$user) {
            $this->applyError = 'Please sign in to apply.';
            return;
        }

        $team = Team::find($this->applyToTeamId);
        if (!$team || !$team->is_recruiting) {
            $this->applyError = 'This team is not currently recruiting.';
            return;
        }

        if ($team->leader_id === $user->id) {
            $this->applyError = 'You are the leader of this team.';
            return;
        }

        if (TeamMember::where('user_id', $user->id)->exists()) {
            $this->applyError = 'You are already a member of a team. Leave your current team before applying to another.';
            return;
        }

        $existing = TeamApplication::where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->whereIn('status', [TeamApplication::STATUS_PENDING, TeamApplication::STATUS_APPROVED])
            ->first();

        if ($existing) {
            $this->applyError = $existing->status === TeamApplication::STATUS_PENDING
                ? 'You already have a pending application for this team.'
                : 'You have already been approved for this team.';
            return;
        }

        // 24-hour cooldown after a rejection by the same team — prevents spam.
        $recentRejection = TeamApplication::where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->where('status', TeamApplication::STATUS_REJECTED)
            ->where('reviewed_at', '>=', now()->subHours(24))
            ->latest('reviewed_at')
            ->first();
        if ($recentRejection) {
            $hoursLeft = (int) ceil(now()->diffInHours($recentRejection->reviewed_at->addDay(), false));
            $this->applyError = "You can re-apply to this team in {$hoursLeft} hour" . ($hoursLeft === 1 ? '' : 's') . '.';
            return;
        }

        $this->validate([
            'applicationMessage' => 'required|string|min:20|max:1000',
            'applicationSkills' => 'nullable|string|max:200',
        ]);

        $app = TeamApplication::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'message' => trim($this->applicationMessage),
            'skills' => trim($this->applicationSkills) ?: null,
            'status' => TeamApplication::STATUS_PENDING,
        ]);

        $app->load(['team', 'user']);
        app(CommunityNotificationService::class)->notifyApplicationReceived($app);

        $this->applyToTeamId = null;
        $this->applicationMessage = '';
        $this->applicationSkills = '';
        $this->applySuccess = 'Application sent — the team leader will review it.';
    }

    public function withdrawApplication(int $applicationId): void
    {
        $user = Auth::user();
        if (!$user) return;

        $app = TeamApplication::find($applicationId);
        if (!$app || $app->user_id !== $user->id) return;
        if ($app->status !== TeamApplication::STATUS_PENDING) return;

        $app->update(['status' => TeamApplication::STATUS_WITHDRAWN]);

        $app->load(['team', 'user']);
        app(CommunityNotificationService::class)->notifyApplicationWithdrawn($app);

        $this->applySuccess = 'Application withdrawn.';
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        $edition = Edition::active();
        $teams = $edition
            ? Team::query()
                ->where('edition_id', $edition->id)
                ->where('status', 'active')
                ->where('is_recruiting', true)
                ->with(['leader', 'theme', 'members'])
                ->withCount('members')
                ->orderByDesc('updated_at')
                ->get()
            : collect();

        $userId = Auth::id();
        $myApplications = $userId
            ? TeamApplication::where('user_id', $userId)
                ->with(['team.leader'])
                ->orderByDesc('created_at')
                ->get()
                ->keyBy('team_id')
            : collect();

        $alreadyOnTeam = $userId ? TeamMember::where('user_id', $userId)->exists() : false;

        return view('livewire.recruiting-teams', [
            'teams' => $teams,
            'myApplications' => $myApplications,
            'alreadyOnTeam' => $alreadyOnTeam,
        ]);
    }
}
