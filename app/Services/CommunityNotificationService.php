<?php

namespace App\Services;

use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\Team;
use App\Models\TeamApplication;
use App\Models\TeamMember;
use App\Models\User;
use App\Notifications\CommunityNotification;
use Illuminate\Support\Facades\Notification;

class CommunityNotificationService
{
    public function notifyPostMentions(CommunityPost $post, array $userIds, array $teamIds): void
    {
        $userIds = $this->cleanIds($userIds, [$post->user_id]);
        if ($userIds) {
            $users = User::whereIn('id', $userIds)->get();
            Notification::send($users, new CommunityNotification([
                'type' => CommunityNotification::TYPE_POST_MENTION,
                'post_id' => $post->id,
                'post_title' => $post->title,
                'actor_id' => $post->user_id,
                'actor_name' => $post->user?->name,
            ]));
        }

        if ($teamIds) {
            $teams = Team::whereIn('id', $teamIds)->with('members')->get();
            foreach ($teams as $team) {
                $memberIds = $team->members->pluck('id')->reject(fn ($id) => $id === $post->user_id)->all();
                if (!$memberIds) continue;
                $members = User::whereIn('id', $memberIds)->get();
                Notification::send($members, new CommunityNotification([
                    'type' => CommunityNotification::TYPE_POST_TEAM_MENTION,
                    'post_id' => $post->id,
                    'post_title' => $post->title,
                    'team_id' => $team->id,
                    'team_name' => $team->name,
                    'actor_id' => $post->user_id,
                    'actor_name' => $post->user?->name,
                ]));
            }
        }
    }

    public function notifyCommentMentions(CommunityComment $comment, array $userIds, array $teamIds): void
    {
        $post = $comment->post;
        $exclude = [$comment->user_id];

        $userIds = $this->cleanIds($userIds, $exclude);
        if ($userIds) {
            $users = User::whereIn('id', $userIds)->get();
            Notification::send($users, new CommunityNotification([
                'type' => CommunityNotification::TYPE_COMMENT_MENTION,
                'post_id' => $post?->id,
                'post_title' => $post?->title,
                'comment_id' => $comment->id,
                'actor_id' => $comment->user_id,
                'actor_name' => $comment->user?->name,
            ]));
        }

        if ($teamIds) {
            $teams = Team::whereIn('id', $teamIds)->with('members')->get();
            foreach ($teams as $team) {
                $memberIds = $team->members->pluck('id')->reject(fn ($id) => $id === $comment->user_id)->all();
                if (!$memberIds) continue;
                $members = User::whereIn('id', $memberIds)->get();
                Notification::send($members, new CommunityNotification([
                    'type' => CommunityNotification::TYPE_COMMENT_TEAM_MENTION,
                    'post_id' => $post?->id,
                    'post_title' => $post?->title,
                    'comment_id' => $comment->id,
                    'team_id' => $team->id,
                    'team_name' => $team->name,
                    'actor_id' => $comment->user_id,
                    'actor_name' => $comment->user?->name,
                ]));
            }
        }
    }

    public function notifyPostAuthorOnComment(CommunityComment $comment): void
    {
        $post = $comment->post;
        if (!$post || $post->user_id === $comment->user_id) {
            return;
        }
        $author = User::find($post->user_id);
        if (!$author) return;

        $author->notify(new CommunityNotification([
            'type' => CommunityNotification::TYPE_POST_COMMENT,
            'post_id' => $post->id,
            'post_title' => $post->title,
            'comment_id' => $comment->id,
            'actor_id' => $comment->user_id,
            'actor_name' => $comment->user?->name,
        ]));
    }

    /**
     * Notify users who previously commented on the post (thread participants),
     * excluding the post author (already notified separately) and the actor.
     */
    public function notifyThreadParticipants(CommunityComment $comment, array $alreadyNotifiedUserIds = []): void
    {
        $post = $comment->post;
        if (!$post) return;

        $exclude = array_merge($alreadyNotifiedUserIds, [
            $comment->user_id,
            $post->user_id, // already gets TYPE_POST_COMMENT
        ]);

        $participantIds = CommunityComment::query()
            ->where('community_post_id', $post->id)
            ->where('id', '!=', $comment->id)
            ->whereNotIn('user_id', $exclude)
            ->pluck('user_id')
            ->unique()
            ->values()
            ->all();

        if (!$participantIds) return;

        $users = User::whereIn('id', $participantIds)->get();
        Notification::send($users, new CommunityNotification([
            'type' => CommunityNotification::TYPE_THREAD_REPLY,
            'post_id' => $post->id,
            'post_title' => $post->title,
            'comment_id' => $comment->id,
            'actor_id' => $comment->user_id,
            'actor_name' => $comment->user?->name,
        ]));
    }

    public function notifyPostLiked(CommunityPost $post, User $liker): void
    {
        if ($post->user_id === $liker->id) return;
        $author = User::find($post->user_id);
        if (!$author) return;

        $author->notify(new CommunityNotification([
            'type' => CommunityNotification::TYPE_POST_LIKE,
            'post_id' => $post->id,
            'post_title' => $post->title,
            'actor_id' => $liker->id,
            'actor_name' => $liker->name,
        ]));
    }

    public function notifyApplicationReceived(TeamApplication $application): void
    {
        $team = $application->team;
        if (!$team || !$team->leader_id) return;

        $leader = User::find($team->leader_id);
        if (!$leader) return;

        $leader->notify(new CommunityNotification([
            'type' => CommunityNotification::TYPE_APPLICATION_RECEIVED,
            'application_id' => $application->id,
            'team_id' => $team->id,
            'team_name' => $team->name,
            'actor_id' => $application->user_id,
            'actor_name' => $application->user?->name,
        ]));
    }

    public function notifyApplicationWithdrawn(TeamApplication $application): void
    {
        $team = $application->team;
        if (!$team || !$team->leader_id) return;

        $leader = User::find($team->leader_id);
        if (!$leader) return;

        $leader->notify(new CommunityNotification([
            'type' => CommunityNotification::TYPE_APPLICATION_WITHDRAWN,
            'application_id' => $application->id,
            'team_id' => $team->id,
            'team_name' => $team->name,
            'actor_id' => $application->user_id,
            'actor_name' => $application->user?->name,
        ]));
    }

    public function notifyApplicationDecision(TeamApplication $application): void
    {
        $applicant = User::find($application->user_id);
        if (!$applicant) return;

        $type = $application->status === TeamApplication::STATUS_APPROVED
            ? CommunityNotification::TYPE_APPLICATION_APPROVED
            : CommunityNotification::TYPE_APPLICATION_REJECTED;

        $applicant->notify(new CommunityNotification([
            'type' => $type,
            'application_id' => $application->id,
            'team_id' => $application->team_id,
            'team_name' => $application->team?->name,
            'response_message' => $application->response_message,
            'actor_id' => $application->reviewed_by,
            'actor_name' => $application->reviewer?->name,
        ]));
    }

    private function cleanIds(array $ids, array $exclude): array
    {
        return collect($ids)->filter()->unique()->reject(fn ($id) => in_array($id, $exclude, true))->values()->all();
    }
}
