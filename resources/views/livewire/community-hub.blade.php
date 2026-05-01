<div>
    <section class="relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10 lg:py-14">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div class="max-w-2xl">
                    <p class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full chip-3d
                              text-aiu-red uppercase tracking-[0.22em] text-[11px] font-bold">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-aiu-red animate-pulse"></span>
                        Community
                    </p>
                    <h1 class="mt-4 font-heading font-bold text-3xl lg:text-5xl leading-[1.05] tracking-tight text-aiu-ink-900">
                        Share ideas. Find your team.
                    </h1>
                    <p class="mt-4 text-base text-aiu-ink-600 leading-relaxed max-w-xl">
                        Post discussions, ask questions, share resources, or look for a team to join before the hackathon kicks off.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('community.teams') }}" class="btn-soft px-4 py-2.5 rounded-xl text-sm font-semibold inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Recruiting Teams
                    </a>
                    @auth
                        <button wire:click="toggleForm" type="button" class="btn-aiu px-4 py-2.5 rounded-xl text-sm font-semibold inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            New post
                        </button>
                    @else
                        <a href="{{ route('login') }}" class="btn-aiu px-4 py-2.5 rounded-xl text-sm font-semibold">Sign in to post</a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-6 lg:px-8 pb-16">
        @auth
            @if ($showForm)
                <form wire:submit.prevent="createPost" class="card-3d rounded-2xl p-5 lg:p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-heading font-bold text-xl text-aiu-ink-900">Share with the community</h2>
                        <button type="button" wire:click="toggleForm" class="text-aiu-ink-400 hover:text-aiu-ink-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-aiu-ink-700 mb-1.5">Title</label>
                            <input type="text" wire:model="title" maxlength="160" placeholder="Short, descriptive title"
                                   class="input-3d w-full px-3 py-2.5 rounded-lg text-sm">
                            @error('title') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-aiu-ink-700 mb-1.5">Category</label>
                            <select wire:model="category" class="input-3d w-full px-3 py-2.5 rounded-lg text-sm">
                                @foreach ($categories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-aiu-ink-700 mb-1.5">Your idea</label>
                        <textarea wire:model="body" rows="6" maxlength="4000" placeholder="What's on your mind? Share details, links, or context."
                                  class="input-3d w-full px-3 py-2.5 rounded-lg text-sm leading-relaxed"></textarea>
                        @error('body') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                    </div>

                    {{-- Mention picker --}}
                    <div class="mb-4">
                        @include('partials.mention-picker', ['compact' => false])
                    </div>

                    {{-- Attachments --}}
                    <div class="mb-4" wire:key="attachments-block">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-aiu-ink-700">Attachments <span class="text-aiu-ink-400 font-normal">(optional, up to 5 · 15 MB each)</span></label>
                            @if (count($attachments) > 0)
                                <span class="text-[11px] text-aiu-ink-500">{{ count($attachments) }}/5</span>
                            @endif
                        </div>

                        <label class="surface-soft rounded-xl border-2 border-dashed border-aiu-line hover:border-aiu-red/40 hover:bg-aiu-red-50/30 transition cursor-pointer flex flex-col items-center justify-center gap-1.5 px-4 py-6 text-center">
                            <svg class="w-7 h-7 text-aiu-ink-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            <p class="text-xs font-semibold text-aiu-ink-700">
                                <span wire:loading.remove wire:target="attachments">Click to attach files or media</span>
                                <span wire:loading wire:target="attachments" class="text-aiu-red">Uploading…</span>
                            </p>
                            <p class="text-[10px] text-aiu-ink-500">Images, video, audio, PDF, Office docs, zip</p>
                            <input type="file" wire:model="attachments" multiple class="hidden"
                                   accept="image/*,video/*,audio/*,application/pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.zip">
                        </label>

                        @error('attachments') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                        @error('attachments.*') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror

                        @if (count($attachments) > 0)
                            <ul class="mt-2 space-y-1.5">
                                @foreach ($attachments as $i => $file)
                                    @php
                                        $name = method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : 'file';
                                        $size = method_exists($file, 'getSize') ? $file->getSize() : 0;
                                        $kb = $size > 0 ? round($size / 1024, 1) : 0;
                                        $sizeText = $kb >= 1024 ? round($kb / 1024, 1) . ' MB' : $kb . ' KB';
                                        $mime = method_exists($file, 'getMimeType') ? ($file->getMimeType() ?? '') : '';
                                        $isImg = str_starts_with($mime, 'image/');
                                    @endphp
                                    <li class="flex items-center gap-2 px-3 py-2 rounded-lg surface-soft text-xs">
                                        @if ($isImg)
                                            <span class="shrink-0 inline-flex items-center justify-center w-7 h-7 rounded bg-aiu-red-50 text-aiu-red">🖼</span>
                                        @else
                                            <span class="shrink-0 inline-flex items-center justify-center w-7 h-7 rounded bg-aiu-ink-100 text-aiu-ink-700">📎</span>
                                        @endif
                                        <span class="flex-1 truncate font-semibold text-aiu-ink-900">{{ $name }}</span>
                                        <span class="text-aiu-ink-500">{{ $sizeText }}</span>
                                        <button type="button" wire:click="removeAttachment({{ $i }})"
                                                class="text-aiu-ink-400 hover:text-aiu-red shrink-0" title="Remove">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    @if ($errorMessage)
                        <p class="mb-3 text-xs text-aiu-red">{{ $errorMessage }}</p>
                    @endif

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" wire:click="toggleForm" class="btn-soft px-4 py-2 rounded-lg text-sm font-semibold">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="createPost,attachments"
                                class="btn-aiu px-5 py-2 rounded-lg text-sm font-semibold">
                            <span wire:loading.remove wire:target="createPost">Post</span>
                            <span wire:loading wire:target="createPost">Posting…</span>
                        </button>
                    </div>
                </form>
            @endif
        @endauth

        <div class="flex flex-wrap items-center gap-2 mb-5">
            <button wire:click="setFilter('all')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition
                {{ $filter === 'all' ? 'btn-aiu' : 'chip-3d text-aiu-ink-700 hover:text-aiu-red' }}">
                All
                <span class="tabular-nums text-[10px] px-1.5 py-0.5 rounded-full
                    {{ $filter === 'all' ? 'bg-white/25 text-white' : 'bg-aiu-ink-100 text-aiu-ink-600' }}">
                    {{ $categoryCounts['__total'] ?? 0 }}
                </span>
            </button>
            @foreach ($categories as $key => $label)
                @php $count = $categoryCounts[$key] ?? 0; @endphp
                <button wire:click="setFilter('{{ $key }}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition
                    {{ $filter === $key ? 'btn-aiu' : 'chip-3d text-aiu-ink-700 hover:text-aiu-red' }}">
                    {{ $label }}
                    <span class="tabular-nums text-[10px] px-1.5 py-0.5 rounded-full
                        {{ $filter === $key ? 'bg-white/25 text-white' : ($count > 0 ? 'bg-aiu-ink-100 text-aiu-ink-600' : 'bg-aiu-ink-50 text-aiu-ink-400') }}">
                        {{ $count }}
                    </span>
                </button>
            @endforeach
        </div>

        @if ($posts->isEmpty())
            <div class="card-3d rounded-2xl p-10 text-center">
                <p class="text-aiu-ink-600 text-sm">No posts yet in this category. Be the first to share an idea.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($posts as $post)
                    <article class="card-3d rounded-2xl p-5 lg:p-6">
                        <div class="flex items-start gap-4">
                            <a href="{{ route('users.show', $post->user_id) }}" class="shrink-0">
                                <x-avatar :user="$post->user" size="md" />
                            </a>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3 mb-1">
                                    <div class="min-w-0">
                                        <a href="{{ route('community.show', $post->id) }}"
                                           class="font-heading font-bold text-lg text-aiu-ink-900 hover:text-aiu-red transition leading-tight block">
                                            {{ $post->title }}
                                        </a>
                                        <p class="mt-0.5 text-xs text-aiu-ink-600">
                                            <a href="{{ route('users.show', $post->user_id) }}" class="font-semibold text-aiu-ink-700 hover:text-aiu-red">
                                                {{ $post->user->name }}
                                            </a>
                                            <span class="text-aiu-ink-400">·</span>
                                            <span>{{ $post->created_at->diffForHumans() }}</span>
                                            @if ($post->category)
                                                <span class="text-aiu-ink-400">·</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full chip-3d text-[10px] uppercase tracking-wider font-semibold text-aiu-ink-700">
                                                    {{ $categories[$post->category] ?? $post->category }}
                                                </span>
                                            @endif
                                        </p>
                                    </div>

                                    @auth
                                        @if (auth()->id() === $post->user_id || auth()->user()->hasRole('super_admin'))
                                            <button wire:click="deletePost({{ $post->id }})"
                                                    wire:confirm="Delete this post?"
                                                    class="text-aiu-ink-400 hover:text-aiu-red transition shrink-0"
                                                    title="Delete post">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                                                </svg>
                                            </button>
                                        @endif
                                    @endauth
                                </div>

                                <div class="mt-2 text-sm text-aiu-ink-700 leading-relaxed line-clamp-4 prose-mentions">
                                    {!! \App\Support\MentionRenderer::render($post->body, $userMentionMap, $teamMentionMap) !!}
                                </div>

                                @include('partials.mention-chips', [
                                    'userIds' => $post->mentioned_user_ids ?? [],
                                    'teamIds' => $post->mentioned_team_ids ?? [],
                                    'userMap' => $userMentionMap,
                                    'teamMap' => $teamMentionMap,
                                ])

                                @include('partials.community-attachments', ['attachments' => $post->attachments, 'compact' => true])

                                <div class="mt-4 flex items-center gap-4 text-xs text-aiu-ink-600">
                                    <button wire:click="toggleLike({{ $post->id }})" type="button"
                                            class="inline-flex items-center gap-1.5 hover:text-aiu-red transition
                                                   {{ isset($likedIds[$post->id]) ? 'text-aiu-red font-semibold' : '' }}">
                                        <svg class="w-4 h-4" fill="{{ isset($likedIds[$post->id]) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                        <span class="tabular-nums">{{ $post->likes_count }}</span>
                                    </button>

                                    <a href="{{ route('community.show', $post->id) }}" class="inline-flex items-center gap-1.5 hover:text-aiu-red transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                        <span class="tabular-nums">{{ $post->comments_count }}</span>
                                        <span class="hidden sm:inline">comments</span>
                                    </a>

                                    <a href="{{ route('community.show', $post->id) }}" class="ml-auto text-aiu-red font-semibold hover:underline">Read more →</a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $posts->links() }}
            </div>
        @endif
    </section>
</div>
