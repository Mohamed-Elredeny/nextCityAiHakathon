<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\JudgeAssignment;
use App\Models\MentorNote;
use App\Models\Score;
use App\Models\Team;
use App\Models\TeamApplication;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DynamicTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $pwd = Hash::make('password');

        // ─────────────────────────────────────────────────────────
        // 1) Cast of "applicant" users — different role profiles
        // ─────────────────────────────────────────────────────────
        $applicants = [
            // [email, name, primary_role, headline]
            ['ahmed.ux@demo.local',     'Ahmed UX Lead',    'designer',  'Senior product designer · 8y'],
            ['mariam.fe@demo.local',    'Mariam FE Dev',    'developer', 'React + Tailwind · ex-Vodafone'],
            ['hany.bd@demo.local',      'Hany Business',    'business',  'Growth + GTM · MBA AUC'],
            ['salma.ml@demo.local',     'Salma ML Eng',     'developer', 'Computer vision researcher'],
            ['kamal.pitch@demo.local',  'Kamal Pitcher',    'business',  'Sales + storytelling'],
            ['nadia.brand@demo.local',  'Nadia Brand',      'designer',  'Brand & motion designer'],
        ];

        foreach ($applicants as [$email, $name, $role, $headline]) {
            $u = User::firstOrCreate(['email' => $email], [
                'name' => $name,
                'password' => $pwd,
                'institution' => 'Demo University',
                'primary_role' => $role,
                'headline' => $headline,
            ]);
            if (!$u->hasRole('voter') && !$u->hasRole('team_member') && !$u->hasRole('team_leader')) {
                $u->assignRole('voter');
            }
        }

        $aqua    = Team::where('slug', 'aqua-guards')->first();
        $allmanda = Team::where('slug', 'allmanda')->first();
        $airi    = Team::where('slug', 'airiscan')->first();

        // ─────────────────────────────────────────────────────────
        // 2) Applications on Aqua Guards — multiple states
        //    (Aqua guards is the team that's actively recruiting)
        // ─────────────────────────────────────────────────────────
        if ($aqua) {
            // Wipe prior demo applications on aqua-guards from these emails so re-running is clean
            $applicantIds = User::whereIn('email', collect($applicants)->pluck(0))->pluck('id');
            TeamApplication::where('team_id', $aqua->id)
                ->whereIn('user_id', $applicantIds)
                ->delete();

            // PENDING — currently waiting for leader's decision
            $this->makeApplication($aqua, 'ahmed.ux@demo.local',
                "Hey Aqua team — I've been a senior product designer for 8 years, currently at a tourism-tech startup. I love the public-dashboard angle and could ship a clean visual system in 2-3 days. Free for the next week.",
                'Figma, motion, design systems',
                TeamApplication::STATUS_PENDING,
            );
            $this->makeApplication($aqua, 'nadia.brand@demo.local',
                "Brand designer with a motion background — I think Aqua Guards needs an identity that feels civic, not techy. Happy to draft a 3-mood logo board within 48h to prove fit.",
                'Figma, After Effects, brand systems',
                TeamApplication::STATUS_PENDING,
            );
            $this->makeApplication($aqua, 'hany.bd@demo.local',
                "Business lead with 5y of B2G partnerships. I have warm intros to the Egyptian Tourism Authority and the Marina municipal office. I can write the value proposition deck this week.",
                'B2G partnerships, GTM',
                TeamApplication::STATUS_PENDING,
            );

            // REJECTED — recently rejected, cooldown should kick in
            $this->makeApplication($aqua, 'kamal.pitch@demo.local',
                "I'm great at pitching and I want in.",
                'pitching',
                TeamApplication::STATUS_REJECTED,
                reviewedAt: now()->subHours(3),
                response: 'Thanks Kamal but we need someone with B2G partnerships specifically.',
            );

            // WITHDRAWN — applicant changed their mind
            $this->makeApplication($aqua, 'mariam.fe@demo.local',
                "FE dev here, available evenings. Built dashboards similar to what you need.",
                'React, Tailwind, D3',
                TeamApplication::STATUS_WITHDRAWN,
                reviewedAt: now()->subHours(8),
            );
        }

        // ─────────────────────────────────────────────────────────
        // 3) Application on Allmanda (also missing 1 role) — pending
        // ─────────────────────────────────────────────────────────
        if ($allmanda) {
            TeamApplication::where('team_id', $allmanda->id)
                ->whereIn('user_id', User::whereIn('email', ['salma.ml@demo.local'])->pluck('id'))
                ->delete();

            // Mark Allmanda as recruiting too so applicants can apply
            $allmanda->update([
                'is_recruiting' => true,
                'needed_roles' => ['developer'],
                'recruitment_message' => 'Looking for one more dev to harden the pharmacy integration.',
                'looking_for_skills' => 'PHP/Laravel, REST APIs',
            ]);

            $this->makeApplication($allmanda, 'salma.ml@demo.local',
                "Hi Mohamed — saw your team and the compounding-pharmacy pitch resonates. I have CV/ML background but I've also shipped Laravel APIs at scale. Down to help wherever needed.",
                'Laravel, PostgreSQL, ML',
                TeamApplication::STATUS_PENDING,
            );
        }

        // ─────────────────────────────────────────────────────────
        // 4) An APPROVED (historical) application on AIriScan
        //    — to populate "Recent decisions" panel for leaders
        // ─────────────────────────────────────────────────────────
        if ($airi) {
            $omar = User::where('email', 'omar.business@demo.local')->first();
            if ($omar) {
                TeamApplication::where(['team_id' => $airi->id, 'user_id' => $omar->id])->delete();
                TeamApplication::create([
                    'team_id' => $airi->id,
                    'user_id' => $omar->id,
                    'message' => 'Business lead with healthcare partnerships background — ready to drive clinic outreach.',
                    'skills' => 'B2B, healthcare, partnerships',
                    'status' => TeamApplication::STATUS_APPROVED,
                    'reviewed_by' => $airi->leader_id,
                    'reviewed_at' => now()->subDays(2),
                    'response_message' => 'Welcome aboard Omar!',
                ]);
            }
        }

        // ─────────────────────────────────────────────────────────
        // 5) A judge that's been admin-recused (to test exclusion)
        // ─────────────────────────────────────────────────────────
        $judge3 = User::firstOrCreate(['email' => 'judge3@demo.local'], [
            'name' => 'Judge Three (Recused)',
            'password' => $pwd,
            'institution' => 'AUC',
            'headline' => 'Recused: COI with one team',
        ]);
        if (!$judge3->hasRole('judge')) $judge3->assignRole('judge');

        if ($aqua) {
            $assn = JudgeAssignment::firstOrCreate(
                ['judge_id' => $judge3->id, 'team_id' => $aqua->id, 'round' => 'round1'],
                ['recused' => true, 'recused_reason' => 'Applicant team includes a former student.'],
            );
            // Even if a score row exists, it should be excluded by the recused filter.
            // Add one to make the test meaningful.
            Score::updateOrCreate(
                ['judge_id' => $judge3->id, 'team_id' => $aqua->id, 'round' => 'round1'],
                [
                    'innovation' => 5, 'technical' => 5, 'impact' => 5,
                    'ux' => 5, 'pitch' => 5, 'business' => 5,
                    'weighted_total' => 5.00,
                    'comment' => 'Pre-recusal score; should not affect leaderboard.',
                    'locked_at' => now()->subDay(),
                ],
            );
        }

        // ─────────────────────────────────────────────────────────
        // 6) Extra mentor notes — make it feel lived-in
        // ─────────────────────────────────────────────────────────
        $mentors = User::role('mentor')->get();
        $extraNotes = [
            'aqua-guards' => [
                'Hardware demo ran into LoRa range issues at 200m — recommend they test gateway placement before judging.',
                'They have a strong public-good narrative; suggest opening pitch with the 2024 Marina incident.',
            ],
            'airiscan' => [
                'Model bias concern — make sure they address dataset demographics in pitch.',
                'Encouraged them to prepare 30-second clinical-impact summary for Q&A.',
            ],
            'allmanda' => [
                'Need to articulate technical moat — ask them about regulatory barriers as defensibility.',
            ],
        ];
        foreach ($extraNotes as $slug => $notes) {
            $team = Team::where('slug', $slug)->first();
            if (!$team || $mentors->isEmpty()) continue;
            foreach ($notes as $note) {
                MentorNote::firstOrCreate(
                    ['mentor_id' => $mentors->first()->id, 'team_id' => $team->id, 'body' => $note],
                );
            }
        }

        // ─────────────────────────────────────────────────────────
        // 7) Audit log breadcrumbs — give the audit timeline content
        // ─────────────────────────────────────────────────────────
        AuditLog::firstOrCreate([
            'action' => 'team.member_left',
            'subject_id' => $aqua?->id,
        ], [
            'user_id' => $aqua?->leader_id,
            'subject_type' => Team::class,
            'payload' => ['user_id' => 9999, 'reason' => 'Left team during demo'],
            'created_at' => now()->subDays(1),
        ]);

        // ─────────────────────────────────────────────────────────
        // 8) "Empty team" demo — a leader who is alone (to show Disband button)
        //    Reuse Allmanda's leader spinning up a stub solo team would clobber data.
        //    Better: create a brand new "Solo Demo" team with a one-off leader.
        // ─────────────────────────────────────────────────────────
        $soloLeader = User::firstOrCreate(['email' => 'solo.leader@demo.local'], [
            'name' => 'Solo Leader',
            'password' => $pwd,
            'institution' => 'Demo University',
            'primary_role' => 'developer',
            'headline' => 'Solo demo · about to disband',
        ]);
        if (!$soloLeader->hasRole('team_leader')) $soloLeader->assignRole('team_leader');

        $soloTeam = Team::firstOrCreate(['slug' => 'solo-demo'], [
            'edition_id' => $aqua?->edition_id ?? 1,
            'theme_id' => $aqua?->theme_id,
            'leader_id' => $soloLeader->id,
            'name' => 'Solo Demo',
            'tagline' => 'A team of one — for testing the Disband flow.',
            'status' => 'active',
            'is_finalist' => false,
            'all_first_timers' => false,
            'is_recruiting' => false,
        ]);
        TeamMember::firstOrCreate(
            ['team_id' => $soloTeam->id, 'user_id' => $soloLeader->id],
            ['role_in_team' => 'Founder', 'role_category' => 'developer', 'is_leader' => true],
        );

        // ─────────────────────────────────────────────────────────
        // 9) Extra judges with varied score matrices per team
        //    Goal: different judges_count per team, plus a DRAFT-only
        //    score so the per-judge breakdown shows "saved but not locked".
        // ─────────────────────────────────────────────────────────
        $extraJudges = [
            ['judge4@demo.local', 'Judge Four (Lina)',  'Cairo University · CS dept'],
            ['judge5@demo.local', 'Judge Five (Karim)', 'GUC · Entrepreneurship'],
        ];
        $createdJudges = [];
        foreach ($extraJudges as [$email, $name, $institution]) {
            $u = User::firstOrCreate(['email' => $email], [
                'name' => $name,
                'password' => $pwd,
                'institution' => $institution,
                'headline' => 'Hackathon judge',
            ]);
            if (!$u->hasRole('judge')) $u->assignRole('judge');
            $createdJudges[$email] = $u;
        }
        $j4 = $createdJudges['judge4@demo.local'];
        $j5 = $createdJudges['judge5@demo.local'];

        // Assignments for j4 and j5 on every demo team
        foreach ([$aqua, $airi, $allmanda] as $team) {
            if (!$team) continue;
            JudgeAssignment::firstOrCreate(['judge_id' => $j4->id, 'team_id' => $team->id, 'round' => 'round1']);
            JudgeAssignment::firstOrCreate(['judge_id' => $j5->id, 'team_id' => $team->id, 'round' => 'round1']);
        }

        $writeScore = function (User $judge, Team $team, array $scores, string $comment, bool $locked) {
            $weighted = 0.0;
            foreach (Score::WEIGHTS as $crit => $w) {
                $weighted += $scores[$crit] * $w;
            }
            Score::updateOrCreate(
                ['judge_id' => $judge->id, 'team_id' => $team->id, 'round' => 'round1'],
                [
                    'innovation' => $scores['innovation'],
                    'technical'  => $scores['technical'],
                    'impact'     => $scores['impact'],
                    'ux'         => $scores['ux'],
                    'pitch'      => $scores['pitch'],
                    'business'   => $scores['business'],
                    'weighted_total' => round($weighted, 2),
                    'comment'    => $comment,
                    'locked_at'  => $locked ? now()->subHours(rand(1, 12)) : null,
                ],
            );
        };

        // Aqua Guards: 4 effective judges (1,2,4,5 locked + 3 recused excluded)
        if ($aqua) {
            $writeScore($j4, $aqua,
                ['innovation' => 9, 'technical' => 10, 'impact' => 10, 'ux' => 8, 'pitch' => 8, 'business' => 7],
                'Hardware execution is genuinely impressive. Visual design is the only weak point.',
                true);
            $writeScore($j5, $aqua,
                ['innovation' => 8, 'technical' => 9, 'impact' => 10, 'ux' => 7, 'pitch' => 9, 'business' => 9],
                'Strong public-good story. Tourism Authority angle is a real differentiator.',
                true);
        }

        // AIriScan: 3 locked + 1 draft (j5 still scoring)
        if ($airi) {
            $writeScore($j4, $airi,
                ['innovation' => 8, 'technical' => 9, 'impact' => 9, 'ux' => 8, 'pitch' => 7, 'business' => 6],
                'Clinical workflow is well thought through. Pricing model needs work.',
                true);
            $writeScore($j5, $airi,
                ['innovation' => 7, 'technical' => 8, 'impact' => 9, 'ux' => 7, 'pitch' => 6, 'business' => 6],
                '(draft — still reviewing the dataset bias claims)',
                false);
        }

        // Allmanda: 3 locked judges (1, 2, 5) — j4 hasn't started yet
        if ($allmanda) {
            $writeScore($j5, $allmanda,
                ['innovation' => 6, 'technical' => 7, 'impact' => 8, 'ux' => 8, 'pitch' => 9, 'business' => 10],
                'Best business pitch of the cohort. Tech moat is the open question.',
                true);
        }

        // ─────────────────────────────────────────────────────────
        // Summary
        // ─────────────────────────────────────────────────────────
        $this->command->info('Dynamic test data seeded:');
        $this->command->info('  Aqua Guards:   3 pending apps · 1 rejected (cooldown) · 1 withdrawn');
        $this->command->info('  Allmanda:      1 pending app  + recruiting flag flipped on');
        $this->command->info('  AIriScan:      1 historical approved app (audit trail)');
        $this->command->info('  Judge 3 (judge3@demo.local): admin-recused on Aqua Guards with pre-recusal score');
        $this->command->info('  Solo Demo (solo.leader@demo.local): single-member team — exposes Disband button');
        $this->command->info('  6 applicant accounts with diverse primary_roles (designer/developer/business)');
        $this->command->info('  +5 mentor notes across the 3 demo teams');
        $this->command->info('  Dynamic judges_count per team:');
        $this->command->info('    · Aqua Guards: 4 locked judges (j1+j2+j4+j5; j3 recused → excluded)');
        $this->command->info('    · AIriScan:    3 locked + 1 DRAFT (j5 still scoring)');
        $this->command->info('    · Allmanda:    3 locked judges (j1+j2+j5; j4 not started)');
    }

    private function makeApplication(
        Team $team,
        string $applicantEmail,
        string $message,
        string $skills,
        string $status,
        ?\Illuminate\Support\Carbon $reviewedAt = null,
        ?string $response = null,
    ): void {
        $user = User::where('email', $applicantEmail)->first();
        if (!$user) return;
        TeamApplication::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'message' => $message,
            'skills' => $skills,
            'status' => $status,
            'reviewed_by' => in_array($status, [TeamApplication::STATUS_REJECTED, TeamApplication::STATUS_APPROVED]) ? $team->leader_id : null,
            'reviewed_at' => $reviewedAt,
            'response_message' => $response,
        ]);
    }
}
