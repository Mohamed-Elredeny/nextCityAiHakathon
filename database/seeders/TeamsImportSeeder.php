<?php

namespace Database\Seeders;

use App\Models\Edition;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeamsImportSeeder extends Seeder
{
    private const FALLBACK_PASSWORD = 'ChangeMe!2026';
    private const DATA_PATH = 'database/data/teams.json';

    public function run(): void
    {
        $edition = Edition::active() ?? Edition::first();
        if (! $edition) {
            $this->command->error('Run EditionSeeder first.');
            return;
        }

        $path = base_path(self::DATA_PATH);
        if (! is_file($path)) {
            $this->command->error("Missing {$path}");
            return;
        }
        $payload = json_decode(file_get_contents($path), true);
        $teams = $payload['teams'] ?? [];
        $unaffiliated = $payload['unaffiliated'] ?? [];
        if (empty($teams) && empty($unaffiliated)) {
            $this->command->warn('No teams or registrants in data file.');
            return;
        }

        $this->command->warn("Wiping prior team data for edition #{$edition->id}…");
        $this->wipe($edition);

        $created = 0;
        $usedSlugs = [];
        foreach ($teams as $row) {
            $name = trim((string) ($row['team_name'] ?? ''));
            if ($name === '') continue;

            $registrants = $row['registrants'] ?? [];
            if (empty($registrants)) continue;

            usort($registrants, function ($a, $b) {
                return strcmp((string) ($a['submitted_at'] ?? ''), (string) ($b['submitted_at'] ?? ''));
            });

            $slug = $this->uniqueSlug($name, $usedSlugs);
            $usedSlugs[] = $slug;

            $leaderRow = $registrants[0];
            $leader = $this->upsertUser($leaderRow, isLeader: true, teamSlug: $slug);

            $tagline = $this->buildTagline($leaderRow);

            $team = Team::create([
                'edition_id' => $edition->id,
                'theme_id' => null,
                'leader_id' => $leader->id,
                'name' => $name,
                'slug' => $slug,
                'tagline' => $tagline,
                'status' => 'active',
                'all_first_timers' => false,
            ]);

            $team->members()->attach($leader->id, [
                'is_leader' => true,
                'role_in_team' => $leaderRow['role_in_team'] ?? 'Team Leader',
            ]);

            foreach (array_slice($registrants, 1) as $memberRow) {
                $member = $this->upsertUser($memberRow, isLeader: false, teamSlug: $slug);
                if ($member->id === $leader->id) continue;
                if ($team->members()->where('user_id', $member->id)->exists()) continue;
                $team->members()->attach($member->id, [
                    'is_leader' => false,
                    'role_in_team' => $memberRow['role_in_team'] ?? null,
                ]);
            }

            $created++;
        }

        $individualCount = 0;
        foreach ($unaffiliated as $row) {
            $email = strtolower(trim((string) ($row['email'] ?? '')));
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) continue;
            $this->upsertUser($row, isLeader: false, teamSlug: 'unaffiliated');
            $individualCount++;
        }

        $totalUsers = User::role(['team_leader', 'team_member'])->count();
        $this->command->info("Imported {$created} teams.");
        $this->command->info("Imported {$individualCount} unaffiliated registrants (individuals + skipped team applicants).");
        $this->command->info("Total participant accounts: {$totalUsers}");
        $this->command->info('Login: email = student email, password = National ID (fallback: ' . self::FALLBACK_PASSWORD . ')');
    }

    private function upsertUser(array $row, bool $isLeader, string $teamSlug): User
    {
        $email = strtolower(trim((string) ($row['email'] ?? '')));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = $teamSlug . '.member.' . Str::random(6) . '@acie.local';
        }

        $name = $this->cleanName((string) ($row['full_name'] ?? $email));
        $institution = $this->trimOrNull($row['institution'] ?? null);
        $phone = $this->trimOrNull($row['phone'] ?? null);
        $headline = $this->buildHeadline($row);
        $bio = $this->buildBio($row);

        $nationalId = preg_replace('/\D+/', '', (string) ($row['national_id'] ?? ''));
        $passwordPlain = $nationalId !== '' ? $nationalId : self::FALLBACK_PASSWORD;

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($passwordPlain),
                'institution' => $institution,
                'phone' => $phone,
                'headline' => $headline,
                'bio' => $bio,
            ],
        );

        $role = $isLeader ? 'team_leader' : 'team_member';
        if (! $user->hasRole($role)) {
            $user->syncRoles([$role]);
        }

        return $user;
    }

    private function buildHeadline(array $row): ?string
    {
        $parts = array_filter([
            $row['role_in_team'] ?? null,
            $row['skill_level'] ?? null,
            $row['field'] ?? null,
        ]);
        return $parts ? implode(' · ', $parts) : null;
    }

    private function buildBio(array $row): ?string
    {
        $lines = [];
        if (! empty($row['track']))  $lines[] = 'Track: ' . trim($row['track']);
        if (! empty($row['skills'])) $lines[] = 'Skills: ' . trim($row['skills'], "; \t\n");
        if (! empty($row['problem'])) $lines[] = "\nIdea: " . trim($row['problem']);
        return $lines ? implode("\n", $lines) : null;
    }

    private function buildTagline(array $leaderRow): ?string
    {
        $idea = trim((string) ($leaderRow['problem'] ?? ''));
        if ($idea === '' || stripos($idea, 'no problem') === 0) return null;
        return Str::limit($idea, 180, '…');
    }

    private function cleanName(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));
        return $name !== '' ? Str::title($name) : 'Unnamed';
    }

    private function trimOrNull($value): ?string
    {
        if ($value === null) return null;
        $v = trim((string) $value);
        return $v === '' ? null : $v;
    }

    private function uniqueSlug(string $name, array $used): string
    {
        $base = Str::slug($name);
        if ($base === '') $base = 'team';
        $slug = $base;
        $i = 1;
        while (in_array($slug, $used, true) || Team::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }

    private function wipe(Edition $edition): void
    {
        DB::table('audit_log')->delete();
        DB::table('peoples_choice_votes')->delete();
        DB::table('mentor_notes')->delete();
        DB::table('mentor_rotation_slots')->delete();
        DB::table('mentor_assignments')->delete();
        DB::table('special_award_nominations')->delete();
        DB::table('scores')->delete();
        DB::table('judge_assignments')->delete();
        DB::table('submission_validations')->delete();
        DB::table('submissions')->delete();
        DB::table('team_workspace_drafts')->delete();
        DB::table('team_comments')->delete();
        DB::table('pitch_schedule')->delete();
        DB::table('team_members')->delete();
        DB::table('teams')->where('edition_id', $edition->id)->delete();

        $protectedRoles = ['super_admin', 'operator', 'judge', 'mentor'];
        $purgeUserIds = User::where('email', '!=', 'admin@acie.local')
            ->whereDoesntHave('roles', function ($q) use ($protectedRoles) {
                $q->whereIn('name', $protectedRoles);
            })
            ->pluck('id');

        DB::table('model_has_roles')->whereIn('model_id', $purgeUserIds)->delete();
        User::whereIn('id', $purgeUserIds)->delete();
    }
}
