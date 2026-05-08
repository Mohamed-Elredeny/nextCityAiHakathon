<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Edition;
use App\Models\PeoplesChoiceVote as VoteModel;
use App\Models\Team;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PeoplesChoiceVote extends Component
{
    public const COOKIE_NAME = 'aiu_voter_token';

    /** Max votes accepted from one IP within the last hour. */
    public const IP_HOURLY_LIMIT = 5;

    /** Auto-flag a team as hacker if it gets >= this many votes in one hour. */
    public const TEAM_VELOCITY_FLAG = 120;

    /** Min seconds the form must be on screen before a vote is accepted. */
    public const MIN_FORM_AGE_SEC = 3;

    /** Bot-like substrings in User-Agent that we reject outright. */
    public const BAD_UA_NEEDLES = [
        'curl', 'wget', 'python', 'go-http', 'okhttp', 'httpclient',
        'java/', 'libwww', 'phantomjs', 'headlesschrome', 'puppeteer',
        'selenium', 'playwright', 'bot', 'spider', 'crawler', 'scraper',
    ];

    public ?int $myVoteTeamId = null;
    public ?string $myVoteTeamName = null;
    public ?string $message = null;

    // Guest fields
    public string $voterName = '';
    public string $voterEmail = '';
    public bool $askForIdentity = true;

    /** Honeypot — must stay empty. Bots fill in every field they see. */
    public string $hp_website = '';

    /** Server-anchored timestamp when the form first rendered (epoch seconds). */
    public int $formOpenedAt = 0;

    public function mount(): void
    {
        // Anchor a server-side timestamp so we can later require a minimum
        // dwell time before accepting a vote (blocks instant-fire scripts).
        $this->formOpenedAt = time();

        // 1) Logged-in users: check by user_id
        if (Auth::check()) {
            $vote = VoteModel::where('user_id', Auth::id())->with('team')->first();
            if ($vote) {
                $this->lockToVote($vote);
                return;
            }
            $this->voterName = Auth::user()->name ?? '';
            $this->voterEmail = Auth::user()->email ?? '';
            $this->askForIdentity = false;
            return;
        }

        // 2) Guest with cookie: look up by token
        $token = request()->cookie(self::COOKIE_NAME);
        if ($token) {
            $vote = VoteModel::where('voter_token', $token)->with('team')->first();
            if ($vote) {
                $this->lockToVote($vote);
                return;
            }
        }
    }

    protected function lockToVote(VoteModel $vote): void
    {
        $this->myVoteTeamId = $vote->team_id;
        $this->myVoteTeamName = $vote->team?->name;
        $this->voterName = (string) ($vote->voter_name ?? Auth::user()?->name ?? '');
        $this->voterEmail = (string) ($vote->voter_email ?? Auth::user()?->email ?? '');
        $this->askForIdentity = false;
    }

    public function startVoting(): void
    {
        if (Auth::check()) {
            $this->askForIdentity = false;
            return;
        }

        $this->validate([
            'voterName'  => 'required|string|min:2|max:120',
            'voterEmail' => 'required|email|max:255',
        ]);

        // Reject email already used
        $existing = VoteModel::where('voter_email', strtolower(trim($this->voterEmail)))->first();
        if ($existing) {
            $this->message = 'This email has already cast a vote.';
            return;
        }

        $this->askForIdentity = false;
        $this->message = null;
    }

    public function vote(int $teamId): void
    {
        $this->message = null;

        // ───── ANTI-SPAM LAYER 1 · honeypot ─────
        // Real users never see this field; bots fill in every text input
        // they find. We log and silently fail (no message) so the bot can't
        // tell why it failed and tune around it.
        if ($this->hp_website !== '') {
            $this->logRejection($teamId, 'honeypot_filled');
            $this->message = 'Thanks for voting!';
            return;
        }

        // ───── ANTI-SPAM LAYER 2 · min form age ─────
        // Reject votes cast in <3 seconds (impossible for a human reading
        // the team list and clicking).
        if ($this->formOpenedAt > 0 && (time() - $this->formOpenedAt) < self::MIN_FORM_AGE_SEC) {
            $this->logRejection($teamId, 'too_fast');
            $this->message = 'Please take a moment to review the teams before voting.';
            return;
        }

        // ───── ANTI-SPAM LAYER 3 · bot user-agent ─────
        $ua = mb_strtolower((string) request()->userAgent());
        foreach (self::BAD_UA_NEEDLES as $needle) {
            if ($ua && str_contains($ua, $needle)) {
                $this->logRejection($teamId, 'bad_ua', ['ua' => $ua]);
                $this->message = 'Vote rejected.';
                return;
            }
        }

        // ───── ANTI-SPAM LAYER 4 · per-IP rate limit ─────
        $ip = (string) request()->ip();
        $hourlyFromIp = VoteModel::where('ip_address', $ip)
            ->where('voted_at', '>=', Carbon::now()->subHour())
            ->count();
        if ($hourlyFromIp >= self::IP_HOURLY_LIMIT) {
            $this->logRejection($teamId, 'ip_rate_limit', [
                'ip' => $ip,
                'count_last_hour' => $hourlyFromIp,
            ]);
            $this->message = 'Too many votes from this network in the last hour. Please try again later.';
            return;
        }

        // Already voted? bail
        if ($this->myVoteTeamId) {
            $this->message = 'You have already voted.';
            return;
        }

        // Guests must identify first
        if (!Auth::check() && $this->askForIdentity) {
            $this->message = 'Please enter your name and email first.';
            return;
        }

        $edition = Edition::active();
        $valid = Team::where('id', $teamId)
            ->where('edition_id', $edition?->id)
            ->where('status', 'active')
            ->exists();
        if (!$valid) return;

        // Re-check uniqueness server-side
        if (Auth::check() && VoteModel::where('user_id', Auth::id())->exists()) {
            $this->message = 'You have already voted.';
            return;
        }
        if (!Auth::check()) {
            $email = strtolower(trim($this->voterEmail));
            if ($email === '' || VoteModel::where('voter_email', $email)->exists()) {
                $this->message = 'This email has already voted.';
                return;
            }
        }

        $token = (string) Str::uuid();
        $vote = VoteModel::create([
            'user_id'     => Auth::id(),
            'team_id'     => $teamId,
            'voted_at'    => Carbon::now(),
            'voter_name'  => Auth::check() ? null : trim($this->voterName),
            'voter_email' => Auth::check() ? null : strtolower(trim($this->voterEmail)),
            'voter_token' => $token,
            'ip_address'  => request()->ip(),
        ]);

        // Set a long-lived cookie so this browser is recognized on return visits
        Cookie::queue(self::COOKIE_NAME, $token, 60 * 24 * 365);

        $vote->load('team');
        $this->lockToVote($vote);

        AuditLog::record('peoples_choice.vote', null, [
            'team_id' => $teamId,
            'voter_email' => Auth::check() ? null : strtolower(trim($this->voterEmail)),
            'auth' => Auth::check() ? 'user' : 'guest',
        ]);

        // ───── ANTI-SPAM LAYER 5 · velocity-based auto-flag ─────
        // If a single team is suddenly receiving an abnormal burst of votes,
        // mark it as hacker so its votes are zeroed and judges' avg is
        // penalised on the leaderboard. Admins can clear the flag from the
        // Teams resource if it was a false positive.
        $hourlyForTeam = VoteModel::where('team_id', $teamId)
            ->where('voted_at', '>=', Carbon::now()->subHour())
            ->count();
        if ($hourlyForTeam >= self::TEAM_VELOCITY_FLAG) {
            $team = Team::find($teamId);
            if ($team && ! $team->is_hacker) {
                $team->forceFill([
                    'is_hacker' => true,
                    'hacker_reason' => 'Auto-flagged: ' . $hourlyForTeam . ' votes in the last hour (threshold ' . self::TEAM_VELOCITY_FLAG . ').',
                    'hacker_marked_at' => Carbon::now(),
                    'hacker_marked_by' => null,
                ])->save();

                AuditLog::record('peoples_choice.auto_flag_hacker', null, [
                    'team_id' => $teamId,
                    'hourly_votes' => $hourlyForTeam,
                ]);
            }
        }

        $this->message = 'Thanks for voting!';
    }

    /**
     * Log a rejected vote attempt to the audit trail. Used by every layer
     * of the anti-spam stack so admins can audit attacks afterwards.
     */
    protected function logRejection(int $teamId, string $reason, array $extra = []): void
    {
        AuditLog::record('peoples_choice.vote_rejected', null, array_merge([
            'team_id' => $teamId,
            'reason' => $reason,
            'ip' => request()->ip(),
            'auth' => Auth::check() ? 'user' : 'guest',
        ], $extra));
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        $edition = Edition::active();
        $teams = $edition
            ? Team::where('edition_id', $edition->id)
                ->where('status', 'active')
                ->with(['theme', 'submission'])
                ->orderBy('name')
                ->get()
            : collect();

        // Load each team's "problem" and "solution" workspace drafts in one query
        $drafts = $teams->isNotEmpty()
            ? \App\Models\TeamWorkspaceDraft::whereIn('team_id', $teams->pluck('id'))
                ->whereIn('section_key', ['problem', 'solution'])
                ->get()
                ->groupBy('team_id')
                ->map(fn ($g) => $g->keyBy('section_key'))
            : collect();

        return view('livewire.peoples-choice-vote', [
            'teams' => $teams,
            'drafts' => $drafts,
            'voteUrl' => url('/vote'),
        ]);
    }
}
