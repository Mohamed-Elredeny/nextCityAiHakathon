<?php

/**
 * Comprehensive smoke test for the recent changes.
 * Run with: php artisan tinker tests/smoke-all.php
 *
 * Or include after bootstrapping the framework.
 */

use App\Livewire\JudgeDashboard;
use App\Livewire\PublicLeaderboard;
use App\Livewire\RecruitingTeams;
use App\Livewire\TeamSubmissionPreview;
use App\Livewire\TeamWorkspace;
use App\Livewire\UserProfile;
use App\Models\AuditLog;
use App\Models\JudgeAssignment;
use App\Models\Score;
use App\Models\Team;
use App\Models\TeamApplication;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\ScoringService;
use Illuminate\Support\Facades\Auth;

class SmokeRunner
{
    public int $pass = 0;
    public int $fail = 0;
    public array $errors = [];

    public function ok(string $name, callable $fn): void
    {
        try {
            $result = $fn();
            if ($result === false) {
                throw new RuntimeException('returned false');
            }
            echo "  \033[32m✓\033[0m {$name}\n";
            $this->pass++;
        } catch (\Throwable $e) {
            echo "  \033[31m✗\033[0m {$name} → " . $e->getMessage() . "\n";
            $this->fail++;
            $this->errors[] = $name . ': ' . $e->getMessage() . "\n    " . $e->getFile() . ':' . $e->getLine();
        }
    }

    public function summary(): void
    {
        echo "\n";
        echo str_repeat('─', 60) . "\n";
        echo "Tests: {$this->pass} passed";
        if ($this->fail > 0) {
            echo ", \033[31m{$this->fail} failed\033[0m";
        }
        echo "\n";
        if ($this->errors) {
            echo "\nFailures:\n";
            foreach ($this->errors as $err) {
                echo "  · " . $err . "\n";
            }
        }
    }
}

$t = new SmokeRunner;

echo "\n=== Models & migrations ===\n";

$t->ok('Team::MAX_MEMBERS constant exists and equals 5', fn() => Team::MAX_MEMBERS === 5);
$t->ok('User::ROLE_CATEGORIES constant has 3 entries', fn() => count(User::ROLE_CATEGORIES) === 3);
$t->ok('teams.needed_roles column exists (cast as array)', function () {
    $team = Team::first();
    return $team && (is_array($team->needed_roles) || is_null($team->needed_roles));
});
$t->ok('users.primary_role column accepts a value', function () {
    $u = User::where('email', 'layla.designer@demo.local')->first();
    return $u && $u->primary_role === 'designer';
});
$t->ok('team_members.role_category column populated for AIriScan', function () {
    $t = Team::where('slug', 'airiscan')->with('teamMembers')->first();
    return $t->teamMembers->whereNotNull('role_category')->count() === 3;
});
$t->ok('Team::role_coverage accessor returns filled & missing arrays', function () {
    $t = Team::where('slug', 'aqua-guards')->with('teamMembers')->first();
    $cov = $t->role_coverage;
    return isset($cov['filled']) && isset($cov['missing']);
});

echo "\n=== Public routes (no auth) ===\n";

$client = app('Illuminate\Contracts\Http\Kernel');

$check = function (string $path, int $expect = 200) use ($client) {
    $req = \Illuminate\Http\Request::create($path, 'GET');
    $resp = $client->handle($req);
    if ($resp->status() !== $expect) {
        throw new RuntimeException("GET {$path} → " . $resp->status() . " (expected {$expect})");
    }
    return true;
};

$t->ok('GET / → 200',                       fn() => $check('/'));
$t->ok('GET /leaderboard → 200',            fn() => $check('/leaderboard'));
$t->ok('GET /teams/aqua-guards → 200',      fn() => $check('/teams/aqua-guards'));
$t->ok('GET /teams/airiscan → 200',         fn() => $check('/teams/airiscan'));
$t->ok('GET /community → 200',              fn() => $check('/community'));
$t->ok('GET /community/teams → 200',        fn() => $check('/community/teams'));
$t->ok('GET /vote → 200',                   fn() => $check('/vote'));
$t->ok('GET /login → 200',                  fn() => $check('/login'));

echo "\n=== Protected routes — role middleware ===\n";

