<?php

namespace Database\Seeders;

use App\Models\MentorNote;
use App\Models\PeoplesChoiceVote;
use App\Models\Score;
use App\Models\Submission;
use App\Models\Team;
use App\Models\TeamWorkspaceDraft;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $teams = Team::whereIn('slug', ['airiscan', 'allmanda', 'aqua-guards'])
            ->with('teamMembers')
            ->get()
            ->keyBy('slug');

        if ($teams->isEmpty()) {
            $this->command->error('No demo teams found. Run TeamsImportSeeder first.');
            return;
        }

        $judges = User::role('judge')->orderBy('id')->get();
        $mentors = User::role('mentor')->orderBy('id')->get();

        $narratives = [
            'airiscan' => [
                'problem' => "Millions of patients in Egypt and across the MENA region delay eye-disease diagnosis because of limited access to specialists. By the time conditions like glaucoma or diabetic retinopathy are detected, irreversible damage is often done. Existing tele-medicine apps focus on appointments, not on early detection.",
                'solution' => "AIriScan uses a smartphone camera + a fine-tuned vision model to triage 5 common eye conditions in under 30 seconds. If a risk is detected, the user is connected to the nearest accredited ophthalmologist and a partner pharmacy for follow-up. The model is tuned on the locally-collected MENA-Eye dataset to reduce demographic bias.",
                'architecture' => "Mobile (Flutter) → on-device pre-processing → REST API (FastAPI on AWS Lambda) → fine-tuned EfficientNet-B3 → triage decision → PostgreSQL audit log + Twilio SMS to nearest doctor.",
                'impact' => "Potential to screen 100k+ patients in year 1 across AIU partner clinics. Each early detection saves an estimated 4,500 EGP in late-stage treatment. Designed to plug into the Ministry of Health's existing referral network.",
                'roles' => "Youssef — ML lead (model training, dataset curation). Designer — Flutter UI + clinic-side dashboard. Business lead — clinic partnerships and pricing model.",
                'references' => "MENA-Eye dataset (2024). EfficientNet (Tan & Le, 2019). EgyptianMOH telemedicine guidelines.",
                'ai_disclosure' => "GitHub Copilot was used for boilerplate scaffolding only. Model architecture, training loop, and clinical logic were authored by the team.",
            ],
            'allmanda' => [
                'problem' => "Cosmetic prescription fulfilment in Egyptian pharmacies is fragmented: dermatologists prescribe, but patients struggle to find the exact compounded formula. 38% of compounded prescriptions go unfilled.",
                'solution' => "Allmanda is a B2B SaaS that connects dermatologists to a network of compounding pharmacies, with a patient-facing app for tracking and refills. Pharmacists get a structured prescription, patients get reminders + delivery, doctors get adherence analytics.",
                'architecture' => "Laravel + Filament admin, Livewire dashboard for clinics, Twilio for SMS reminders, Stripe-EG for payments, PostgreSQL + Redis cache.",
                'impact' => "Pilot ready with 12 clinics in Alexandria. Targeting 80%+ fulfilment rate by month 6. Clear path to franchise model across MENA.",
                'roles' => "Mohamed — Business lead, clinic partnerships. Designer — patient app UI. Pharmacy domain expert.",
                'references' => "EFDA compounding regulations 2025. Patient adherence study, Lancet Dermatology 2023.",
                'ai_disclosure' => "ChatGPT used for marketing copy only. All code written by the team.",
            ],
            'aqua-guards' => [
                'problem' => "Beach and coastal water quality on the North Coast is monitored manually — typically a single weekly sample. Bacterial outbreaks (e.g., 2024 Marina incident) go undetected for days, exposing thousands of swimmers.",
                'solution' => "Aqua Guards deploys low-cost IoT buoys (~\$120/unit) that stream pH, turbidity and bacterial proxies every 5 minutes to a public dashboard. A predictive model forecasts safe-to-swim windows 24 hours out. Lifeguards get a tablet app with red/yellow/green status.",
                'architecture' => "ESP32-based buoy (LoRa to shore gateway) → MQTT → InfluxDB → forecast model (Prophet + custom rules) → Vue dashboard. Public API for tourism boards.",
                'impact' => "Could prevent the next public-health incident on Egypt's North Coast. Tourism Authority is interested in branding the certification. Open data benefits researchers worldwide.",
                'roles' => "Hardware lead (buoy electronics & deployment). Backend developer (data pipeline, forecasting). NEEDED: designer for the public dashboard. NEEDED: business lead for tourism-board partnerships.",
                'references' => "EU Bathing Water Directive 2006/7/EC. Egypt Tourism Authority 2024 report.",
                'ai_disclosure' => "Claude used for forecasting model design discussion. Final model implementation by the team.",
            ],
        ];

        foreach ($teams as $slug => $team) {
            $n = $narratives[$slug] ?? null;
            if (!$n) continue;

            // 1) Workspace drafts (what teams "wrote")
            foreach ($n as $key => $body) {
                TeamWorkspaceDraft::updateOrCreate(
                    ['team_id' => $team->id, 'section_key' => $key],
                    ['body' => $body, 'updated_by' => $team->leader_id],
                );
            }

            // 2) Round-1 submission with all links
            Submission::updateOrCreate(
                ['team_id' => $team->id, 'round' => Submission::ROUND_ONE],
                [
                    'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'slides_url' => 'https://docs.google.com/presentation/d/demo-' . $slug,
                    'repo_url' => 'https://github.com/demo/' . $slug,
                    'ai_disclosure_text' => $n['ai_disclosure'],
                    'status' => Submission::STATUS_SUBMITTED,
                    'submitted_at' => now()->subDays(2),
                    'submitted_by' => $team->leader_id,
                ],
            );
        }

        // 3) Role categories on team members so role coverage shows real states
        $coverage = [
            'airiscan'    => ['developer', 'designer', 'business'],   // complete
            'allmanda'    => ['business', 'designer'],                 // missing developer
            'aqua-guards' => ['developer'],                            // missing designer + business
        ];
        foreach ($coverage as $slug => $roleList) {
            $team = $teams->get($slug);
            if (!$team) continue;
            $members = $team->teamMembers;
            foreach ($members as $i => $m) {
                $m->update(['role_category' => $roleList[$i] ?? null]);
            }
        }

        // Make Aqua guards actively recruiting with declared needs
        $aqua = $teams->get('aqua-guards');
        if ($aqua) {
            $aqua->update([
                'is_recruiting' => true,
                'recruitment_message' => "Pre-finals push! We have working hardware and a backend pipeline. We need a designer for the public dashboard and a business lead to talk to the Tourism Authority. Last 4 days before submissions close.",
                'looking_for_skills' => 'Figma, Vue/Tailwind, B2G partnerships',
                'needed_roles' => ['designer', 'business'],
            ]);
        }

        // 4) Locked judge scores → leaderboard rankings
        $scoreMatrix = [
            'aqua-guards' => [
                ['innovation' => 10, 'technical' => 9, 'impact' => 10, 'ux' => 9, 'pitch' => 9, 'business' => 8, 'comment' => 'Real-world impact, technically ambitious. Design needs polish but the core is excellent.'],
                ['innovation' => 9, 'technical' => 10, 'impact' => 9, 'ux' => 10, 'pitch' => 9, 'business' => 9, 'comment' => 'Best-in-show hardware demo. Strong forecast model.'],
            ],
            'airiscan' => [
                ['innovation' => 8, 'technical' => 9, 'impact' => 9, 'ux' => 8, 'pitch' => 7, 'business' => 7, 'comment' => 'Solid model, clear clinical impact. Pitch could be sharper.'],
                ['innovation' => 9, 'technical' => 8, 'impact' => 10, 'ux' => 7, 'pitch' => 8, 'business' => 8, 'comment' => 'Excellent dataset choice and bias awareness.'],
            ],
            'allmanda' => [
                ['innovation' => 7, 'technical' => 7, 'impact' => 8, 'ux' => 9, 'pitch' => 8, 'business' => 9, 'comment' => 'Strongest business case of the round. Tech is ordinary but execution is sharp.'],
                ['innovation' => 8, 'technical' => 6, 'impact' => 7, 'ux' => 9, 'pitch' => 7, 'business' => 10, 'comment' => 'Clear ROI path. Need to deepen the technical moat.'],
            ],
        ];

        foreach ($scoreMatrix as $slug => $rows) {
            $team = $teams->get($slug);
            if (!$team) continue;
            foreach ($rows as $i => $row) {
                $judge = $judges[$i] ?? null;
                if (!$judge) continue;
                $weighted = 0.0;
                foreach (Score::WEIGHTS as $crit => $w) {
                    $weighted += $row[$crit] * $w;
                }
                Score::updateOrCreate(
                    ['judge_id' => $judge->id, 'team_id' => $team->id, 'round' => 'round1'],
                    [
                        'innovation' => $row['innovation'],
                        'technical' => $row['technical'],
                        'impact' => $row['impact'],
                        'ux' => $row['ux'],
                        'pitch' => $row['pitch'],
                        'business' => $row['business'],
                        'weighted_total' => round($weighted, 2),
                        'comment' => $row['comment'],
                        'locked_at' => now()->subHours(6),
                    ],
                );
            }
        }

        // 5) Mentor notes — one private note per mentor per team
        $mentorNotes = [
            'aqua-guards' => 'Schedule a UX session before finals — visual design is the weakest link. Hardware story is strong.',
            'airiscan'    => 'Encourage them to rehearse the pitch. Strong technical content but underselling impact.',
            'allmanda'    => 'Push them on the technical differentiator. Business is strong, judges may question moat.',
        ];
        foreach ($teams as $slug => $team) {
            $body = $mentorNotes[$slug] ?? null;
            if (!$body) continue;
            foreach ($mentors as $mentor) {
                MentorNote::firstOrCreate(
                    ['mentor_id' => $mentor->id, 'team_id' => $team->id, 'body' => $body],
                );
            }
        }

        // 6) People's-choice votes spread unevenly so popularity differs
        $voteCounts = ['aqua-guards' => 47, 'airiscan' => 31, 'allmanda' => 18];
        foreach ($voteCounts as $slug => $count) {
            $team = $teams->get($slug);
            if (!$team) continue;
            $existing = PeoplesChoiceVote::where('team_id', $team->id)->count();
            for ($i = $existing; $i < $count; $i++) {
                PeoplesChoiceVote::create([
                    'team_id' => $team->id,
                    'voter_name' => 'Guest ' . substr(md5($slug . $i), 0, 6),
                    'voter_email' => 'guest' . $i . '_' . $slug . '@demo.local',
                    'voter_token' => bin2hex(random_bytes(8)),
                    'ip_address' => '127.0.0.' . (($i % 254) + 1),
                    'voted_at' => now()->subMinutes(rand(1, 1440)),
                ]);
            }
        }

        $this->command->info('Demo content seeded:');
        $this->command->info('  · 3 teams (airiscan, allmanda, aqua-guards) with full workspace narratives');
        $this->command->info('  · Round-1 submissions with video/slides/repo links + AI disclosure');
        $this->command->info('  · Locked judge scores from 2 judges → leaderboard ranks');
        $this->command->info('  · Role coverage: airiscan complete · allmanda missing developer · aqua-guards missing designer+business');
        $this->command->info('  · Aqua guards is recruiting (designer + business needed)');
        $this->command->info('  · Mentor notes from both mentors');
        $this->command->info('  · 47 / 31 / 18 people-choice votes');
    }
}
