<?php

namespace Database\Seeders;

use App\Models\AwardWinner;
use App\Models\Edition;
use App\Models\Team;
use Illuminate\Database\Seeder;

class AwardWinnersSeeder extends Seeder
{
    /**
     * Locks in the announced winners for the active edition. Re-running
     * this seeder is safe — it upserts on (edition_id, slot).
     *
     * Picks (announced 2026-05-08):
     *   🥇 1st               — Rasid (راصد)
     *   🥈 2nd               — CityPulse
     *   🥉 3rd               — PharmaChain AI
     *   🤖 AI Innovation     — Team Scrollwork
     *   💡 Impact Award      — Bioaxiom
     *   ❤️ People's Choice   — Synapse404
     */
    public function run(): void
    {
        $edition = Edition::active() ?? Edition::orderByDesc('id')->first();
        if (!$edition) {
            $this->command?->warn('No edition found — skipping AwardWinnersSeeder.');
            return;
        }

        // Match by slug first (stable across renames), then fall back to a
        // name-substring search so the seeder still works after small typos.
        $picks = [
            AwardWinner::SLOT_FIRST          => ['slug' => 'rasid-rasd',           'needle' => 'Rasid'],
            AwardWinner::SLOT_SECOND         => ['slug' => 'rvm',                  'needle' => 'CityPulse'],
            AwardWinner::SLOT_THIRD          => ['slug' => 'pharmachain-ai',       'needle' => 'PharmaChain'],
            AwardWinner::SLOT_BEST_AI        => ['slug' => 'team-scrollwork-FKC4', 'needle' => 'Scrollwork'],
            AwardWinner::SLOT_MOST_IMPACTFUL => ['slug' => 'bioaxiom-PM16',        'needle' => 'Bioaxiom'],
            AwardWinner::SLOT_PEOPLES_CHOICE => ['slug' => 'synapse404',           'needle' => 'Synapse404'],
        ];

        foreach ($picks as $slot => $find) {
            $team = Team::where('edition_id', $edition->id)
                ->where('slug', $find['slug'])
                ->first()
                ?? Team::where('edition_id', $edition->id)
                    ->where('name', 'like', '%' . $find['needle'] . '%')
                    ->first();

            if (!$team) {
                $this->command?->warn("Skipped {$slot}: team matching '{$find['needle']}' not found.");
                continue;
            }

            AwardWinner::updateOrCreate(
                ['edition_id' => $edition->id, 'slot' => $slot],
                ['team_id' => $team->id, 'display_order' => AwardWinner::SLOTS[$slot]['order']],
            );

            $this->command?->info("✓ {$slot} → {$team->name} (#{$team->id})");
        }
    }
}