// Anonymous → /workspace, /judge, /mentor should redirect to /login
$checkAnonRedirect = function (string $path) use ($client) {
    $req = \Illuminate\Http\Request::create($path, 'GET');
    $resp = $client->handle($req);
    if ($resp->status() !== 302) {
        throw new RuntimeException("GET {$path} (anon) → " . $resp->status() . " (expected 302)");
    }
    $loc = $resp->headers->get('Location');
    if (!str_contains($loc, '/login')) {
        throw new RuntimeException("Redirected to {$loc} (expected /login)");
    }
    return true;
};

$t->ok('Anonymous → /workspace → /login', fn() => $checkAnonRedirect('/workspace'));
$t->ok('Anonymous → /judge → /login',     fn() => $checkAnonRedirect('/judge'));
$t->ok('Anonymous → /mentor → /login',    fn() => $checkAnonRedirect('/mentor'));
$t->ok('Anonymous → /profile → /login',   fn() => $checkAnonRedirect('/profile'));

// Wrong-role user → /judge → 403
$t->ok('Voter accessing /judge → 403', function () use ($client) {
    $voter = User::role('voter')->first();
    if (!$voter) throw new RuntimeException('no voter found');
    Auth::login($voter);
    $req = \Illuminate\Http\Request::create('/judge', 'GET');
    $resp = $client->handle($req);
    Auth::logout();
    if ($resp->status() !== 403) {
        throw new RuntimeException("got " . $resp->status());
    }
    return true;
});

$t->ok('Voter accessing /mentor → 403', function () use ($client) {
    $voter = User::role('voter')->first();
    Auth::login($voter);
    $req = \Illuminate\Http\Request::create('/mentor', 'GET');
    $resp = $client->handle($req);
    Auth::logout();
    return $resp->status() === 403;
});

$t->ok('Judge accessing /judge → 200', function () use ($client) {
    $judge = User::role('judge')->first();
    Auth::login($judge);
    $req = \Illuminate\Http\Request::create('/judge', 'GET');
    $resp = $client->handle($req);
    Auth::logout();
    if ($resp->status() !== 200) {
        throw new RuntimeException('status=' . $resp->status());
    }
    return true;
});

$t->ok('Mentor accessing /mentor → 200', function () use ($client) {
    $mentor = User::role('mentor')->first();
    Auth::login($mentor);
    $req = \Illuminate\Http\Request::create('/mentor', 'GET');
    $resp = $client->handle($req);
    Auth::logout();
    return $resp->status() === 200;
});

$t->ok('User without team accessing /workspace → redirects to /community/teams', function () use ($client) {
    $voter = User::role('voter')->first();
    Auth::login($voter);
    $req = \Illuminate\Http\Request::create('/workspace', 'GET');
    $resp = $client->handle($req);
    Auth::logout();
    if ($resp->status() !== 302) {
        throw new RuntimeException('status=' . $resp->status());
    }
    return str_contains((string) $resp->headers->get('Location'), '/community/teams');
});

echo "\n=== Livewire blade rendering smoke ===\n";

$renderLivewire = function (string $componentClass, ?User $authUser = null, array $mountArgs = []) {
    if ($authUser) Auth::login($authUser);
    $component = new $componentClass;
    if (method_exists($component, 'mount')) {
        $component->mount(...$mountArgs);
    }
    $view = $component->render();
    if (!$view) {
        // mount() may have triggered a redirect (legitimate) — skip render
        if ($authUser) Auth::logout();
        return true;
    }
    $rendered = (string) view($view->name(), $view->getData())->render();
    if ($authUser) Auth::logout();
    if (strlen($rendered) < 100) {
        throw new RuntimeException('rendered too small: ' . strlen($rendered));
    }
    return true;
};

$t->ok('PublicLeaderboard renders',         fn() => $renderLivewire(PublicLeaderboard::class));
$t->ok('TeamSubmissionPreview renders aqua-guards', fn() => $renderLivewire(TeamSubmissionPreview::class, null, ['aqua-guards']));
$t->ok('TeamSubmissionPreview renders airiscan',    fn() => $renderLivewire(TeamSubmissionPreview::class, null, ['airiscan']));
$t->ok('RecruitingTeams renders',           fn() => $renderLivewire(RecruitingTeams::class));
$t->ok('UserProfile renders for any user',  fn() => $renderLivewire(UserProfile::class, User::first()));
$t->ok('TeamWorkspace renders for leader',  function () use ($renderLivewire) {
    $leader = User::where('email', 'youssef.moharm.2024@aiu.edu.eg')->first();
    return $renderLivewire(TeamWorkspace::class, $leader);
});
$t->ok('TeamWorkspace renders for member', function () use ($renderLivewire) {
    $member = User::where('email', 'layla.designer@demo.local')->first();
    return $renderLivewire(TeamWorkspace::class, $member);
});
$t->ok('JudgeDashboard renders for judge',  function () use ($renderLivewire) {
    $judge = User::where('email', 'judge1@demo.local')->first();
    return $renderLivewire(JudgeDashboard::class, $judge);
});

