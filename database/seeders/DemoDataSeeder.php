<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Edition;
use App\Models\JudgeAssignment;
use App\Models\MentorAssignment;
use App\Models\MentorNote;
use App\Models\MentorRotationSlot;
use App\Models\PeoplesChoiceVote;
use App\Models\PitchSchedule;
use App\Models\Score;
use App\Models\SpecialAwardNomination;
use App\Models\Submission;
use App\Models\SubmissionValidation;
use App\Models\Team;
use App\Models\TeamComment;
use App\Models\TeamWorkspaceDraft;
use App\Models\Theme;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private const TEAM_NAME_POOL = [
        'NeuroFlow', 'CivicPulse', 'UtiliWatch', 'MoveMate', 'LoopBack',
        'Wanderly', 'MediMatch', 'SmartRoof', 'GreenLight', 'ResqGrid',
        'AssetSentinel', 'CityCircle', 'HeritageLens', 'PulsePoint', 'EcoFacade',
        'TransitIQ', 'PowerPath', 'AquaLogic', 'SolarSway', 'WasteWise',
        'TrafficZen', 'GridGuard', 'BuildSense', 'ClinicAI', 'TourTrail',
        'RescueWave', 'RecycleHub', 'VitalSign', 'RoofWise', 'PathFinder',
    ];

    private const TAGLINE_POOL = [
        'Adaptive traffic-signal control via deep RL',
        'Citizen-sourced disaster early-warning network',
        'Vibration anomaly detection on city pumps',
        'Bilingual MaaS planner for Egyptian cities',
        'AI-graded waste sorting for recycling centers',
        'Personalized walking-tour AI for Alamein',
        'Triage assistant for clinic over-crowding',
        'Occupancy + HVAC optimization with cheap sensors',
        'Pedestrian-priority intersection vision system',
        'Drone-routing for emergency responders',
        'Telemetry-based wear prediction for buses',
        'Marketplace for surplus building materials',
        'AR storytelling at heritage sites',
        'Mental-health chat assistant for students',
        'Solar-panel layout optimizer for irregular roofs',
    ];

    private const INSTITUTION_POOL = [
        'Cairo University', 'AUC', 'AAST', 'Alamein International University',
        'Mansoura University', 'Helwan University', 'Ain Shams', 'GUC',
        'Vodafone', 'Instabug', 'Swvl', 'IBM', 'Flat6Labs',
    ];

    private const ROLE_IN_TEAM_POOL = [
        'Engineer', 'Designer', 'Researcher', 'Product', 'Data Scientist', 'ML Engineer',
    ];

    public function run(): void
    {
        $faker = fake();

        $edition = Edition::active() ?? Edition::first();
        if (! $edition) {
            $this->command->error('Run EditionSeeder first.');
            return;
        }
        $themes = Theme::where('edition_id', $edition->id)->get();
        if ($themes->isEmpty()) {
            $this->command->error('No themes found for the active edition. Run EditionSeeder first.');
            return;
        }

        $teamCount = (int) env('SEED_TEAMS', 15);
        $judgeCount = (int) env('SEED_JUDGES', random_int(5, 7));
        $mentorCount = (int) env('SEED_MENTORS', random_int(3, 5));
        $voterCount = (int) env('SEED_VOTERS', random_int(60, 120));
        $finalistCount = (int) env('SEED_FINALISTS', min($teamCount, random_int(4, 6)));

        $this->command->warn("Wiping demo data for edition #{$edition->id}…");
        $this->wipe($edition);

        $this->command->info("Seeding {$judgeCount} judges, {$mentorCount} mentors…");
        $judges = $this->seedRolePool('judge', $judgeCount, 'judge');
        $mentors = $this->seedRolePool('mentor', $mentorCount, 'mentor');
        $admins = User::role('super_admin')->get()->all();

        $this->command->info("Seeding {$teamCount} teams + members + workspace drafts…");
        $teams = [];
        $usedNames = [];
        for ($i = 0; $i < $teamCount; $i++) {
            $name = $this->uniqueTeamName($usedNames);
            $tagline = $faker->randomElement(self::TAGLINE_POOL);
            $theme = $themes->random();
            $teams[] = $this->seedTeam($edition, $name, $theme, $i, $tagline, $faker);
        }

        $strength = $this->buildStrengthMap($teams);
        $criterionBias = $this->buildCriterionBiasMap($teams);

        $this->command->info('Assigning every judge to every team (Round 1)…');
        foreach ($teams as $team) {
            foreach ($judges as $judge) {
                JudgeAssignment::create([
                    'judge_id' => $judge->id,
                    'team_id' => $team->id,
                    'round' => JudgeAssignment::ROUND_ONE,
                    'recused' => $faker->boolean(3),
                    'recused_reason' => null,
                ]);
            }
        }

        $this->command->info('Locking Round 1 scores with realistic spread…');
        $this->seedScores(
            teams: $teams,
            judges: $judges,
            round: JudgeAssignment::ROUND_ONE,
            strength: $strength,
            criterionBias: $criterionBias,
            lockProbability: 92,
            faker: $faker,
        );

        $this->command->info("Promoting top {$finalistCount} to finalists…");
        $finalists = $this->pickFinalists($teams, $strength, $finalistCount);
        foreach ($finalists as $f) {
            $f->update(['is_finalist' => true]);
            $strength[$f->id] = min(10.0, $strength[$f->id] + 0.3);
        }

        $this->command->info('Seeding Finals round (assignments + partial scores)…');
        foreach ($judges as $judge) {
            foreach ($finalists as $finalist) {
                JudgeAssignment::create([
                    'judge_id' => $judge->id,
                    'team_id' => $finalist->id,
                    'round' => JudgeAssignment::ROUND_FINALS,
                    'recused' => false,
                ]);
            }
        }
        $this->seedScores(
            teams: $finalists,
            judges: $judges,
            round: JudgeAssignment::ROUND_FINALS,
            strength: $strength,
            criterionBias: $criterionBias,
            lockProbability: 55,
            faker: $faker,
        );

        $this->command->info('Generating Round 1 + Finals pitch schedules…');
        $this->seedPitchSchedules($teams, $finalists);

        $this->command->info('Seeding submissions with mixed statuses…');
        $this->seedSubmissions($teams, $faker);

        $this->command->info('Seeding mentor assignments + rotation slots + notes…');
        $this->seedMentorWork($teams, $mentors, $faker);

        $this->command->info('Seeding admin team comments…');
        $this->seedTeamComments($teams, $admins, $faker);

        $this->command->info('Seeding special award nominations…');
        $this->seedSpecialAwards($finalists, $judges, $faker);

        $this->command->info("Seeding {$voterCount} People's Choice votes (mix of users + guests)…");
        $this->seedVotes($teams, $strength, $voterCount, $faker);

        $this->command->info('Sprinkling audit log entries…');
        $this->seedAuditLog($teams, $judges, $admins, $faker);

        $this->printSummary($teams, $finalists, $judges, $mentors);
    }

    private function uniqueTeamName(array &$used): string
    {
        $pool = array_diff(self::TEAM_NAME_POOL, $used);
        if (! empty($pool)) {
            $name = collect($pool)->random();
            $used[] = $name;
            return $name;
        }
        do {
            $name = ucfirst(fake()->word()) . ucfirst(fake()->word());
        } while (in_array($name, $used, true));
        $used[] = $name;
        return $name;
    }

    private function seedRolePool(string $role, int $count, string $emailPrefix): array
    {
        $faker = fake();
        $created = [];
        $headlines = [
            'judge' => ['ML researcher', 'AI / data science lead', 'Engineering manager', 'Smart-city advisor', 'Investor & startup mentor'],
            'mentor' => ['Senior ML engineer', 'Product designer', 'DevOps & cloud', 'Mobile dev', 'Data scientist'],
        ];
        for ($i = 1; $i <= $count; $i++) {
            $name = $faker->name();
            $email = $emailPrefix . '.' . Str::slug($name, '.') . '@acie.local';
            $u = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'institution' => $faker->randomElement(self::INSTITUTION_POOL),
                    'phone' => '+201' . $faker->numerify('#########'),
                    'password' => Hash::make('password'),
                    'headline' => $faker->randomElement($headlines[$role] ?? ['Hackathon participant']),
                    'bio' => $faker->paragraph(random_int(2, 4)),
                    'social_links' => array_filter([
                        'linkedin' => $faker->boolean(70) ? 'https://linkedin.com/in/' . Str::slug($name) : null,
                        'github' => $faker->boolean(50) ? 'https://github.com/' . Str::slug($name) : null,
                    ]),
                ],
            );
            $u->syncRoles([$role]);
            $created[] = $u;
        }
        return $created;
    }

    private function seedTeam(Edition $edition, string $name, ?Theme $theme, int $idx, string $tagline, $faker): Team
    {
        $slug = Str::slug($name);
        $leader = User::create([
            'name' => $faker->name(),
            'email' => "{$slug}.leader@acie.local",
            'institution' => $faker->randomElement(self::INSTITUTION_POOL),
            'phone' => '+201' . $faker->numerify('#########'),
            'password' => Hash::make('password'),
        ]);
        $leader->assignRole('team_leader');

        $team = Team::create([
            'edition_id' => $edition->id,
            'theme_id' => $theme?->id,
            'leader_id' => $leader->id,
            'name' => $name,
            'slug' => $slug,
            'tagline' => $tagline,
            'status' => 'active',
            'all_first_timers' => $faker->boolean(25),
        ]);
        $team->members()->attach($leader->id, ['is_leader' => true, 'role_in_team' => 'Team Leader']);

        $memberCount = random_int(2, 4);
        for ($m = 1; $m <= $memberCount; $m++) {
            $u = User::create([
                'name' => $faker->name(),
                'email' => "{$slug}.m{$m}@acie.local",
                'institution' => $faker->randomElement(self::INSTITUTION_POOL),
                'phone' => '+201' . $faker->numerify('#########'),
                'password' => Hash::make('password'),
            ]);
            $u->assignRole('team_member');
            $team->members()->attach($u->id, [
                'is_leader' => false,
                'role_in_team' => $faker->randomElement(self::ROLE_IN_TEAM_POOL),
            ]);
        }

        foreach (TeamWorkspaceDraft::SECTIONS as $key => $label) {
            if ($faker->boolean(80)) {
                TeamWorkspaceDraft::create([
                    'team_id' => $team->id,
                    'section_key' => $key,
                    'body' => "## {$label}\n\n" . $faker->paragraphs(random_int(2, 4), true) . "\n\nTagline: {$tagline}",
                    'updated_by' => $leader->id,
                ]);
            }
        }
        return $team;
    }

    private function buildStrengthMap(array $teams): array
    {
        $map = [];
        foreach ($teams as $t) {
            $map[$t->id] = round(mt_rand(50, 92) / 10, 1);
        }
        return $map;
    }

    private function buildCriterionBiasMap(array $teams): array
    {
        $map = [];
        foreach ($teams as $t) {
            $map[$t->id] = [];
            foreach (array_keys(Score::WEIGHTS) as $criterion) {
                $map[$t->id][$criterion] = round(mt_rand(-12, 12) / 10, 1);
            }
        }
        return $map;
    }

    private function seedScores(array $teams, array $judges, string $round, array $strength, array $criterionBias, int $lockProbability, $faker): void
    {
        $weights = Score::WEIGHTS;
        $now = Carbon::now();

        foreach ($teams as $team) {
            $base = $strength[$team->id] ?? 7.0;
            foreach ($judges as $judge) {
                $row = [];
                $weighted = 0.0;
                foreach (array_keys($weights) as $c) {
                    $bias = $criterionBias[$team->id][$c] ?? 0;
                    $jitter = mt_rand(-15, 15) / 10;
                    $val = max(0, min(10, round($base + $bias + $jitter, 1)));
                    $row[$c] = $val;
                    $weighted += $val * $weights[$c];
                }
                Score::create([
                    'judge_id' => $judge->id,
                    'team_id' => $team->id,
                    'round' => $round,
                    'innovation' => $row['innovation'],
                    'technical' => $row['technical'],
                    'impact' => $row['impact'],
                    'ux' => $row['ux'],
                    'pitch' => $row['pitch'],
                    'business' => $row['business'],
                    'weighted_total' => round($weighted, 2),
                    'comment' => $faker->boolean(70) ? $this->randomComment($faker) : null,
                    'locked_at' => $faker->boolean($lockProbability) ? $now->copy()->subMinutes(mt_rand(1, 120)) : null,
                ]);
            }
        }
    }

    private function pickFinalists(array $teams, array $strength, int $count): array
    {
        return collect($teams)
            ->sortByDesc(fn ($t) => $strength[$t->id] ?? 0)
            ->take($count)
            ->values()
            ->all();
    }

    private function seedPitchSchedules(array $teams, array $finalists): void
    {
        $r1Start = Carbon::parse('2026-05-08 09:20:00', 'Africa/Cairo');
        foreach ($teams as $i => $team) {
            PitchSchedule::create([
                'team_id' => $team->id,
                'round' => 'round1',
                'slot_index' => $i + 1,
                'scheduled_start' => $r1Start->copy()->addMinutes($i * 8),
            ]);
        }
        $r1 = PitchSchedule::where('round', 'round1')->orderBy('slot_index')->get();
        $completedCount = (int) floor($r1->count() / 3);
        foreach ($r1->take($completedCount) as $slot) {
            $slot->update([
                'started_at' => $slot->scheduled_start,
                'ended_at' => $slot->scheduled_start->copy()->addMinutes(8),
            ]);
        }
        if ($r1->count() > $completedCount) {
            $r1[$completedCount]->update([
                'started_at' => Carbon::now()->subMinutes(2),
                'ended_at' => null,
            ]);
        }

        $finalsStart = Carbon::parse('2026-05-08 13:00:00', 'Africa/Cairo');
        foreach ($finalists as $i => $finalist) {
            PitchSchedule::create([
                'team_id' => $finalist->id,
                'round' => 'finals',
                'slot_index' => $i + 1,
                'scheduled_start' => $finalsStart->copy()->addMinutes($i * 13),
            ]);
        }
    }

    private function seedSubmissions(array $teams, $faker): void
    {
        $statusDistribution = [
            Submission::STATUS_VALIDATED => 35,
            Submission::STATUS_SUBMITTED => 25,
            Submission::STATUS_ACCEPTED => 15,
            Submission::STATUS_FLAGGED => 8,
            Submission::STATUS_REJECTED => 4,
            Submission::STATUS_DRAFT => 8,
        ];

        $teamsCount = count($teams);
        $skipCount = max(0, (int) round($teamsCount * 0.07));

        foreach ($teams as $i => $team) {
            if ($i < $skipCount) {
                continue;
            }

            $status = $this->weightedPick($statusDistribution);
            $isSubmittedFamily = ! in_array($status, [Submission::STATUS_DRAFT], true);

            $submission = Submission::create([
                'team_id' => $team->id,
                'round' => Submission::ROUND_ONE,
                'report_pdf_path' => $isSubmittedFamily ? 'submissions/reports/' . $team->slug . '.pdf' : null,
                'slides_url' => $isSubmittedFamily ? 'https://docs.google.com/presentation/d/' . Str::random(20) : null,
                'repo_url' => $isSubmittedFamily ? 'https://github.com/acie-hackathon/' . $team->slug : null,
                'video_url' => $isSubmittedFamily && $faker->boolean(85) ? 'https://youtu.be/' . Str::random(11) : null,
                'ai_disclosure_text' => $isSubmittedFamily ? $faker->randomElement([
                    'ChatGPT for ideation; GitHub Copilot for boilerplate.',
                    'Claude for code review; Cursor for refactors.',
                    'GPT-4o for architecture sketching; Copilot for tests.',
                    'Gemini for image classification baseline; Copilot for glue code.',
                ]) : null,
                'status' => $status,
                'submitted_at' => $isSubmittedFamily ? Carbon::parse('2026-05-07 15:' . str_pad((string) mt_rand(20, 44), 2, '0', STR_PAD_LEFT)) : null,
                'submitted_by' => $isSubmittedFamily ? $team->leader_id : null,
                'reject_reason' => $status === Submission::STATUS_REJECTED ? $faker->sentence(8) : null,
            ]);

            $checks = ['report_pdf', 'slides_url', 'repo_url', 'video_url', 'ai_disclosure'];
            foreach ($checks as $check) {
                if (! $isSubmittedFamily && $faker->boolean(60)) continue;
                SubmissionValidation::create([
                    'submission_id' => $submission->id,
                    'check_key' => $check,
                    'status' => match (true) {
                        $status === Submission::STATUS_FLAGGED && $faker->boolean(40) => 'fail',
                        $status === Submission::STATUS_DRAFT => 'pending',
                        default => 'pass',
                    },
                    'message' => $faker->boolean(20) ? $faker->sentence(6) : null,
                    'checked_at' => $isSubmittedFamily ? now()->subMinutes(mt_rand(5, 240)) : null,
                ]);
            }

            // Some finalists also have an updated finals submission
            if ($team->is_finalist && $isSubmittedFamily && $faker->boolean(70)) {
                Submission::create([
                    'team_id' => $team->id,
                    'round' => Submission::ROUND_FINALS,
                    'report_pdf_path' => $submission->report_pdf_path,
                    'slides_url' => 'https://docs.google.com/presentation/d/' . Str::random(20),
                    'repo_url' => $submission->repo_url,
                    'video_url' => $faker->boolean(80) ? 'https://youtu.be/' . Str::random(11) : $submission->video_url,
                    'ai_disclosure_text' => $submission->ai_disclosure_text,
                    'status' => Submission::STATUS_SUBMITTED,
                    'submitted_at' => Carbon::parse('2026-05-08 12:' . str_pad((string) mt_rand(15, 54), 2, '0', STR_PAD_LEFT)),
                    'submitted_by' => $team->leader_id,
                ]);
            }
        }
    }

    private function seedMentorWork(array $teams, array $mentors, $faker): void
    {
        if (empty($mentors)) return;

        foreach ($teams as $i => $team) {
            $mentor = $mentors[$i % count($mentors)];
            MentorAssignment::create(['mentor_id' => $mentor->id, 'team_id' => $team->id]);
        }

        $rotationStart = Carbon::parse('2026-05-07 13:00:00', 'Africa/Cairo');
        foreach ($teams as $i => $team) {
            $mentor = $mentors[$i % count($mentors)];
            $slotStart = $rotationStart->copy()->addMinutes($i * 6);
            MentorRotationSlot::create([
                'mentor_id' => $mentor->id,
                'team_id' => $team->id,
                'slot_start' => $slotStart,
                'slot_end' => $slotStart->copy()->addMinutes(5),
            ]);
        }

        foreach ($teams as $team) {
            $mentor = $mentors[array_rand($mentors)];
            $noteCount = random_int(0, 3);
            for ($n = 0; $n < $noteCount; $n++) {
                MentorNote::create([
                    'mentor_id' => $mentor->id,
                    'team_id' => $team->id,
                    'body' => $faker->paragraph(random_int(2, 5)),
                ]);
            }
        }
    }

    private function seedTeamComments(array $teams, array $admins, $faker): void
    {
        if (empty($admins)) return;
        foreach ($teams as $team) {
            if (! $faker->boolean(40)) continue;
            TeamComment::create([
                'team_id' => $team->id,
                'user_id' => $admins[array_rand($admins)]->id,
                'body' => $faker->randomElement([
                    'Reminder: please confirm your final pitch slot.',
                    'Submission looks good — verified all four artifacts.',
                    'Please contact the help desk for badge replacement.',
                    'Heads-up: theme lock-in window closing in 15 min.',
                ]),
            ]);
        }
    }

    private function seedSpecialAwards(array $finalists, array $judges, $faker): void
    {
        if (empty($finalists)) return;
        $awards = [
            SpecialAwardNomination::AWARD_BEST_AI,
            SpecialAwardNomination::AWARD_MOST_IMPACTFUL,
            SpecialAwardNomination::AWARD_BEST_NEWCOMER,
        ];
        foreach ($judges as $judge) {
            $picks = collect($awards)->shuffle()->take(random_int(1, 3));
            foreach ($picks as $awardKey) {
                $finalist = $finalists[array_rand($finalists)];
                SpecialAwardNomination::firstOrCreate(
                    [
                        'judge_id' => $judge->id,
                        'award_key' => $awardKey,
                        'round' => 'finals',
                    ],
                    [
                        'team_id' => $finalist->id,
                        'justification' => $faker->sentence(12),
                    ],
                );
            }
        }
    }

    private function seedVotes(array $teams, array $strength, int $voterCount, $faker): void
    {
        $weights = [];
        foreach ($teams as $t) {
            $weights[$t->id] = max(1, ($strength[$t->id] ?? 7.0) ** 2);
        }

        for ($v = 0; $v < $voterCount; $v++) {
            $teamId = $this->weightedPick($weights);
            $isGuest = $faker->boolean(70);
            $when = Carbon::now()->subMinutes(mt_rand(1, 480));

            if ($isGuest) {
                PeoplesChoiceVote::create([
                    'user_id' => null,
                    'voter_name' => $faker->name(),
                    'voter_email' => $faker->unique()->safeEmail(),
                    'voter_token' => Str::random(48),
                    'ip_address' => $faker->ipv4(),
                    'team_id' => $teamId,
                    'voted_at' => $when,
                ]);
            } else {
                $user = User::create([
                    'name' => $faker->name(),
                    'email' => 'voter.' . Str::random(8) . '@acie.local',
                    'password' => Hash::make('password'),
                ]);
                $user->assignRole('voter');
                PeoplesChoiceVote::create([
                    'user_id' => $user->id,
                    'team_id' => $teamId,
                    'voted_at' => $when,
                ]);
            }
        }
    }

    private function seedAuditLog(array $teams, array $judges, array $admins, $faker): void
    {
        $actions = [
            'team.created', 'team.member.added',
            'submission.submitted', 'submission.validated', 'submission.flagged',
            'score.locked', 'score.unlocked',
            'judge.assigned', 'judge.recused',
            'phase.activated', 'phase.closed',
            'vote.cast', 'finalist.promoted',
        ];

        $actors = array_merge($judges, $admins);
        $entryCount = random_int(40, 70);

        for ($i = 0; $i < $entryCount; $i++) {
            $team = $teams[array_rand($teams)];
            $actor = $actors[array_rand($actors)];
            AuditLog::create([
                'user_id' => $faker->boolean(85) ? $actor->id : null,
                'action' => $faker->randomElement($actions),
                'subject_type' => Team::class,
                'subject_id' => $team->id,
                'payload' => ['team' => $team->name, 'note' => $faker->sentence(6)],
                'created_at' => Carbon::now()->subMinutes(mt_rand(1, 720)),
            ]);
        }
    }

    private function weightedPick(array $weights): int|string
    {
        $total = array_sum($weights);
        $r = mt_rand(1, max(1, (int) ceil($total)));
        $cum = 0;
        foreach ($weights as $key => $w) {
            $cum += $w;
            if ($r <= $cum) return $key;
        }
        return array_key_last($weights);
    }

    private function randomComment($faker): string
    {
        $remarks = [
            'Strong technical depth and clear narrative.',
            'Promising idea — needs validation with real users.',
            'Excellent UX choices and thoughtful accessibility.',
            'Solid AI integration; deployment story could be stronger.',
            'Pitch was crisp and well-rehearsed.',
            'Business model is plausible; pricing assumptions are bold.',
            'Impact metrics are well quantified.',
            'Great use of open data; consider edge-case handling.',
            'Innovative angle on a familiar problem.',
            'Clean architecture, but the demo lagged on stage.',
            'Compelling story — recommend more attention to data privacy.',
            'Good team dynamics visible in the Q&A.',
        ];
        return $faker->randomElement($remarks);
    }

    private function printSummary(array $teams, array $finalists, array $judges, array $mentors): void
    {
        $sampleLeader = $teams[0] ?? null;
        $leaderEmail = $sampleLeader ? Str::slug($sampleLeader->name) . '.leader@acie.local' : 'n/a';
        $judgeEmail = $judges[0]->email ?? 'n/a';
        $mentorEmail = $mentors[0]->email ?? 'n/a';

        $this->command->info('');
        $this->command->info('=========================================');
        $this->command->info(' Demo data ready!');
        $this->command->info('=========================================');
        $this->command->info(' Counts:');
        $this->command->info('   Teams:     ' . count($teams) . ' (finalists: ' . count($finalists) . ')');
        $this->command->info('   Judges:    ' . count($judges));
        $this->command->info('   Mentors:   ' . count($mentors));
        $this->command->info('');
        $this->command->info(' Sample logins (password: "password"):');
        $this->command->info("   Admin   → admin@acie.local");
        $this->command->info("   Judge   → {$judgeEmail}");
        $this->command->info("   Mentor  → {$mentorEmail}");
        $this->command->info("   Leader  → {$leaderEmail}");
        $this->command->info('=========================================');
        $this->command->info(' Tip: override counts via env, e.g.');
        $this->command->info('   SEED_TEAMS=20 SEED_JUDGES=7 SEED_VOTERS=200 \\');
        $this->command->info('     php artisan db:seed --class=DemoDataSeeder');
        $this->command->info('=========================================');
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

        $demoUserIds = User::where('email', '!=', 'admin@acie.local')
            ->where(function ($q) {
                $q->where('email', 'like', '%@acie.local')
                  ->orWhere('email', 'like', '%@example.com');
            })
            ->pluck('id');

        DB::table('model_has_roles')->whereIn('model_id', $demoUserIds)->delete();
        User::whereIn('id', $demoUserIds)->delete();
    }
}
