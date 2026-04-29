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

    public ?int $myVoteTeamId = null;
    public ?string $myVoteTeamName = null;
    public ?string $message = null;

    // Guest fields
    public string $voterName = '';
    public string $voterEmail = '';
    public bool $askForIdentity = true;

    public function mount(): void
    {
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

        $this->message = 'Thanks for voting!';
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