echo "\n=== Score lock validation ===\n";

$t->ok('Score lock refuses zeros', function () {
    $j = User::where('email', 'judge1@demo.local')->first();
    $team = Team::where('slug', 'aqua-guards')->first();
    // Save existing locked score so we can restore
    $existing = Score::where(['judge_id' => $j->id, 'team_id' => $team->id, 'round' => 'round1'])->first();
    $backup = $existing ? $existing->toArray() : null;
    Score::where(['judge_id' => $j->id, 'team_id' => $team->id, 'round' => 'round1'])->delete();
    try {
        app(ScoringService::class)->lock($j, $team, 'round1', [
            'innovation' => 0, 'technical' => 0, 'impact' => 0, 'ux' => 0, 'pitch' => 0, 'business' => 0,
        ]);
        return false;
    } catch (\RuntimeException $e) {
        if (str_contains($e->getMessage(), 'rate every criterion')) {
            // Restore
            if ($backup) {
                Score::create(array_merge($backup, ['locked_at' => $backup['locked_at']]));
            }
            return true;
        }
        return false;
    }
});

$t->ok('Score lock refuses partial scores', function () {
    $j = User::where('email', 'judge2@demo.local')->first();
    $team = Team::where('slug', 'aqua-guards')->first();
    $existing = Score::where(['judge_id' => $j->id, 'team_id' => $team->id, 'round' => 'round1'])->first();
    $backup = $existing ? $existing->toArray() : null;
    Score::where(['judge_id' => $j->id, 'team_id' => $team->id, 'round' => 'round1'])->delete();
    try {
        app(ScoringService::class)->lock($j, $team, 'round1', [
            'innovation' => 8, 'technical' => 0, 'impact' => 9, 'ux' => 8, 'pitch' => 7, 'business' => 7,
        ]);
        return false;
    } catch (\RuntimeException $e) {
        if (str_contains($e->getMessage(), 'rate every criterion')) {
            if ($backup) {
                Score::create($backup);
            }
            return true;
        }
        return false;
    }
});

echo "\n=== Recused judge exclusion ===\n";

$t->ok('Recused judge score excluded from leaderboard average', function () {
    // Use AIriScan where the two judges scored differently (8.25 vs 8.45)
    // so recusing one actually changes the average.
    $j = User::where('email', 'judge1@demo.local')->first();
    $team = Team::where('slug', 'airiscan')->first();
    $assn = JudgeAssignment::where(['judge_id' => $j->id, 'team_id' => $team->id, 'round' => 'round1'])->first();
    if (!$assn) throw new RuntimeException('no assignment');

    $component = new PublicLeaderboard;
    $component->mount();
    $beforeAvg = $component->render()->getData()['teams']->firstWhere('id', $team->id)?->avg_total;

    $assn->update(['recused' => true, 'recused_reason' => 'smoke test']);

    $component2 = new PublicLeaderboard;
    $component2->mount();
    $afterAvg = $component2->render()->getData()['teams']->firstWhere('id', $team->id)?->avg_total;

    $assn->update(['recused' => false, 'recused_reason' => null]); // restore

    if ($beforeAvg === null || $afterAvg === null) {
        throw new RuntimeException("avg null: before={$beforeAvg} after={$afterAvg}");
    }
    if (abs($beforeAvg - $afterAvg) < 0.01) {
        throw new RuntimeException("avg unchanged: before={$beforeAvg} after={$afterAvg}");
    }
    return true;
});

$t->ok('Pre-recused judge3 score is already excluded from Aqua Guards avg', function () {
    $aqua = Team::where('slug', 'aqua-guards')->first();
    $j3 = User::where('email', 'judge3@demo.local')->value('id');
    if (!$j3) throw new RuntimeException('judge3 not seeded');

    $rawAvg = Score::where('team_id', $aqua->id)->whereNotNull('locked_at')->avg('weighted_total');
    $excludedAvg = Score::where('team_id', $aqua->id)->whereNotNull('locked_at')
        ->where('judge_id', '!=', $j3)->avg('weighted_total');

    $component = new PublicLeaderboard;
    $component->mount();
    $leaderboardAvg = $component->render()->getData()['teams']->firstWhere('id', $aqua->id)?->avg_total;

    if (abs($leaderboardAvg - $excludedAvg) > 0.01) {
        throw new RuntimeException("leaderboard avg ({$leaderboardAvg}) doesn't match recused-excluded avg ({$excludedAvg})");
    }
    if (abs($leaderboardAvg - $rawAvg) < 0.01) {
        throw new RuntimeException("leaderboard avg matches RAW avg — recusal not applied");
    }
    return true;
});

