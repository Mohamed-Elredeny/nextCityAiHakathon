<div class="max-w-4xl mx-auto px-6 lg:px-8 py-10">
    <a href="{{ route('community') }}" class="inline-flex items-center gap-1.5 text-sm text-aiu-ink-600 hover:text-aiu-red transition mb-5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to community
    </a>

    <article class="card-3d rounded-2xl p-6 lg:p-8">
        <div class="flex items-start gap-4">
            <a href="{{ route('users.show', $post->user_id) }}" class="shrink-0">
                <x-avatar :user="$post->user" size="lg" />
            </a>

            <div class="flex-1 min-w-0">
                @if ($editingPost && auth()->check() && auth()->id() === $post->user_id)
                    <form wire:submit.prevent="saveEditPost" class="space-y-3">
                        <input type="text" wire:model="postTitleEdit" maxlength="160"
                               class="input-3d w-full px-3 py-2.5 rounded-lg text-base font-semibold">
                        @error('postTitleEdit') <p class="text-xs text-aiu-red">{{ $message }}</p> @enderror
                        <textarea wire:model="postBodyEdit" rows="6" maxlength="4000"
                                  class="input-3d w-full px-3 py-2.5 rounded-lg text-sm leading-relaxed"></textarea>
                        @error('postBodyEdit') <p class="text-xs text-aiu-red">{{ $message }}</p> @enderror
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" wire:click="cancelEditPost" class="btn-soft px-4 py-2 rounded-lg text-sm font-semibold">Cancel</button>
                            <button type="submit" class="btn-aiu px-5 py-2 rounded-lg text-sm font-semibold">Save</button>
                        </div>
                    </form>
                @else
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h1 class="font-heading font-bold text-2xl lg:text-3xl text-aiu-ink-900 leading-tight">{{ $post->title }}</h1>
                            <p class="mt-1.5 text-xs text-aiu-ink-600">
                                <a href="{{ route('users.show', $post->user_id) }}" class="font-semibold text-aiu-ink-700 hover:text-aiu-red">
                                    {{ $post->user->name }}
                                </a>
                                <span class="text-aiu-ink-400">·</span>
                                <span>{{ $post->created_at->diffForHumans() }}</span>
                                @if ($post->edited_at)
                                    <span class="text-aiu-ink-400">·</span>
                                    <span class="italic" title="Edited {{ $post->edited_at->diffForHumans() }}">edited</span>
                                @endif
                                @if ($post->category)
                                    <span class="text-aiu-ink-400">·</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full chip-3d text-[10px] uppercase tracking-wider font-semibold text-aiu-ink-700">
                                        {{ $categories[$post->category] ?? $post->category }}
                                    </span>
                                @endif
                            </p>
                        </div>

                        @auth
                            <div class="flex items-center gap-1 shrink-0">
                                @if (auth()->id() === $post->user_id)
                                    <button wire:click="startEditPost" class="text-aiu-ink-400 hover:text-aiu-red transition" title="Edit post">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                @endif
                                @if (auth()->id() === $post->user_id || auth()->user()->hasRole('super_admin'))
                                    <button wire:click="deletePost" wire:confirm="Delete this post and all its comments?"
                                            class="text-aiu-ink-400 hover:text-aiu-red transition" title="Delete post">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        @endauth
                    </div>

                    <div class="mt-5 text-sm lg:text-base text-aiu-ink-700 leading-relaxed prose-mentions">
                        {!! \App\Support\MentionRenderer::render($post->body, $userMentionMap, $teamMentionMap) !!}
                    </div>
                @endif

                @include('partials.mention-chips', [
                    'userIds' => $post->mentioned_user_ids ?? [],
                    'teamIds' => $post->mentioned_team_ids ?? [],
                    'userMap' => $userMentionMap,
                    'teamMap' => $teamMentionMap,
                ])

                @include('partials.community-attachments', ['attachments' => $post->attachments, 'compact' => false])

                <div class="mt-6 flex items-center gap-4 text-sm">
                    <button wire:click="toggleLike" type="button"
                            class="inline-flex items-center gap-1.5 transition hover:text-aiu-red
                                   {{ $isLiked ? 'text-aiu-red font-semibold' : 'text-aiu-ink-600' }}">
                        <svg class="w-5 h-5" fill="{{ $isLiked ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span class="tabular-nums">{{ $post->likes_count }}</span>
                        <span class="hidden sm:inline">{{ $post->likes_count === 1 ? 'like' : 'likes' }}</span>
                    </button>
                    <span class="inline-flex items-center gap-1.5 text-aiu-ink-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span class="tabular-nums">{{ $post->comments_count }}</span>
                        <span class="hidden sm:inline">{{ $post->comments_count === 1 ? 'comment' : 'comments' }}</span>
                    </span>
                </div>
            </div>
        </div>
    </article>

    <section class="mt-8">
        <h2 class="font-heading font-bold text-lg text-aiu-ink-900 mb-4">
            {{ $comments->count() }} {{ $comments->count() === 1 ? 'comment' : 'comments' }}
        </h2>

        @auth
            <div class="card-3d rounded-2xl p-4 lg:p-5 mb-5">
                <div class="flex items-start gap-3">
                    <x-avatar :user="auth()->user()" size="sm" />
                    <div class="flex-1">
                        <textarea wire:model="newComment" rows="3" maxlength="2000"
                                  placeholder="Write a thoughtful comment…"
                                  class="input-3d w-full px-3 py-2.5 rounded-lg text-sm leading-relaxed"></textarea>

                        <div class="mt-3">
                            @include('partials.mention-picker', ['compact' => true])
                        </div>

                        @if ($errorMessage)
                            <p class="mt-2 text-xs text-aiu-red">{{ $errorMessage }}</p>
                        @endif
                        <div class="mt-3 flex items-center justify-end">
                            <button wire:click="postComment" type="button" class="btn-aiu px-4 py-2 rounded-lg text-sm font-semibold">
                                Post comment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card-3d rounded-2xl p-4 mb-5 text-center text-sm text-aiu-ink-600">
                <a href="{{ route('login') }}" class="text-aiu-red font-semibold hover:underline">Sign in</a> to join the discussion.
            </div>
        @endauth

        @if ($comments->isEmpty())
            <div class="text-center text-sm text-aiu-ink-500 py-8">No comments yet. Start the conversation.</div>
        @else
            <div class="space-y-3">
                @foreach ($comments as $comment)
                    <div class="card-3d rounded-2xl p-4 lg:p-5">
                        <div class="flex items-start gap-3">
                            <a href="{{ route('users.show', $comment->user_id) }}" class="shrink-0">
                                <x-avatar :user="$comment->user" size="sm" />
                            </a>
                            <div class="flex-1 min-w-0">
                                @if ($editingCommentId === $comment->id)
                                    <form wire:submit.prevent="saveEditComment" class="space-y-2">
                                        <textarea wire:model="commentBodyEdit" rows="3" maxlength="2000"
                                                  class="input-3d w-full px-3 py-2 rounded-lg text-sm leading-relaxed"></textarea>
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" wire:click="cancelEditComment" class="btn-soft px-3 py-1 rounded-lg text-xs font-semibold">Cancel</button>
                                            <button type="submit" class="btn-aiu px-4 py-1 rounded-lg text-xs font-semibold">Save</button>
                                        </div>
                                    </form>
                                @else
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="text-xs text-aiu-ink-600">
                                            <a href="{{ route('users.show', $comment->user_id) }}" class="font-semibold text-aiu-ink-700 hover:text-aiu-red">
                                                {{ $comment->user->name }}
                                            </a>
                                            <span class="text-aiu-ink-400">·</span>
                                            <span>{{ $comment->created_at->diffForHumans() }}</span>
                                            @if ($comment->edited_at)
                                                <span class="text-aiu-ink-400">·</span>
                                                <span class="italic" title="Edited {{ $comment->edited_at->diffForHumans() }}">edited</span>
                                            @endif
                                        </p>
                                        @auth
                                            <div class="flex items-center gap-1 shrink-0">
                                                @if (auth()->id() === $comment->user_id)
                                                    <button wire:click="startEditComment({{ $comment->id }})"
                                                            class="text-aiu-ink-400 hover:text-aiu-red transition" title="Edit comment">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                                @if (auth()->id() === $comment->user_id || auth()->user()->hasRole('super_admin'))
                                                    <button wire:click="deleteComment({{ $comment->id }})" wire:confirm="Delete this comment?"
                                                            class="text-aiu-ink-400 hover:text-aiu-red transition" title="Delete comment">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        @endauth
                                    </div>
                                    <div class="mt-1.5 text-sm text-aiu-ink-700 leading-relaxed prose-mentions">
                                        {!! \App\Support\MentionRenderer::render($comment->body, $userMentionMap, $teamMentionMap) !!}
                                    </div>
                                @endif
                                @include('partials.mention-chips', [
                                    'userIds' => $comment->mentioned_user_ids ?? [],
                                    'teamIds' => $comment->mentioned_team_ids ?? [],
                                    'userMap' => $userMentionMap,
                                    'teamMap' => $teamMentionMap,
                                ])
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
