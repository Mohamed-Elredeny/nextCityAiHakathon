<?php

namespace Database\Seeders;

use App\Models\Edition;
use App\Models\Phase;
use App\Models\Theme;
use Illuminate\Database\Seeder;

class EditionSeeder extends Seeder
{
    public function run(): void
    {
        $edition = Edition::updateOrCreate(
            ['year' => 2026],
            [
                'name' => 'Next City AI Hackathon 2026',
                'starts_at' => '2026-05-07 08:30:00',
                'ends_at' => '2026-05-08 16:00:00',
                'is_active' => true,
            ]
        );

        $themes = [
            ['key' => 'mobility', 'name' => 'Mobility & Smart Traffic', 'description' => 'AI-driven traffic management, congestion prediction, adaptive signal control, autonomous vehicle integration, and accessible urban mobility solutions.'],
            ['key' => 'disaster_resilience', 'name' => 'Disaster Resilience', 'description' => 'Early warning systems, AI-powered emergency response coordination, real-time risk mapping, crisis simulation, and post-disaster recovery optimization.'],
            ['key' => 'predictive_maintenance', 'name' => 'Predictive Maintenance', 'description' => 'AI for infrastructure health monitoring, fault detection in utilities and transportation assets, lifecycle prediction, and smart maintenance scheduling.'],
            ['key' => 'smart_mobility_maas', 'name' => 'Smart Mobility & MaaS', 'description' => 'Mobility as a Service (MaaS) platforms, multimodal trip planning, dynamic routing, ride-sharing optimization, and micro-mobility integration.'],
            ['key' => 'circular_economy', 'name' => 'Circular Economy', 'description' => 'AI for waste stream optimization, resource recovery, reverse logistics, urban mining, sustainable supply chains, and consumption pattern analysis.'],
            ['key' => 'tourism', 'name' => 'Tourism & Hospitality Innovation', 'description' => 'Smart visitor management, personalized tourism experiences, AI-powered cultural heritage preservation, hospitality demand forecasting, and sustainable tourism flows.'],
            ['key' => 'healthcare', 'name' => 'Smart Healthcare & Health Infrastructure', 'description' => 'Telemedicine platforms, AI diagnostics, hospital resource optimization, public health surveillance, mental health tools, and health equity analytics.'],
            ['key' => 'smart_buildings', 'name' => 'Smart Buildings & Built Environment', 'description' => 'AI for building energy management, occupancy sensing, HVAC optimization, digital twins of urban infrastructure, and accessible smart space design.'],
        ];

        foreach ($themes as $i => $theme) {
            Theme::updateOrCreate(
                ['edition_id' => $edition->id, 'key' => $theme['key']],
                array_merge($theme, ['edition_id' => $edition->id, 'sort_order' => $i + 1])
            );
        }

        $phases = [
            ['key' => Phase::KEY_REGISTRATION, 'label' => 'Registration', 'starts_at' => '2026-04-26 00:00:00', 'ends_at' => '2026-05-07 08:30:00'],
            ['key' => Phase::KEY_THEME_LOCK_WINDOW, 'label' => 'Problem Theme Lock-In', 'starts_at' => '2026-05-07 09:45:00', 'ends_at' => '2026-05-07 10:00:00'],
            ['key' => Phase::KEY_SPRINT_1, 'label' => 'Development Sprint 1', 'starts_at' => '2026-05-07 10:00:00', 'ends_at' => '2026-05-07 12:00:00'],
            ['key' => Phase::KEY_MENTOR_SPEED, 'label' => 'Mentor Speed Rounds', 'starts_at' => '2026-05-07 13:00:00', 'ends_at' => '2026-05-07 13:30:00'],
            ['key' => Phase::KEY_SPRINT_2, 'label' => 'Development Sprint 2', 'starts_at' => '2026-05-07 13:30:00', 'ends_at' => '2026-05-07 15:30:00'],
            ['key' => Phase::KEY_SUBMISSION_WINDOW, 'label' => 'Submission Window', 'starts_at' => '2026-05-07 15:30:00', 'ends_at' => '2026-05-07 15:45:00'],
            ['key' => Phase::KEY_SUBMISSION_CLOSED, 'label' => 'Submissions Closed', 'starts_at' => '2026-05-07 15:45:00', 'ends_at' => '2026-05-08 09:20:00'],
            ['key' => Phase::KEY_ROUND1_PITCHING, 'label' => 'Round 1 Pitches', 'starts_at' => '2026-05-08 09:20:00', 'ends_at' => '2026-05-08 11:30:00'],
            ['key' => Phase::KEY_JUDGING_BREAK, 'label' => "Judges' Deliberation", 'starts_at' => '2026-05-08 11:30:00', 'ends_at' => '2026-05-08 12:00:00'],
            ['key' => Phase::KEY_FINALIST_ANNOUNCE, 'label' => 'Finalist Announcement', 'starts_at' => '2026-05-08 12:00:00', 'ends_at' => '2026-05-08 12:15:00'],
            ['key' => Phase::KEY_FINALS_SUBMISSION_WINDOW, 'label' => 'Finals Submission Window', 'starts_at' => '2026-05-08 12:15:00', 'ends_at' => '2026-05-08 12:55:00'],
            ['key' => Phase::KEY_FINALIST_PITCHING, 'label' => 'Finalist Pitches', 'starts_at' => '2026-05-08 13:00:00', 'ends_at' => '2026-05-08 14:30:00'],
            ['key' => Phase::KEY_AWARDS, 'label' => 'Awards Ceremony', 'starts_at' => '2026-05-08 15:00:00', 'ends_at' => '2026-05-08 16:00:00'],
        ];

        foreach ($phases as $i => $phase) {
            Phase::updateOrCreate(
                ['edition_id' => $edition->id, 'key' => $phase['key']],
                array_merge($phase, [
                    'edition_id' => $edition->id,
                    'sort_order' => $i + 1,
                    'state' => Phase::STATE_PENDING,
                    'auto_transition' => true,
                ])
            );
        }
    }
}