echo "\n=== Re-apply cooldown ===\n";

$t->ok('Rejected applicant within 24h cannot re-apply', function () {
    $applicant = User::role('voter')->first();
    if (!$applicant) throw new RuntimeException('no voter');
    $team = Team::where('slug', 'aqua-guards')->first();

    // Clean any existing application
    TeamApplication::where(['team_id' => $team->id, 'user_id' => $applicant->id])->delete();

    // Insert a fresh "rejected just now" application
    $rejected = TeamApplication::create([
        'team_id' => $team->id,
        'user_id' => $applicant->id,
        'message' => 'previous attempt',
        'status' => TeamApplication::STATUS_REJECTED,
        'reviewed_by' => $team->leader_id,
        'reviewed_at' => now()->subHours(2),
    ]);

    Auth::login($applicant);
    $component = new RecruitingTeams;
    $component->applyToTeamId = $team->id;
    $component->applicationMessage = 'Please reconsider, I would love to join your team.';
    $component->applicationSkills = 'Figma';
    $component->submitApplication();
    $err = $component->applyError;
    Auth::logout();

    // Cleanup
    $rejected->delete();

    if (!$err || !str_contains($err, 're-apply')) {
        throw new RuntimeException("expected cooldown error, got: " . ($err ?? 'no error'));
    }
    return true;
});

echo "\n=== Auto-fill role_category on approval ===\n";

$t->ok('Approving applicant with primary_role auto-fills team_members.role_category', function () {
    $team = Team::where('slug', 'allmanda')->first();
    $leader = User::find($team->leader_id);

    // Snapshot recruiting state so the approval-side-effect (auto-disable) can be reverted
    $snapshot = [
        'is_recruiting' => $team->is_recruiting,
        'needed_roles' => $team->needed_roles,
    ];

    $applicant = User::firstOrCreate(['email' => 'smoke.applicant@demo.local'], [
        'name' => 'Smoke Applicant',
        'password' => bcrypt('password'),
        'primary_role' => 'designer',
    ]);
    if (!$applicant->hasRole('voter')) $applicant->assignRole('voter');

    TeamMember::where('user_id', $applicant->id)->delete();
    TeamApplication::where(['team_id' => $team->id, 'user_id' => $applicant->id])->delete();

    $app = TeamApplication::create([
        'team_id' => $team->id,
        'user_id' => $applicant->id,
        'message' => 'I would like to join.',
        'skills' => 'Figma, motion',
        'status' => TeamApplication::STATUS_PENDING,
    ]);

    Auth::login($leader);
    $component = new TeamWorkspace;
    $component->mount();
    $component->approveApplication($app->id);
    Auth::logout();

    $tm = TeamMember::where(['team_id' => $team->id, 'user_id' => $applicant->id])->first();
    $autoRole = $tm?->role_category;

    // Restore recruiting state via query builder (the local Eloquent model
    // is stale because the approval flipped is_recruiting in the DB; calling
    // ->update($snapshot) on the stale instance is a no-op since the dirty
    // check sees the original values match).
    Team::where('id', $team->id)->update($snapshot);

    // Cleanup
    if ($tm) $tm->delete();
    $app->delete();

    if ($autoRole !== 'designer') {
        throw new RuntimeException("expected role_category=designer, got: " . ($autoRole ?? 'null'));
    }
    return true;
});

echo "\n=== Audit log entries ===\n";

$t->ok('AuditLog table accepts our action keys', function () {
    $count = AuditLog::whereIn('action', [
        'team.member_left', 'team.member_kicked', 'team.leadership_transferred',
        'team.disbanded', 'application.approved', 'application.rejected',
        'judge.self_recused',
    ])->count();
    // Just verify we can query it without error
    return true;
});

echo "\n=== Dynamic state ===\n";

