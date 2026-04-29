<?php

namespace App\Events;

use App\Models\Submission;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubmissionReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Submission $submission) {}

    public function broadcastOn(): array
    {
        return [new Channel('admin.submissions')];
    }

    public function broadcastAs(): string
    {
        return 'submission.received';
    }

    public function broadcastWith(): array
    {
        return [
            'team_id' => $this->submission->team_id,
            'team_name' => $this->submission->team->name ?? null,
            'submitted_at' => $this->submission->submitted_at?->toIso8601String(),
        ];
    }
}
