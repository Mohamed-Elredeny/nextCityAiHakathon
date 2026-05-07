<?php

namespace Database\Seeders;

use App\Models\Edition;
use App\Models\JudgeAssignment;
use App\Models\MentorAssignment;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BoardAndPartnersSeeder extends Seeder
{
    /**
     * Seeds 6 board members + 2 industry partners with `judge` and `mentor`
     * roles. Emails are placeholders (admin can update from Filament).
     * Passwords are randomized and printed to the console once — re-running
     * the seeder will NOT regenerate passwords (it only updates profile data).
     */
    public function run(): void
    {
        $board = [
            ['name' => 'Dr. Hesham Gaber',          'email' => 'hesham.gaber@aiu.edu.eg',         'institution' => 'AIU'],
            ['name' => 'Dr. Maged Ghonima',         'email' => 'maged.ghonima@aiu.edu.eg',        'institution' => 'AIU'],
            ['name' => 'Dr. Fadel Daigham',         'email' => 'fadel.daigham@aiu.edu.eg',        'institution' => 'AIU'],
            ['name' => 'Dr. Mohamed Abdel Kareem',  'email' => 'mohamed.abdelkareem@aiu.edu.eg',  'institution' => 'AIU'],
            ['name' => 'Dr. Ahmed Elyazbi',         'email' => 'ahmed.elyazbi@aiu.edu.eg',        'institution' => 'AIU'],
            ['name' => 'Dr. Malak Sameh',           'email' => 'malak.sameh@aiu.edu.eg',          'institution' => 'AIU'],
        ];

        $partners = [
            [
                'name' => 'Eng. Mohamed Ramadan',
                'email' => 'mohamed.ramadan@hassanallam.com',
                'organization' => 'Hassan Allam Group',
                'org_url' => 'https://hassanallam.com',
                'headline' => 'Industry Partner — Hassan Allam Group',
            ],
            [
                'name' => 'Eng. Abdelrahman Ahmed',
                'email' => 'abdelrahman.ahmed@onyxsystems.com',
                'organization' => 'Onyx Systems',
                'org_url' => null,
                'headline' => 'Industry Partner — Onyx Systems',
            ],
        ];

        $credentials = [];

        foreach ($board as $person) {
            $credentials[] = $this->upsert($person + [
                'user_category' => User::CATEGORY_BOARD,
                'headline' => 'Board Member',
            ]);
        }

        foreach ($partners as $person) {
            $credentials[] = $this->upsert($person + [
                'user_category' => User::CATEGORY_PARTNER,
                'institution' => $person['organization'] ?? null,
            ]);
        }

        $this->command?->newLine();
        $this->command?->warn('Board & Partner accounts (share these credentials with the people only):');
        $this->command?->table(['Name', 'Email', 'Password', 'Category'], $credentials);
        $this->command?->newLine();

        // Make sure every board member and partner is assigned as judge AND
        // mentor to every team in the active edition. Idempotent — safe to
        // re-run.
        $assignmentSummary = $this->assignBoardAndPartnersToAllTeams();
        $this->command?->info('Assigned board/partners to all teams: ' . json_encode($assignmentSummary));
    }

    /**
     * Ensure every board member + partner has a JudgeAssignment (round1 +
     * finals) and a MentorAssignment for every team in the active edition.
     */
    public function assignBoardAndPartnersToAllTeams(): array
    {
        $edition = Edition::active();
        if (! $edition) {
            $this->command?->warn('No active edition — skipping auto-assignment.');
            return ['judges_added' => 0, 'mentors_added' => 0];
        }

        $teams = Team::where('edition_id', $edition->id)->where('status', 'active')->get();
        if ($teams->isEmpty()) {
            $this->command?->warn('No active teams in edition — skipping auto-assignment.');
            return ['judges_added' => 0, 'mentors_added' => 0];
        }

        $people = User::query()
            ->whereIn('user_category', [User::CATEGORY_BOARD, User::CATEGORY_PARTNER])
            ->get();

        $judgesAdded = 0;
        $mentorsAdded = 0;

        foreach ($people as $person) {
            foreach ($teams as $team) {
                // Round 1
                $r1 = JudgeAssignment::firstOrCreate(
                    ['judge_id' => $person->id, 'team_id' => $team->id, 'round' => JudgeAssignment::ROUND_ONE],
                    ['recused' => false],
                );
                if ($r1->wasRecentlyCreated) $judgesAdded++;

                // Finals (only meaningful if team becomes finalist later, but
                // pre-creating is harmless and avoids manual work).
                $rf = JudgeAssignment::firstOrCreate(
                    ['judge_id' => $person->id, 'team_id' => $team->id, 'round' => JudgeAssignment::ROUND_FINALS],
                    ['recused' => false],
                );
                if ($rf->wasRecentlyCreated) $judgesAdded++;

                // Mentor assignment (no round)
                $m = MentorAssignment::firstOrCreate(
                    ['mentor_id' => $person->id, 'team_id' => $team->id],
                );
                if ($m->wasRecentlyCreated) $mentorsAdded++;
            }
        }

        return [
            'judges_added' => $judgesAdded,
            'mentors_added' => $mentorsAdded,
            'people' => $people->count(),
            'teams' => $teams->count(),
        ];
    }

    private function upsert(array $data): array
    {
        $isNew = ! User::where('email', $data['email'])->exists();
        $plainPassword = $isNew ? Str::random(12) : '— unchanged —';

        $payload = collect($data)
            ->only(['name', 'institution', 'organization', 'headline', 'org_url', 'user_category'])
            ->filter(fn ($v) => $v !== null)
            ->all();

        $payload['registration_status'] = 'approved';
        $payload['approved_at'] = now();

        if ($isNew) {
            $payload['password'] = Hash::make($plainPassword);
        }

        $user = User::updateOrCreate(['email' => $data['email']], $payload);

        // Roles: judge + mentor cover the requested privileges (view +
        // communicate are open to any logged-in user via /community).
        $user->syncRoles(['judge', 'mentor']);

        return [
            $user->name,
            $user->email,
            $plainPassword,
            User::USER_CATEGORIES[$user->user_category] ?? '—',
        ];
    }
}
