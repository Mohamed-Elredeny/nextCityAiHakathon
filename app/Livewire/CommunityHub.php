<?php

namespace App\Livewire;

use App\Models\CommunityPost;
use App\Models\CommunityPostAttachment;
use App\Models\Team;
use App\Models\User;
use App\Services\CommunityNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CommunityHub extends Component
{
    use WithPagination, WithFileUploads;

    public string $title = '';
    public string $body = '';
    public string $category = 'idea';
    public string $filter = 'all';
    public bool $showForm = false;
    public ?string $postedAt = null;
    public ?string $errorMessage = null;

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $attachments = [];

    public string $mentionSearch = '';
    /** @var array<int, array{id:int,name:string,subtitle:?string}> */
    public array $mentionedUsers = [];
    /** @var array<int, array{id:int,name:string,subtitle:?string}> */
    public array $mentionedTeams = [];

    public const MAX_ATTACHMENTS = 5;
    public const MAX_FILE_SIZE_KB = 15360; // 15 MB
    public const ALLOWED_MIMES = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'video/mp4', 'video/webm', 'video/quicktime',
        'audio/mpeg', 'audio/wav', 'audio/ogg',
        'application/pdf',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain', 'application/zip',
    ];

    public function setFilter(string $filter): void
    {
        $valid = array_merge(['all'], array_keys(CommunityPost::CATEGORIES));
        $this->filter = in_array($filter, $valid, true) ? $filter : 'all';
        $this->resetPage();
    }

    public function toggleForm(): void
    {
        $this->showForm = !$this->showForm;
        $this->errorMessage = null;
        if (!$this->showForm) {
            $this->attachments = [];
            $this->mentionedUsers = [];
            $this->mentionedTeams = [];
            $this->mentionSearch = '';
        }
    }

    public function addUserMention(int $userId): void
    {
        if (collect($this->mentionedUsers)->contains('id', $userId)) return;
        $user = User::select('id', 'name', 'institution')->find($userId);
        if (!$user || $user->id === Auth::id()) return;
        $this->mentionedUsers[] = [
            'id' => $user->id,
            'name' => $user->name,
            'subtitle' => $user->institution,
        ];
        $this->mentionSearch = '';
    }

    public function addTeamMention(int $teamId): void
    {
        if (collect($this->mentionedTeams)->contains('id', $teamId)) return;
        $team = Team::select('id', 'name', 'tagline')->find($teamId);
        if (!$team) return;
        $this->mentionedTeams[] = [
            'id' => $team->id,
            'name' => $team->name,
            'subtitle' => $team->tagline,
        ];
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

    public function removeAttachment(int $index): void
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments);
        }
    }

    public function updatedAttachments(): void
    {
        $allowedMimes = implode(',', self::ALLOWED_MIMES);
        $this->validate([
            'attachments' => 'array|max:' . self::MAX_ATTACHMENTS,
            'attachments.*' => 'file|max:' . self::MAX_FILE_SIZE_KB . '|mimetypes:' . $allowedMimes,
        ], [
            'attachments.max' => 'You can attach at most ' . self::MAX_ATTACHMENTS . ' files.',
            'attachments.*.max' => 'Each file must be under ' . round(self::MAX_FILE_SIZE_KB / 1024) . ' MB.',
            'attachments.*.mimetypes' => 'Unsupported file type. Allowed: images, videos, audio, PDF, Office docs, zip.',
        ]);
    }

    public function createPost(): void
    {
        $this->errorMessage = null;

        if (!Auth::check()) {
            $this->errorMessage = 'Please sign in to share an idea.';
            return;
        }

        $key = 'community-post:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $this->errorMessage = "You're posting too fast. Try again in {$seconds}s.";
            return;
        }
        RateLimiter::hit($key, 60);

        $allowedMimes = implode(',', self::ALLOWED_MIMES);
        $this->validate([
            'title' => 'required|string|min:4|max:160',
            'body' => 'required|string|min:10|max:4000',
            'category' => 'required|in:' . implode(',', array_keys(CommunityPost::CATEGORIES)),
            'attachments' => 'array|max:' . self::MAX_ATTACHMENTS,
            'attachments.*' => 'file|max:' . self::MAX_FILE_SIZE_KB . '|mimetypes:' . $allowedMimes,
        ], [
            'attachments.max' => 'You can attach at most ' . self::MAX_ATTACHMENTS . ' files.',
            'attachments.*.max' => 'Each file must be under ' . round(self::MAX_FILE_SIZE_KB / 1024) . ' MB.',
            'attachments.*.mimetypes' => 'Unsupported file type.',
        ]);

        $userIds = collect($this->mentionedUsers)->pluck('id')->all();
        $teamIds = collect($this->mentionedTeams)->pluck('id')->all();

        $post = CommunityPost::create([
            'user_id' => Auth::id(),
            'title' => trim($this->title),
            'body' => trim($this->body),
            'category' => $this->category,
            'mentioned_user_ids' => $userIds ?: null,
            'mentioned_team_ids' => $teamIds ?: null,
        ]);

        foreach ($this->attachments as $file) {
            $mime = $file->getMimeType() ?: 'application/octet-stream';
            $path = $file->store('community/' . $post->id, 'public');
            CommunityPostAttachment::create([
                'community_post_id' => $post->id,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mime,
                'size' => $file->getSize() ?: 0,
                'kind' => CommunityPostAttachment::kindFromMime($mime),
            ]);
        }

        if ($userIds || $teamIds) {
            $post->load('user');
            app(CommunityNotificationService::class)->notifyPostMentions($post, $userIds, $teamIds);
        }

        $this->reset(['title', 'body', 'attachments', 'mentionedUsers', 'mentionedTeams', 'mentionSearch']);
        $this->category = 'idea';
        $this->showForm = false;
        $this->postedAt = now()->toIso8601String();
        $this->resetPage();
    }

    public function toggleLike(int $postId): void
    {
        if (!Auth::check()) {
            $this->errorMessage = 'Please sign in to like a post.';
            return;
        }
        $post = CommunityPost::find($postId);
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

    public function deletePost(int $postId): void
    {
        $user = Auth::user();
        if (!$user) return;
        $post = CommunityPost::with('attachments')->find($postId);
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
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        $query = CommunityPost::query()
            ->with(['user', 'attachments'])
            ->withCount('comments')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at');

        if ($this->filter !== 'all') {
            $query->where('category', $this->filter);
        }

        $posts = $query->paginate(10);

        $likedIds = [];
        if (Auth::check() && $posts->isNotEmpty()) {
            $likedIds = \App\Models\CommunityPostLike::query()
                ->whereIn('community_post_id', $posts->pluck('id'))
                ->where('user_id', Auth::id())
                ->pluck('community_post_id')
                ->toArray();
        }

        $categoryCounts = CommunityPost::query()
            ->selectRaw('category, count(*) as c')
            ->groupBy('category')
            ->pluck('c', 'category')
            ->toArray();
        $categoryCounts['__total'] = array_sum($categoryCounts);

        $mentionedUserIds = collect($posts->all())
            ->flatMap(fn ($p) => $p->mentioned_user_ids ?? [])
            ->unique()->values()->all();
        $mentionedTeamIds = collect($posts->all())
            ->flatMap(fn ($p) => $p->mentioned_team_ids ?? [])
            ->unique()->values()->all();

        $userMentionMap = $mentionedUserIds
            ? User::whereIn('id', $mentionedUserIds)->select('id', 'name')->get()->keyBy('id')
            : collect();
        $teamMentionMap = $mentionedTeamIds
            ? Team::whereIn('id', $mentionedTeamIds)->select('id', 'name', 'slug')->get()->keyBy('id')
            : collect();

        $userResults = collect();
        $teamResults = collect();
        if (Auth::check() && trim($this->mentionSearch) !== '' && $this->showForm) {
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

        return view('livewire.community-hub', [
            'posts' => $posts,
            'categories' => CommunityPost::CATEGORIES,
            'likedIds' => array_flip($likedIds),
            'categoryCounts' => $categoryCounts,
            'userMentionMap' => $userMentionMap,
            'teamMentionMap' => $teamMentionMap,
            'userResults' => $userResults,
            'teamResults' => $teamResults,
        ]);
    }
}
