<?php

namespace App\Livewire;

use App\Models\Phase;
use App\Models\Submission;
use App\Models\Team;
use App\Models\TeamApplication;
use App\Models\TeamComment;
use App\Models\TeamMember;
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

        $team->is_recruiting = (bool) $this->isRecruiting;
        $team->recruitment_message = trim($this->recruitmentMessage) ?: null;
        $team->looking_for_skills = trim($this->lookingForSkills) ?: null;
        $team->save();

        $this->recruitingSaved = $team->is_recruiting
            ? 'Recruiting status saved — your team is now visible in the community.'
            : 'Recruiting paused — your team is hidden from the recruitment list.';
    }

    public function approveApplication(int $applicationId): void
    {
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

        DB::transaction(function () use ($app, $team) {
            TeamMember::create([
                'team_id' => $team->id,
                'user_id' => $app->user_id,
                'role_in_team' => $app->skills,
                'is_leader' => false,
            ]);
            $app->update([
                'status' => TeamApplication::STATUS_APPROVED,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'response_message' => $this->applicationResponses[$app->id] ?? null,
            ]);

            $user = $app->user;
            if ($user && method_exists($user, 'syncRoles')) {
                if (!$user->hasRole('team_leader') && !$user->hasRole('team_member')) {
                    $user->assignRole('team_member');
                }
            }
        });

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
        $app->load(['team', 'reviewer']);
        app(CommunityNotificationService::class)->notifyApplicationDecision($app);

        unset($this->applicationResponses[$applicationId]);
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
