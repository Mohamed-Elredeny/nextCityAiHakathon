<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Edition;
use App\Models\Phase;
use App\Models\RestrictedAwardVote as VoteModel;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Restricted voting page for the two judged-by-community awards.
 * Only accessible to registered users who are eligible per
 * User::canVoteRestrictedAwards(). The page renders a list of teams under
 * each award and lets the user pick (and re-pick) one team per award
 * while the voting phase is open.
 */
class RestrictedAwardVote extends Component
{
    /** Currently selected team per award_key. Loaded from existing votes on mount. */
    public array $myVotes = [
        VoteModel::AWARD_BEST_AI       => null,
        VoteModel::AWARD_MOST_IMPACTFUL => null,
    ];

    public ?string $message = null;
    public ?string $error = null;

    public function mount(): void
    {
        $user = Auth::user();
        $edition = Edition::active();
        if (!$user || !$edition) {
            return;
        }

        $existing = VoteModel::where('user_id', $user->id)
            ->where('edition_id', $edition->id)
            ->get(['award_key', 'team_id']);

        foreach ($existing as $row) {
            $this->myVotes[$row->award_key] = (int) $row->team_id;
        }
    }

    public function vote(string $awardKey, int $teamId): void
    {
        $this->message = null;
        $this->error = null;

        if (!array_key_exists($awardKey, VoteModel::AWARDS)) {
            $this->error = 'Unknown award.';
            return;
        }

        $user = Auth::user();
        if (!$user || !$user->canVoteRestrictedAwards()) {
            $this->error = 'You are not eligible to vote on these awards.';
            return;
        }

        $edition = Edition::active();
        if (!$edition) {
            $this->error = 'No active edition.';
            return;
        }

        if (!$this->isVotingOpen($edition->id)) {
            $this->error = 'Voting for these awards is not open right now.';
            return;
        }

        // Team must belong to the active edition, be active, AND be a
        // finalist (Top 8). Defense in depth: the UI already filters to
        // finalists, but a tampered request must still be rejected.
        $team = Team::where('id', $teamId)
            ->where('edition_id', $edition->id)
            ->where('status', 'active')
            ->where('is_finalist', true)
            ->first();
        if (!$team) {
            $this->error = 'Selected team is not eligible (not a finalist).';
            return;
        }

        // Members of a team cannot vote for their own team in either award.
        $isOwnTeam = $user->teams()->where('teams.id', $team->id)->exists();
        if ($isOwnTeam) {
            $this->error = 'You cannot vote for your own team.';
            return;
        }

        $vote = VoteModel::firstOrNew([
            'user_id'    => $user->id,
            'award_key'  => $awardKey,
            'edition_id' => $edition->id,
        ]);

        $previousTeamId = $vote->exists ? $vote->team_id : null;
        $vote->team_id = $team->id;
        $vote->voter_role = $user->restrictedAwardVoterRole();
        $vote->save();

        $this->myVotes[$awardKey] = $team->id;
        $this->message = $previousTeamId
            ? 'Vote updated.'
            : 'Vote recorded.';

        AuditLog::record('restricted_award.voted', $vote, [
            'user_id'         => $user->id,
            'team_id'         => $team->id,
            'previous_team_id' => $previousTeamId,
            'award_key'       => $awardKey,
            'edition_id'      => $edition->id,
        ]);
    }

    public function isVotingOpen(int $editionId): bool
    {
        $phase = Phase::where('edition_id', $editionId)
            ->where('key', Phase::KEY_RESTRICTED_AWARD_VOTING)
            ->first();
        if (!$phase || $phase->state !== Phase::STATE_ACTIVE) {
            return false;
        }
        $now = now();
        if ($phase->starts_at && $now->lt($phase->starts_at)) return false;
        if ($phase->ends_at && $now->gt($phase->ends_at))   return false;
        return true;
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        $edition = Edition::active();
        $user = Auth::user();

        // Teams the user is in — they can't vote for their own.
        $ownTeamIds = $user
            ? $user->teams()->pluck('teams.id')->all()
            : [];

        // Restricted-award voting only applies to teams that made it to the
        // finals (Top 8). Voting opens AFTER the finals presentations, so by
        // then the finalist set is final. Showing non-finalists would let
        // users waste votes on teams that can't win the award.
        $teams = $edition
            ? Team::query()
                ->where('edition_id', $edition->id)
                ->where('status', 'active')
                ->where('is_finalist', true)
                ->whereNotIn('id', $ownTeamIds)
                ->with('theme')
                ->orderBy('name')
                ->get()
            : collect();

        $isOpen = $edition ? $this->isVotingOpen($edition->id) : false;

        $phase = $edition ? Phase::where('edition_id', $edition->id)
            ->where('key', Phase::KEY_RESTRICTED_AWARD_VOTING)
            ->first() : null;

        return view('livewire.restricted-award-vote', [
            'awards'     => VoteModel::AWARDS,
            'teams'      => $teams,
            'isOpen'     => $isOpen,
            'phase'      => $phase,
            'isEligible' => $user?->canVoteRestrictedAwards() ?? false,
            'voterRole'  => $user?->restrictedAwardVoterRole(),
        ]);
    }
}
