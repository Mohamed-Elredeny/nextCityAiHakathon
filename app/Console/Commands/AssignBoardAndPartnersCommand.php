<?php

namespace App\Console\Commands;

use Database\Seeders\BoardAndPartnersSeeder;
use Illuminate\Console\Command;

class AssignBoardAndPartnersCommand extends Command
{
    protected $signature = 'hackathon:assign-board-partners';

    protected $description = 'Make sure every board member and partner is assigned as judge + mentor to every active team in the active edition. Idempotent.';

    public function handle(): int
    {
        $seeder = new BoardAndPartnersSeeder();
        $seeder->setCommand($this);
        $summary = $seeder->assignBoardAndPartnersToAllTeams();

        $this->table(
            array_keys($summary),
            [array_values($summary)],
        );
        $this->info('Done.');
        return self::SUCCESS;
    }
}
