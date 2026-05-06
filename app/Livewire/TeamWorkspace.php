<?php

namespace App\Livewire;

use App\Models\Phase;
use App\Models\Submission;
use App\Models\Team;
use App\Models\TeamApplication;
use App\Models\TeamComment;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\TeamWorkspaceDraft;
use App\Models\Theme;
use App\Services\CommunityNotificationService;
use App\Services\SubmissionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class TeamWorkspace extends Component
{
    use WithFileUploads;

    public ?int $teamId = null;
    public ?int $themeId = null;
    public string $step = 'overview';
    public string $activeSection = 'problem';
    public array $drafts = [];
    public array $savedAt = [];
    public string $newComment = '';
    public string $activeChannel = 'team';
    public string $activeRound = Submission::ROUND_ONE;

    public string $tagline = '';
    public $newLogo = null;
    public $newBanner = null;
    public ?string $identitySaved = null;

    public bool $isRecruiting = false;
    public string $recruitmentMessage = '';
    public string $lookingForSkills = '';
    public ?string $recruitingSaved = null;
    public array $applicationResponses = [];

    public const STEPS = ['overview', 'report', 'submission', 'discussion', 'team'];

    public string $slidesUrl = '';
    public string $repoUrl = '';
    public string $videoUrl = '';
    public string $aiDisclosure = '';
    public $reportPdf = null;
    public ?string $submitError = null;

    public const SECTION_META = [
        'cover' => [
            'icon' => 'document-text',
            'recommended' => 80,
            'hint' => 'A one-page cover with project name, tagline, team identity, and theme.',
            'placeholder' => "Project name: …\nTagline: …\nTeam: …\nTheme: …",
        ],
        'problem' => [
            'icon' => 'exclamation-triangle',
            'recommended' => 250,
            'hint' => 'What pain point in the city are you tackling? Who hurts because of it, and how big is the problem?',
            'placeholder' => "What's broken in our city?\nWho is affected?\nWhy hasn't it been solved?\nQuantify the impact (numbers, evidence)…",
        ],
        'solution' => [
            'icon' => 'light-bulb',
            'recommended' => 350,
            'hint' => "Your AI-powered approach. Describe the user journey and what makes the solution differentiated.",
            'placeholder' => "Our solution in one sentence: …\nKey user journey:\n  1. …\n  2. …\nWhat's novel? Why us?",
        ],
        'architecture' => [
            'icon' => 'cpu-chip',
            'recommended' => 300,
            'hint' => 'Technical stack, AI models, data sources, deployment plan. A diagram pasted as ASCII or described works.',
            'placeholder' => "Frontend: …\nBackend / API: …\nAI models / data: …\nInfra & deployment: …\nKey trade-offs we made…",
        ],
        'impact' => [
            'icon' => 'chart-bar',
            'recommended' => 250,
            'hint' => 'Who benefits and how much. Include measurable outcomes (KPIs) and how it scales beyond a pilot.',
            'placeholder' => "Direct beneficiaries: …\nMeasurable KPIs in year 1: …\nWhy this scales city-wide…",
        ],
        'roles' => [
            'icon' => 'user-group',
            'recommended' => 120,
            'hint' => 'Who did what. Each member with their core contribution.',
            'placeholder' => "• Leader · …  — …\n• Member 1 · ML — built …\n• Member 2 · UX — designed …",
        ],
        'references' => [
            'icon' => 'book-open',
            'recommended' => 100,
            'hint' => 'Datasets, papers, libraries, prior art you built on.',
            'placeholder' => "1. Dataset: …\n2. Paper: …\n3. Library / SDK: …",
        ],
        'ai_disclosure' => [
            'icon' => 'sparkles',
            'recommended' => 100,
            'hint' => 'Mandatory: which AI tools did you use during the hackathon, and for what.',
            'placeholder' => "ChatGPT — used for ideation and initial outline.\nGitHub Copilot — boilerplate scaffolding.\nClaude — code review and bug fixing.",
        ],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        $team = $user?->currentTeam();
        if (!$team) {
            // No active team — bounce to recruiting so the user can join one.
            $this->redirect(route('community.teams'), navigate: false);
            return;
        }
        $this->teamId = $team->id;
        $this->themeId = $team->theme_id;
        $this->tagline = (string) $team->tagline;
        $this->isRecruiting = (bool) $team->is_recruiting;
        $this->recruitmentMessage = (string) $team->recruitment_message;
        $this->lookingForSkills = (string) $team->looking_for_skills;
        $this->loadDrafts();
        $this->loadSubmission();
    }

    public function saveTeamIdentity(): void
    {
        $team = Team::find($this->teamId);
        if (!$team) return;
        if (Auth::id() !== $team->leader_id) return;

        $this->validate([
            'tagline' => 'nullable|string|max:160',
            'newLogo' => 'nullable|image|max:1024',
            'newBanner' => 'nullable|image|max:3072',
        ]);

        $team->tagline = $this->tagline ?: null;

        if ($this->newLogo) {
            if ($team->logo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($team->logo_path);
            }
            $team->logo_path = $this->newLogo->store('team-logos', 'public');
            $this->newLogo = null;
        }
        if ($this->newBanner) {
            if ($team->banner_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($team->banner_path);
            }
            $team->banner_path = $this->newBanner->store('team-banners', 'public');
            $this->newBanner = null;
        }
        $team->save();
        $this->identitySaved = 'Team identity updated';
    }

    protected function loadSubmission(): void
    {
        $submission = Submission::where('team_id', $this->teamId)
            ->where('round', $this->activeRound)
            ->first();

        // For finals, prefill from round1 if no finals submission exists yet
        if (!$submission && $this->activeRound === Submission::ROUND_FINALS) {
            $submission = Submission::where('team_id', $this->teamId)
                ->where('round', Submission::ROUND_ONE)
                ->first();
        }

        if ($submission) {
            $this->slidesUrl = (string) $submission->slides_url;
            $this->repoUrl = (string) $submission->repo_url;
            $this->videoUrl = (string) $submission->video_url;
            $this->aiDisclosure = (string) $submission->ai_disclosure_text;
        } else {
            $this->slidesUrl = '';
            $this->repoUrl = '';
            $this->videoUrl = '';
            $this->aiDisclosure = '';
        }
    }

    public function setRound(string $round): void
    {
        if (!in_array($round, [Submission::ROUND_ONE, Submission::ROUND_FINALS], true)) {
            return;
        }
        $this->activeRound = $round;
        $this->reportPdf = null;
        $this->submitError = null;
        $this->loadSubmission();
    }

    protected function loadDrafts(): void
    {
        $rows = TeamWorkspaceDraft::where('team_id', $this->teamId)->pluck('body', 'section_key')->toArray();
        foreach (array_keys(TeamWorkspaceDraft::SECTIONS) as $key) {
            $this->drafts[$key] = (string) ($rows[$key] ?? '');
        }
    }

    public function setSection(string $key): void
    {
        if (array_key_exists($key, TeamWorkspaceDraft::SECTIONS)) {
            $this->activeSection = $key;
        }
    }

    public function goTo(string $step): void
    {
        if (in_array($step, self::STEPS, true)) {
            $this->step = $step;
        }
    }

    public function openSubmission(string $round): void
    {
        $this->setRound($round);
        $this->step = 'submission';
    }

    public function saveDraft(string $key): void
    {
        if (!array_key_exists($key, TeamWorkspaceDraft::SECTIONS) || !$this->teamId) {
            return;
        }
        TeamWorkspaceDraft::updateOrCreate(
            ['team_id' => $this->teamId, 'section_key' => $key],
            ['body' => $this->drafts[$key] ?? '', 'updated_by' => Auth::id()],
        );
        $this->savedAt[$key] = now()->toIso8601String();
        $this->dispatch('draft-saved', section: $key);
    }

    public function updated($field): void
    {
        if (str_starts_with($field, 'drafts.')) {
            $key = substr($field, 7);
            $this->saveDraft($key);
        }
    }

    public function pickTheme(int $themeId): void
    {
        $team = Team::find($this->teamId);
        if (!$team) {
            return;
        }

        $themeLockPhase = Phase::where('edition_id', $team->edition_id)
            ->where('key', Phase::KEY_THEME_LOCK_WINDOW)
            ->first();
        $isLockOpen = $themeLockPhase && $themeLockPhase->state === Phase::STATE_ACTIVE;

        if (!$isLockOpen && $team->theme_id) {
            $this->dispatch('theme-locked');
            return;
        }

        $valid = Theme::where('id', $themeId)
            ->where('edition_id', $team->edition_id)
            ->exists();
        if (!$valid) {
            return;
        }

        DB::transaction(function () use ($team, $themeId) {
            $team->update(['theme_id' => $themeId]);
        });
        $this->themeId = $themeId;
        $this->dispatch('theme-saved');
    }

    public function saveSubmissionDraft(): void
    {
        if (!$this->teamId) return;

        // Drafts are intentionally permissive — teams iterate on partial,
        // half-typed values throughout the hackathon. Strict URL/format
        // validation runs in SubmissionService::submit() at lock-in time.
        $this->validate([
            'reportPdf' => 'nullable|file|mimes:pdf|max:51200',
            'slidesUrl' => 'nullable|string|max:500',
            'repoUrl' => 'nullable|string|max:500',
            'videoUrl' => 'nullable|string|max:500',
            'aiDisclosure' => 'nullable|string|max:2000',
        ]);

        $sub = Submission::firstOrNew([
            'team_id' => $this->teamId,
            'round' => $this->activeRound,
        ]);
        $sub->slides_url = $this->slidesUrl ?: null;
        $sub->repo_url = $this->repoUrl ?: null;
        $sub->video_url = $this->videoUrl ?: null;
        $sub->ai_disclosure_text = $this->aiDisclosure ?: null;
        if ($this->reportPdf) {
            $path = $this->reportPdf->store('submissions/reports', 'public');
            $sub->report_pdf_path = $path;
            $this->reportPdf = null;
        }
        $sub->status = $sub->status ?: Submission::STATUS_DRAFT;
        $sub->save();
        $this->dispatch('submission-draft-saved');
    }

    public function submitFinal(SubmissionService $service): void
    {
        $this->submitError = null;
        $team = Team::find($this->teamId);
        if (!$team) return;

        $user = Auth::user();
        if ($team->leader_id !== $user->id) {
            $this->submitError = 'Only the team leader may submit the final entry.';
            return;
        }

        $this->saveSubmissionDraft();

        try {
            $service->submit($team, $user->id, [
                'slides_url' => $this->slidesUrl,
                'repo_url' => $this->repoUrl,
                'video_url' => $this->videoUrl,
                'ai_disclosure_text' => $this->aiDisclosure,
            ], $this->activeRound);
            $this->dispatch('submission-final');
        } catch (\Throwable $e) {
            $this->submitError = $e->getMessage();
        }
    }

    public function setChannel(string $channel): void
    {
        if (in_array($channel, [TeamComment::CHANNEL_TEAM, TeamComment::CHANNEL_MENTOR, TeamComment::CHANNEL_JUDGE], true)) {
            $this->activeChannel = $channel;
        }
    }

    public function saveRecruiting(): void
    {
        $team = Team::find($this->teamId);
        if (!$team) return;
        if (Auth::id() !== $team->leader_id) return;

        $this->validate([
            'recruitmentMessage' => 'nullable|string|max:1000',
            'lookingForSkills' => 'nullable|string|max:200',
        ]);

        // A team without a logo on the public recruiting board looks abandoned.
        // Require leaders to upload one before going live.
        if ($this->isRecruiting && !$team->logo_path) {
            $this->recruitingSaved = null;
            $this->addError('isRecruiting', 'Upload a team logo first — recruiting cards look abandoned without one.');
            $this->isRecruiting = false;
            return;
        }

        // Once the team is at the size cap, recruiting is meaningless.
        $size = $team->teamMembers()->count();
        if ($this->isRecruiting && $size >= Team::MAX_MEMBERS) {
            $this->recruitingSaved = null;
            $this->addError('isRecruiting', 'Team is already at the ' . Team::MAX_MEMBERS . '-member limit.');
            $this->isRecruiting = false;
            return;
        }

        $team->is_recruiting = (bool) $this->isRecruiting;
        $team->recruitment_message = trim($this->recruitmentMessage) ?: null;
        $team->looking_for_skills = trim($this->lookingForSkills) ?: null;
        $team->save();

        $this->recruitingSaved = $team->is_recruiting
            ? 'Recruiting status saved — your team is now visible in the community.'
            : 'Recruiting paused — your team is hidden from the recruitment list.';
    }

    public ?string $applicationError = null;

    public function approveApplication(int $applicationId): void
    {
        $this->applicationError = null;
        $team = Team::find($this->teamId);
        if (!$team || Auth::id() !== $team->leader_id) return;

        $app = TeamApplication::where('id', $applicationId)
            ->where('team_id', $team->id)
            ->where('status', TeamApplication::STATUS_PENDING)
            ->first();
        if (!$app) return;

        if (TeamMember::where('user_id', $app->user_id)->exists()) {
            $app->update([
                'status' => TeamApplication::STATUS_REJECTED,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'response_message' => $this->applicationResponses[$applicationId]
                    ?? 'Applicant joined another team in the meantime.',
            ]);
            $app->load(['team', 'reviewer']);
            app(CommunityNotificationService::class)->notifyApplicationDecision($app);
            unset($this->applicationResponses[$applicationId]);
            return;
        }

        // Capacity check: refuse if the team is already at MAX_MEMBERS.
        $currentSize = TeamMember::where('team_id', $team->id)->count();
        if ($currentSize >= Team::MAX_MEMBERS) {
            $app->update([
                'status' => TeamApplication::STATUS_REJECTED,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'response_message' => 'Team is at the ' . Team::MAX_MEMBERS . '-member limit.',
            ]);
            $team->update(['is_recruiting' => false]);
            unset($this->applicationResponses[$applicationId]);
            return;
        }

        try {
            DB::transaction(function () use ($app, $team) {
                // Re-check inside the transaction with row-locks to avoid races
                // (two leaders/admins approving at the same moment, or the same
                // applicant getting approved on two teams concurrently).
                $alreadyOnTeam = TeamMember::where('user_id', $app->user_id)
                    ->lockForUpdate()
                    ->exists();
                if ($alreadyOnTeam) {
                    throw new \RuntimeException('Applicant joined another team in the meantime.');
                }
                $sizeNow = TeamMember::where('team_id', $team->id)
                    ->lockForUpdate()
                    ->count();
                if ($sizeNow >= Team::MAX_MEMBERS) {
                    throw new \RuntimeException('Team is at the ' . Team::MAX_MEMBERS . '-member limit.');
                }

                $applicant = User::find($app->user_id);
            TeamMember::create([
                'team_id' => $team->id,
                'user_id' => $app->user_id,
                'role_in_team' => $app->skills,
                'role_category' => $applicant?->primary_role,
                'is_leader' => false,
            ]);
            $app->update([
                'status' => TeamApplication::STATUS_APPROVED,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'response_message' => $this->applicationResponses[$app->id] ?? null,
            ]);

            if ($applicant && !$applicant->hasRole('team_leader') && !$applicant->hasRole('team_member')) {
                $applicant->assignRole('team_member');
            }

                // Auto-disable recruiting once every needed_role is filled (P1.8)
                $team->refresh()->load('teamMembers');
                $needed = (array) ($team->needed_roles ?? []);
                if ($team->is_recruiting && !empty($needed)) {
                    $filled = $team->role_coverage['filled'];
                    $stillNeeded = array_diff($needed, $filled);
                    if (empty($stillNeeded)) {
                        $team->update(['is_recruiting' => false, 'needed_roles' => []]);
                    }
                }
            });
        } catch (\Throwable $e) {
            $this->applicationError = $e->getMessage();
            return;
        }

        \App\Models\AuditLog::record('application.approved', $app, [
            'team_id' => $team->id,
            'applicant_id' => $app->user_id,
            'reviewer_id' => Auth::id(),
        ]);

        $app->load(['team', 'reviewer']);
        app(CommunityNotificationService::class)->notifyApplicationDecision($app);

        unset($this->applicationResponses[$applicationId]);
    }

    public function rejectApplication(int $applicationId): void
    {
        $team = Team::find($this->teamId);
        if (!$team || Auth::id() !== $team->leader_id) return;

        $app = TeamApplication::where('id', $applicationId)
            ->where('team_id', $team->id)
            ->where('status', TeamApplication::STATUS_PENDING)
            ->first();
        if (!$app) return;

        $app->update([
            'status' => TeamApplication::STATUS_REJECTED,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'response_message' => $this->applicationResponses[$applicationId] ?? null,
        ]);

        \App\Models\AuditLog::record('application.rejected', $app, [
            'team_id' => $team->id,
            'applicant_id' => $app->user_id,
            'reviewer_id' => Auth::id(),
        ]);

        $app->load(['team', 'reviewer']);
        app(CommunityNotificationService::class)->notifyApplicationDecision($app);

        unset($this->applicationResponses[$applicationId]);
    }

    public function transferLeadership(int $newLeaderUserId): void
    {
        $team = Team::find($this->teamId);
        if (!$team || Auth::id() !== $team->leader_id) return;
        if ($newLeaderUserId === $team->leader_id) return;

        $isOnTeam = TeamMember::where('team_id', $team->id)
            ->where('user_id', $newLeaderUserId)
            ->exists();
        if (!$isOnTeam) return;

        $previousLeaderId = $team->leader_id;
        DB::transaction(function () use ($team, $newLeaderUserId) {
            TeamMember::where('team_id', $team->id)->update(['is_leader' => false]);
            TeamMember::where('team_id', $team->id)
                ->where('user_id', $newLeaderUserId)
                ->update(['is_leader' => true]);
            $team->update(['leader_id' => $newLeaderUserId]);

            $newLeader = User::find($newLeaderUserId);
            if ($newLeader && !$newLeader->hasRole('team_leader')) {
                $newLeader->assignRole('team_leader');
            }
        });

        \App\Models\AuditLog::record('team.leadership_transferred', $team, [
            'team_id' => $team->id,
            'from_user_id' => $previousLeaderId,
            'to_user_id' => $newLeaderUserId,
        ]);

        $this->dispatch('leadership-transferred');
    }

    public function kickMember(int $userId): void
    {
        $team = Team::find($this->teamId);
        if (!$team || Auth::id() !== $team->leader_id) return;
        if ($userId === $team->leader_id) return;

        TeamMember::where('team_id', $team->id)
            ->where('user_id', $userId)
            ->delete();

        \App\Models\AuditLog::record('team.member_kicked', $team, [
            'team_id' => $team->id,
            'kicked_user_id' => $userId,
            'by_user_id' => Auth::id(),
        ]);

        $this->dispatch('member-removed');
    }

    public function leaveTeam(): void
    {
        $team = Team::find($this->teamId);
        if (!$team) return;

        $userId = Auth::id();
        if ($userId === $team->leader_id) return;

        TeamMember::where('team_id', $team->id)
            ->where('user_id', $userId)
            ->delete();

        \App\Models\AuditLog::record('team.member_left', $team, [
            'user_id' => $userId,
            'team_id' => $team->id,
        ]);

        $this->teamId = null;
        $this->dispatch('left-team');
        $this->redirect(route('community.teams'), navigate: false);
    }

    public function disbandTeam(): void
    {
        $team = Team::find($this->teamId);
        if (!$team || Auth::id() !== $team->leader_id) return;

        $remainingMembers = TeamMember::where('team_id', $team->id)
            ->where('user_id', '!=', $team->leader_id)
            ->count();

        // Only allow disband when the leader is the last person standing.
        if ($remainingMembers > 0) return;

        DB::transaction(function () use ($team) {
            TeamMember::where('team_id', $team->id)->delete();
            TeamApplication::where('team_id', $team->id)
                ->where('status', TeamApplication::STATUS_PENDING)
                ->update(['status' => TeamApplication::STATUS_REJECTED, 'reviewed_at' => now(), 'response_message' => 'Team was disbanded.']);
            $team->update([
                'status' => 'withdrawn',
                'is_recruiting' => false,
            ]);
        });

        \App\Models\AuditLog::record('team.disbanded', $team, [
            'team_id' => $team->id,
            'leader_id' => $team->leader_id,
        ]);

        $this->teamId = null;
        $this->dispatch('team-disbanded');
        $this->redirect(route('community.teams'), navigate: false);
    }

    public function postComment(): void
    {
        $this->newComment = trim($this->newComment);
        if (!$this->teamId || $this->newComment === '') {
            return;
        }
        TeamComment::create([
            'team_id' => $this->teamId,
            'user_id' => Auth::id(),
            'channel' => $this->activeChannel,
            'body' => $this->newComment,
        ]);
        $this->newComment = '';
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        $team = $this->teamId ? Team::with(['theme', 'leader', 'members'])->find($this->teamId) : null;
        $themes = $team ? Theme::where('edition_id', $team->edition_id)->orderBy('sort_order')->get() : collect();
        $themeLockPhase = $team
            ? Phase::where('edition_id', $team->edition_id)->where('key', Phase::KEY_THEME_LOCK_WINDOW)->first()
            : null;
        $isThemeLockOpen = $themeLockPhase?->state === Phase::STATE_ACTIVE;
        $comments = $team
            ? TeamComment::where('team_id', $team->id)
                ->where('channel', $this->activeChannel)
                ->with('user.roles')
                ->latest()
                ->limit(50)
                ->get()
            : collect();

        $channelCounts = $team
            ? TeamComment::where('team_id', $team->id)
                ->selectRaw('channel, count(*) as c')
                ->groupBy('channel')
                ->pluck('c', 'channel')
                ->toArray()
            : [];

        $service = app(SubmissionService::class);
        $allSubmissions = $team
            ? Submission::where('team_id', $team->id)->get()->keyBy('round')
            : collect();
        $submission = $allSubmissions->get($this->activeRound);

        // Build a working snapshot for the checklist (combines saved submission + unsaved field state)
        $working = $submission ? clone $submission : new Submission;
        if ($this->slidesUrl) $working->slides_url = $this->slidesUrl;
        if ($this->repoUrl) $working->repo_url = $this->repoUrl;
        if ($this->videoUrl) $working->video_url = $this->videoUrl;
        if ($this->aiDisclosure) $working->ai_disclosure_text = $this->aiDisclosure;
        $checklist = $service->checklist($working);

        $isSubmissionWindowOpen = $team ? $service->isWindowOpen($team, $this->activeRound) : false;
        $isSubmitted = $submission && $submission->status !== Submission::STATUS_DRAFT;
        $isLeader = $team && Auth::id() === $team->leader_id;
        $finalsAllowed = $team && $team->is_finalist;

        $wordCounts = [];
        $sectionsFilled = 0;
        $totalWords = 0;
        foreach (array_keys(TeamWorkspaceDraft::SECTIONS) as $key) {
            $body = trim((string) ($this->drafts[$key] ?? ''));
            $w = $body === '' ? 0 : str_word_count(strip_tags($body));
            $wordCounts[$key] = $w;
            $totalWords += $w;
            if ($w >= 20) {
                $sectionsFilled++;
            }
        }

        $pendingApplications = $team
            ? TeamApplication::where('team_id', $team->id)
                ->where('status', TeamApplication::STATUS_PENDING)
                ->with('user')
                ->orderBy('created_at')
                ->get()
            : collect();

        $reviewedApplications = $team
            ? TeamApplication::where('team_id', $team->id)
                ->whereIn('status', [TeamApplication::STATUS_APPROVED, TeamApplication::STATUS_REJECTED, TeamApplication::STATUS_WITHDRAWN])
                ->with('user')
                ->orderByDesc('reviewed_at')
                ->limit(20)
                ->get()
            : collect();

        return view('livewire.team-workspace', [
            'team' => $team,
            'themes' => $themes,
            'isThemeLockOpen' => $isThemeLockOpen,
            'sections' => TeamWorkspaceDraft::SECTIONS,
            'sectionMeta' => self::SECTION_META,
            'wordCounts' => $wordCounts,
            'sectionsFilled' => $sectionsFilled,
            'totalSections' => count(TeamWorkspaceDraft::SECTIONS),
            'totalWords' => $totalWords,
            'comments' => $comments,
            'channels' => TeamComment::CHANNELS,
            'channelCounts' => $channelCounts,
            'submission' => $submission,
            'allSubmissions' => $allSubmissions,
            'rounds' => Submission::ROUNDS,
            'isSubmissionWindowOpen' => $isSubmissionWindowOpen,
            'checklist' => $checklist,
            'requiredChecks' => SubmissionService::REQUIRED_CHECKS,
            'isSubmitted' => $isSubmitted,
            'isLeader' => $isLeader,
            'finalsAllowed' => $finalsAllowed,
            'pendingApplications' => $pendingApplications,
            'reviewedApplications' => $reviewedApplications,
        ]);
    }
}
