<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CommunityNotification extends Notification
{
    use Queueable;

    public const TYPE_POST_MENTION = 'post_mention';
    public const TYPE_COMMENT_MENTION = 'comment_mention';
    public const TYPE_POST_TEAM_MENTION = 'post_team_mention';
    public const TYPE_COMMENT_TEAM_MENTION = 'comment_team_mention';
    public const TYPE_POST_COMMENT = 'post_comment';
    public const TYPE_POST_LIKE = 'post_like';
    public const TYPE_THREAD_REPLY = 'thread_reply';
    public const TYPE_APPLICATION_RECEIVED = 'application_received';
    public const TYPE_APPLICATION_APPROVED = 'application_approved';
    public const TYPE_APPLICATION_REJECTED = 'application_rejected';
    public const TYPE_APPLICATION_WITHDRAWN = 'application_withdrawn';

    public function __construct(public array $payload)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return $this->payload;
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload);
    }

    public function broadcastType(): string
    {
        return 'community.notification';
    }
}
