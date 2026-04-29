<?php

namespace App\Events;

use App\Models\Score;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreLocked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $editionId,
        public int $teamId,
        public string $round,
        public float $weightedTotal,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("edition.{$this->editionId}"),
            new Channel("leaderboard.{$this->editionId}.{$this->round}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'score.locked';
    }

    public function broadcastWith(): array
    {
        return [
            'team_id' => $this->teamId,
            'round' => $this->round,
            'weighted_total' => $this->weightedTotal,
            'at' => now()->toIso8601String(),
        ];
    }
}