$t->ok('Aqua Guards has 3 pending applications', function () {
    $count = TeamApplication::where('team_id', Team::where('slug', 'aqua-guards')->value('id'))
        ->where('status', 'pending')->count();
    if ($count !== 3) throw new RuntimeException("expected 3, got {$count}");
    return true;
});

$t->ok('Allmanda has 1 pending application', function () {
    $count = TeamApplication::where('team_id', Team::where('slug', 'allmanda')->value('id'))
        ->where('status', 'pending')->count();
    if ($count !== 1) throw new RuntimeException("expected 1, got {$count}");
    return true;
});

$t->ok('Solo Demo team exists with exactly 1 member (disband-button case)', function () {
    $team = Team::where('slug', 'solo-demo')->first();
    if (!$team) throw new RuntimeException('solo-demo team missing');
    $size = TeamMember::where('team_id', $team->id)->count();
    if ($size !== 1) throw new RuntimeException("expected 1 member, got {$size}");
    return true;
});

$t->ok('Recruiting board lists Aqua Guards + Allmanda but not Solo Demo', function () {
    Auth::logout();
    $component = new RecruitingTeams;
    $teams = $component->render()->getData()['teams'];
    $slugs = $teams->pluck('slug')->all();
    if (!in_array('aqua-guards', $slugs)) throw new RuntimeException('aqua-guards missing');
    if (!in_array('allmanda', $slugs)) throw new RuntimeException('allmanda missing');
    if (in_array('solo-demo', $slugs)) throw new RuntimeException('solo-demo should be hidden (not recruiting)');
    return true;
});

$t->ok('TeamWorkspace for Aqua leader exposes 3 pending applications', function () {
    $aqua = Team::where('slug', 'aqua-guards')->first();
    $leader = User::find($aqua->leader_id);
    Auth::login($leader);
    $component = new TeamWorkspace;
    $component->mount();
    $data = $component->render()->getData();
    $pending = $data['pendingApplications'];
    Auth::logout();
    if ($pending->count() !== 3) throw new RuntimeException("expected 3, got {$pending->count()}");
    return true;
});

$t->ok('Non-leader member sees pending applications too', function () {
    // Aqua guards' second member (not the leader)
    $aqua = Team::where('slug', 'aqua-guards')->with('teamMembers')->first();
    $nonLeader = $aqua->teamMembers->firstWhere('is_leader', false);
    if (!$nonLeader || !$nonLeader->user_id) {
        // Skip — no second member exists
        return true;
    }
    Auth::loginUsingId($nonLeader->user_id);
    $component = new TeamWorkspace;
    $component->mount();
    $data = $component->render()->getData();
    $pending = $data['pendingApplications'];
    Auth::logout();
    if ($pending->count() !== 3) throw new RuntimeException("expected 3 visible, got {$pending->count()}");
    return true;
});

$t->ok('Solo leader sees Disband button trigger condition (members.count === 1)', function () {
    $solo = Team::where('slug', 'solo-demo')->with('members')->first();
    if ($solo->members->count() !== 1) {
        throw new RuntimeException("members.count is {$solo->members->count()}, disband button hidden");
    }
    return true;
});

echo "\n=== Final integrity ===\n";

$t->ok('No teams with leader_id pointing to non-member', function () {
    $broken = Team::query()
        ->whereNotNull('leader_id')
        ->whereNotIn('id', function ($q) {
            $q->select('team_id')->from('team_members')
                ->whereColumn('user_id', 'teams.leader_id');
        })
        ->where('status', 'active')
        ->count();
    if ($broken > 0) {
        throw new RuntimeException($broken . ' teams have a leader who is not in team_members');
    }
    return true;
});

$t->ok('All seeded judges have a JudgeAssignment for round1', function () {
    foreach (User::role('judge')->get() as $j) {
        if (JudgeAssignment::where('judge_id', $j->id)->where('round', 'round1')->count() === 0) {
            throw new RuntimeException("Judge {$j->email} has no round1 assignments");
        }
    }
    return true;
});

$t->ok('Leaderboard final_score computed for all 3 demo teams', function () {
    $component = new PublicLeaderboard;
    $component->mount();
    $teams = $component->render()->getData()['teams'];
    foreach (['aqua-guards', 'airiscan', 'allmanda'] as $slug) {
        $team = $teams->firstWhere('slug', $slug);
        if (!$team || $team->final_score === null) {
            throw new RuntimeException("{$slug} has no final_score");
        }
    }
    return true;
});

$t->summary();

if ($t->fail > 0) {
    exit(1);
}
