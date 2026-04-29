<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PhaseChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $editionId,
        public string $phaseKey,
        public string $from,
        public string $to,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("edition.{$this->editionId}")];
    }

    public function broadcastAs(): string
    {
        return 'phase.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'phase' => $this->phaseKey,
            'from' => $this->from,
            'to' => $this->to,
            'at' => now()->toIso8601String(),
        ];
    }
}
