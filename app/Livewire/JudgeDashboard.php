<?php

namespace App\Livewire;

use App\Models\Edition;
use App\Models\JudgeAssignment;
use App\Models\Phase;
use App\Models\Score;
use App\Models\Team;
use App\Models\TeamComment;
use App\Models\TeamWorkspaceDraft;
use App\Models\Theme;
use App\Services\ScoringService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class JudgeDashboard extends Component
{
    public string $round = 'round1';
    public ?int $themeFilter = null;
    public string $teamSearch = '';
    public ?int $selectedTeamId = null;
    public array $scores = ['business_viability' => 0, 'technical_feasibility' => 0, 'impact_relevance' => 0, 'team_skills' => 0, 'presentation_skills' => 0];
    public string $comment = '';
    public string $newMessage = '';
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    public function mount(): void
    {
        $edition = Edition::active();
        if ($edition) {
            $finalsActive = Phase::where('edition_id', $edition->id)
                ->where('key', Phase::KEY_FINALIST_PITCHING)
                ->where('state', Phase::STATE_ACTIVE)
                ->exists();
            $this->round = $finalsActive ? 'finals' : 'round1';
        }
    }

    public function setRound(string $round): void
    {
        if (in_array($round, ['round1', 'finals'], true)) {
            $this->round = $round;
            $this->selectedTeamId = null;
            $this->resetScoreForm();
        }
    }

    public function setThemeFilter(?int $themeId): void
    {
        $this->themeFilter = $themeId ?: null;
    }

    public function clearFilters(): void
    {
        $this->themeFilter = null;
        $this->teamSearch = '';
    }

    public function selectTeam(int $teamId): void
    {
        $this->selectedTeamId = $teamId;
        $this->errorMessage = null;
        $this->successMessage = null;
        $this->newMessage = '';

        $existing = Score::where([
            'judge_id' => Auth::id(),
            'team_id' => $teamId,
            'round' => $this->round,
        ])->first();
        if ($existing) {
            foreach (array_keys(Score::WEIGHTS) as $c) {
                $this->scores[$c] = (float) ($existing->{$c} ?? 0);
            }
            $this->comment = (string) $existing->comment;
        } else {
            $this->resetScoreForm();
        }
    }

    protected function resetScoreForm(): void
    {
        $this->scores = ['business_viability' => 0, 'technical_feasibility' => 0, 'impact_relevance' => 0, 'team_skills' => 0, 'presentation_skills' => 0];
        $this->comment = '';
    }

    public function saveDraft(ScoringService $service): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;
        if (!$this->selectedTeamId) return;
        $team = Team::find($this->selectedTeamId);
        if (!$team) return;
        if (!$service->isJudgingOpen($team->edition_id, $this->round)) {
            $this->errorMessage = 'Judging window is not open for this round right now.';
            return;
        }
        try {
            $service->saveDraft(Auth::user(), $team, $this->round, $this->scores, $this->comment ?: null);
            $this->successMessage = 'Draft saved.';
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function sendMessage(): void
    {
        $body = trim($this->newMessage);
        if (!$this->selectedTeamId || $body === '') return;

        $assigned = JudgeAssignment::where('judge_id', Auth::id())
            ->where('team_id', $this->selectedTeamId)
            ->where('recused', false)
            ->exists();
        if (!$assigned) return;

        TeamComment::create([
            'team_id' => $this->selectedTeamId,
            'user_id' => Auth::id(),
            'channel' => TeamComment::CHANNEL_JUDGE,
            'body' => $body,
        ]);
        $this->newMessage = '';
    }

    public string $recuseReason = '';

    public function selfRecuse(): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;
        $reason = trim($this->recuseReason);
        if (!$this->selectedTeamId || $reason === '') {
            $this->errorMessage = 'Please describe the conflict of interest before recusing.';
            return;
        }

        $assignment = JudgeAssignment::where([
            'judge_id' => Auth::id(),
            'team_id' => $this->selectedTeamId,
            'round' => $this->round,
        ])->first();
        if (!$assignment) return;

        // Refuse to self-recuse if the score is already locked.
        $hasLocked = Score::where([
            'judge_id' => Auth::id(),
            'team_id' => $this->selectedTeamId,
            'round' => $this->round,
        ])->whereNotNull('locked_at')->exists();
        if ($hasLocked) {
            $this->errorMessage = 'This score is already locked. Contact an organizer to remove it.';
            return;
        }

        $assignment->update([
            'recused' => true,
            'recused_reason' => $reason,
        ]);

        \App\Models\AuditLog::record('judge.self_recused', $assignment, [
            'judge_id' => Auth::id(),
            'team_id' => $this->selectedTeamId,
            'round' => $this->round,
            'reason' => $reason,
        ]);

        $this->selectedTeamId = null;
        $this->recuseReason = '';
        $this->successMessage = 'Recused. The team has been removed from your list.';
    }

    public function lockScore(ScoringService $service): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;
        if (!$this->selectedTeamId) return;
        $team = Team::find($this->selectedTeamId);
        if (!$team) return;
        if (!$service->isJudgingOpen($team->edition_id, $this->round)) {
            $this->errorMessage = 'Judging window is not open for this round right now.';
            return;
        }
        try {
            $service->lock(Auth::user(), $team, $this->round, $this->scores, $this->comment ?: null);
            $this->successMessage = 'Score locked. The leaderboard will update.';
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        $judge = Auth::user();
        $assignments = JudgeAssignment::where('judge_id', $judge->id)
            ->where('round', $this->round)
            ->where('recused', false)
            ->with(['team.theme', 'team.submission', 'team.teamMembers.user'])
            ->get();

        $teamIds = $assignments->pluck('team_id');
        $myScores = Score::whereIn('team_id', $teamIds)
            ->where('judge_id', $judge->id)
            ->where('round', $this->round)
            ->get()
            ->keyBy('team_id');

        $teams = $assignments->map(function ($a) use ($myScores) {
            $a->team->setAttribute('my_score', $myScores->get($a->team_id));
            return $a->team;
        });

        $availableThemes = $teams
            ->filter(fn ($t) => $t->theme)
            ->pluck('theme')
            ->unique('id')
            ->sortBy('sort_order')
            ->values();

        if ($this->themeFilter) {
            $teams = $teams->filter(fn ($t) => $t->theme_id === $this->themeFilter)->values();
        }
        if (filled($this->teamSearch)) {
            $needle = mb_strtolower($this->teamSearch);
            $teams = $teams->filter(fn ($t) => str_contains(mb_strtolower($t->name), $needle))->values();
        }

        $weights = Score::WEIGHTS;
        $weightedPreview = collect($weights)->reduce(
            fn ($carry, $w, $c) => $carry + ((float) ($this->scores[$c] ?? 0)) * $w, 0.0
        );

        $selectedTeam = $this->selectedTeamId ? $teams->firstWhere('id', $this->selectedTeamId) : null;
        $isLocked = $selectedTeam?->my_score?->locked_at !== null;

        $selectedTeamDrafts = $selectedTeam
            ? TeamWorkspaceDraft::where('team_id', $selectedTeam->id)->get()->keyBy('section_key')
            : collect();

        $messages = $selectedTeam
            ? TeamComment::where('team_id', $selectedTeam->id)
                ->where('channel', TeamComment::CHANNEL_JUDGE)
                ->with('user.roles')
                ->latest()
                ->limit(50)
                ->get()
            : collect();

        return view('livewire.judge-dashboard', [
            'teams' => $teams,
            'availableThemes' => $availableThemes,
            'weights' => $weights,
            'weightedPreview' => round($weightedPreview, 2),
            'selectedTeam' => $selectedTeam,
            'isLocked' => $isLocked,
            'selectedTeamDrafts' => $selectedTeamDrafts,
            'messages' => $messages,
        ]);
    }
}
