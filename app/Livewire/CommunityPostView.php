<?php

namespace App\Livewire;

use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\Team;
use App\Models\User;
use App\Services\CommunityNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CommunityPostView extends Component
{
    public int $postId;
    public string $newComment = '';
    public ?string $errorMessage = null;

    public string $mentionSearch = '';
    public array $mentionedUsers = [];
    public array $mentionedTeams = [];

    public bool $editingPost = false;
    public string $postBodyEdit = '';
    public string $postTitleEdit = '';

    public ?int $editingCommentId = null;
    public string $commentBodyEdit = '';

    public function mount(int $post): void
    {
        $exists = CommunityPost::whereKey($post)->exists();
        if (!$exists) {
            abort(404);
        }
        $this->postId = $post;
    }

    public function addUserMention(int $userId): void
    {
        if (collect($this->mentionedUsers)->contains('id', $userId)) return;
        $user = User::select('id', 'name', 'institution')->find($userId);
        if (!$user || $user->id === Auth::id()) return;
        $this->mentionedUsers[] = ['id' => $user->id, 'name' => $user->name, 'subtitle' => $user->institution];
        $this->mentionSearch = '';
    }

    public function addTeamMention(int $teamId): void
    {
        if (collect($this->mentionedTeams)->contains('id', $teamId)) return;
        $team = Team::select('id', 'name', 'tagline')->find($teamId);
        if (!$team) return;
        $this->mentionedTeams[] = ['id' => $team->id, 'name' => $team->name, 'subtitle' => $team->tagline];
        $this->mentionSearch = '';
    }

    public function removeUserMention(int $userId): void
    {
        $this->mentionedUsers = array_values(array_filter($this->mentionedUsers, fn ($m) => $m['id'] !== $userId));
    }

    public function removeTeamMention(int $teamId): void
    {
        $this->mentionedTeams = array_values(array_filter($this->mentionedTeams, fn ($m) => $m['id'] !== $teamId));
    }

    public function postComment(): void
    {
        $this->errorMessage = null;

        if (!Auth::check()) {
            $this->errorMessage = 'Please sign in to comment.';
            return;
        }

        $key = 'community-comment:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            $this->errorMessage = "Slow down — try again in {$seconds}s.";
            return;
        }
        RateLimiter::hit($key, 60);

        $body = trim($this->newComment);
        if ($body === '') return;
        if (mb_strlen($body) > 2000) {
            $this->errorMessage = 'Comment too long (max 2000 characters).';
            return;
        }

        $post = CommunityPost::find($this->postId);
        if (!$post) return;

        $userIds = collect($this->mentionedUsers)->pluck('id')->all();
        $teamIds = collect($this->mentionedTeams)->pluck('id')->all();

        $comment = CommunityComment::create([
            'community_post_id' => $post->id,
            'user_id' => Auth::id(),
            'body' => $body,
            'mentioned_user_ids' => $userIds ?: null,
            'mentioned_team_ids' => $teamIds ?: null,
        ]);
        $post->increment('comments_count');

        $service = app(CommunityNotificationService::class);
        $comment->load(['user', 'post']);
        $service->notifyPostAuthorOnComment($comment);
        if ($userIds || $teamIds) {
            $service->notifyCommentMentions($comment, $userIds, $teamIds);
        }
        // Notify earlier commenters who haven't already been notified via mention
        $service->notifyThreadParticipants($comment, $userIds);

        $this->newComment = '';
        $this->mentionedUsers = [];
        $this->mentionedTeams = [];
        $this->mentionSearch = '';
    }

    public function deleteComment(int $commentId): void
    {
        $user = Auth::user();
        if (!$user) return;
        $comment = CommunityComment::find($commentId);
        if (!$comment) return;
        if ($comment->user_id !== $user->id && !$user->hasRole('super_admin')) {
            return;
        }
        $post = $comment->post;
        $comment->delete();
        if ($post) {
            $post->decrement('comments_count');
        }
    }

    public function toggleLike(): void
    {
        if (!Auth::check()) {
            $this->errorMessage = 'Please sign in to like this post.';
            return;
        }
        $post = CommunityPost::find($this->postId);
        if (!$post) return;

        $existing = $post->likes()->where('user_id', Auth::id())->first();
        if ($existing) {
            $existing->delete();
            $post->decrement('likes_count');
        } else {
            $post->likes()->create(['user_id' => Auth::id()]);
            $post->increment('likes_count');
            app(CommunityNotificationService::class)->notifyPostLiked($post, Auth::user());
        }
    }

    public function startEditPost(): void
    {
        $post = CommunityPost::find($this->postId);
        if (!$post || $post->user_id !== Auth::id()) return;
        $this->editingPost = true;
        $this->postTitleEdit = $post->title;
        $this->postBodyEdit = $post->body;
    }

    public function cancelEditPost(): void
    {
        $this->editingPost = false;
        $this->postTitleEdit = '';
        $this->postBodyEdit = '';
    }

    public function saveEditPost(): void
    {
        $post = CommunityPost::find($this->postId);
        if (!$post || $post->user_id !== Auth::id()) return;

        $this->validate([
            'postTitleEdit' => 'required|string|min:4|max:160',
            'postBodyEdit' => 'required|string|min:10|max:4000',
        ]);

        $post->update([
            'title' => trim($this->postTitleEdit),
            'body' => trim($this->postBodyEdit),
            'edited_at' => now(),
        ]);

        $this->editingPost = false;
        $this->postTitleEdit = '';
        $this->postBodyEdit = '';
    }

    public function startEditComment(int $commentId): void
    {
        $comment = CommunityComment::find($commentId);
        if (!$comment || $comment->user_id !== Auth::id()) return;
        $this->editingCommentId = $commentId;
        $this->commentBodyEdit = $comment->body;
    }

    public function cancelEditComment(): void
    {
        $this->editingCommentId = null;
        $this->commentBodyEdit = '';
    }

    public function saveEditComment(): void
    {
        if (!$this->editingCommentId) return;
        $comment = CommunityComment::find($this->editingCommentId);
        if (!$comment || $comment->user_id !== Auth::id()) return;

        $body = trim($this->commentBodyEdit);
        if ($body === '' || mb_strlen($body) > 2000) {
            $this->errorMessage = 'Comment must be between 1 and 2000 characters.';
            return;
        }

        $comment->update([
            'body' => $body,
            'edited_at' => now(),
        ]);

        $this->editingCommentId = null;
        $this->commentBodyEdit = '';
    }

    public function deletePost(): void
    {
        $user = Auth::user();
        if (!$user) return;
        $post = CommunityPost::with('attachments')->find($this->postId);
        if (!$post) return;
        if ($post->user_id !== $user->id && !$user->hasRole('super_admin')) {
            return;
        }
        foreach ($post->attachments as $attachment) {
            if ($attachment->path) {
                Storage::disk('public')->delete($attachment->path);
            }
        }
        $post->delete();
        $this->redirect(route('community'));
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        $post = CommunityPost::with(['user', 'attachments'])->findOrFail($this->postId);
        $comments = CommunityComment::with('user')
            ->where('community_post_id', $post->id)
            ->orderBy('created_at')
            ->get();

        $isLiked = Auth::check()
            ? $post->likes()->where('user_id', Auth::id())->exists()
            : false;

        $userMentionIds = collect()
            ->concat($post->mentioned_user_ids ?? [])
            ->concat($comments->flatMap(fn ($c) => $c->mentioned_user_ids ?? []))
            ->unique()->values()->all();
        $teamMentionIds = collect()
            ->concat($post->mentioned_team_ids ?? [])
            ->concat($comments->flatMap(fn ($c) => $c->mentioned_team_ids ?? []))
            ->unique()->values()->all();

        $userMentionMap = $userMentionIds
            ? User::whereIn('id', $userMentionIds)->select('id', 'name')->get()->keyBy('id')
            : collect();
        $teamMentionMap = $teamMentionIds
            ? Team::whereIn('id', $teamMentionIds)->select('id', 'name', 'slug')->get()->keyBy('id')
            : collect();

        $userResults = collect();
        $teamResults = collect();
        if (Auth::check() && trim($this->mentionSearch) !== '') {
            $needle = '%' . trim($this->mentionSearch) . '%';
            $excludeUserIds = collect($this->mentionedUsers)->pluck('id')->push(Auth::id())->all();
            $excludeTeamIds = collect($this->mentionedTeams)->pluck('id')->all();

            $userResults = User::query()
                ->whereNotIn('id', $excludeUserIds)
                ->where('name', 'like', $needle)
                ->select('id', 'name', 'institution')
                ->limit(6)
                ->get();

            $teamResults = Team::query()
                ->whereNotIn('id', $excludeTeamIds)
                ->where('name', 'like', $needle)
                ->select('id', 'name', 'tagline')
                ->limit(4)
                ->get();
        }

        return view('livewire.community-post-view', [
            'post' => $post,
            'comments' => $comments,
            'isLiked' => $isLiked,
            'categories' => CommunityPost::CATEGORIES,
            'userMentionMap' => $userMentionMap,
            'teamMentionMap' => $teamMentionMap,
            'userResults' => $userResults,
            'teamResults' => $teamResults,
        ]);
    }
}
