<?php

namespace App\Console\Commands;

use App\Services\PhaseEngine;
use Illuminate\Console\Command;

class PhaseTickCommand extends Command
{
    protected $signature = 'hackathon:phase-tick';

    protected $description = 'Advance hackathon phases according to their start/end times.';

    public function handle(PhaseEngine $engine): int
    {
        $result = $engine->tick();
        $this->info("Phase tick: {$result['changed']} transition(s).");
        if (!empty($result['transitions'])) {
            foreach ($result['transitions'] as [$key, $from, $to]) {
                $this->line("  - {$key}: {$from} → {$to}");
            }
        }
        return self::SUCCESS;
    }
}
