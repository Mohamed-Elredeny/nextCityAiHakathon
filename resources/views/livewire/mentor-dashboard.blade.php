<div class="max-w-7xl mx-auto px-6 lg:px-8 py-10">
    <header class="mb-8">
        <p class="inline-flex items-center gap-2 px-3 py-1 rounded-full chip-3d
                  text-aiu-red uppercase tracking-[0.22em] text-[10px] font-bold mb-3">
            Mentor Console
        </p>
        <h1 class="font-heading text-4xl font-bold text-aiu-ink-900">My Assigned Teams</h1>
        <p class="mt-2 text-sm text-aiu-ink-600">Leave advisory notes — these are visible to the team and organizers and do not affect scoring.</p>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <aside class="lg:col-span-4">
            <div class="card-3d rounded-2xl p-3 mb-4">
                <p class="px-3 pt-2 pb-3 text-[10px] uppercase tracking-[0.22em] text-aiu-ink-400 font-bold">Teams ({{ count($teams) }})</p>
                <div class="flex flex-col gap-1">
                    @forelse ($teams as $team)
                        @php $sel = $selectedTeamId === $team->id; @endphp
                        <button wire:click="selectTeam({{ $team->id }})" type="button"
                            class="text-left px-3 py-2.5 rounded-lg transition
                                   {{ $sel ? 'bg-aiu-red-50 ring-1 ring-aiu-red/30' : 'hover:bg-aiu-ink-50' }}">
                            <p class="font-heading font-bold text-sm text-aiu-ink-900">{{ $team->name }}</p>
                            <p class="text-[11px] text-aiu-ink-600">{{ $team->theme?->name ?? '—' }}</p>
                        </button>
                    @empty
                        <p class="px-3 py-6 text-center text-sm text-aiu-ink-400">No teams assigned yet.</p>
                    @endforelse
                </div>
            </div>

            @if (count($rotation))
                <div class="card-3d rounded-2xl p-3">
                    <p class="px-3 pt-2 pb-3 text-[10px] uppercase tracking-[0.22em] text-aiu-ink-400 font-bold">Speed-Round Rotation</p>
                    <div class="flex flex-col gap-1">
                        @foreach ($rotation as $slot)
                            <div class="px-3 py-2 rounded-lg surface-soft">
                                <p class="text-xs font-bold text-aiu-ink-900">{{ $slot->team?->name }}</p>
                                <p class="text-[11px] text-aiu-ink-400 tabular-nums">
                                    {{ $slot->slot_start->format('H:i') }} – {{ $slot->slot_end->format('H:i') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>

        <section class="lg:col-span-8">
            @if (!$selectedTeam)
                <div class="card-3d rounded-2xl p-12 text-center">
                    <p class="font-heading text-xl font-bold text-aiu-ink-900">Select a team to view notes</p>
                </div>
            @else
                @include('partials.team-submission-preview', [
                    'team' => $selectedTeam,
                    'drafts' => $selectedTeamDrafts,
                ])

                {{-- Direct chat with the team (mentor channel) --}}
                <div class="card-3d rounded-2xl overflow-hidden mb-6">
                    <div class="p-6 border-b border-aiu-line bg-gradient-to-r from-emerald-50/40 via-white to-white">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-emerald-600 text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </span>
                            <div>
                                <p class="text-[10px] uppercase tracking-[0.22em] text-emerald-700 font-bold">Mentor Channel</p>
                                <h2 class="font-heading text-xl font-bold text-aiu-ink-900">Chat with {{ $selectedTeam->name }}</h2>
                                <p class="text-xs text-aiu-ink-600 mt-0.5">Two-way thread with the team. They see your replies in their workspace.</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <form wire:submit="sendMessage" class="flex gap-2 mb-5">
                            <input type="text" wire:model="newMessage" placeholder="Reply to the team…"
                                   class="input-3d flex-1 px-4 py-2.5 rounded-lg text-sm">
                            <button type="submit" class="btn-aiu px-5 py-2.5 rounded-lg font-bold text-sm inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                Send
                            </button>
                        </form>
                        <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1">
                            @forelse ($messages as $m)
                                @php
                                    $initials = collect(explode(' ', $m->user->name ?? '?'))->take(2)->map(fn($p)=>mb_substr($p,0,1))->implode('');
                                    $authorRole = $m->user?->roles?->first()?->name ?? 'team_member';
                                    $isMine = $m->user_id === auth()->id();
                                    $roleBadge = match (true) {
                                        $authorRole === 'mentor' => ['Mentor', 'bg-emerald-600 text-white'],
                                        $authorRole === 'super_admin' => ['Admin', 'bg-aiu-ink-900 text-white'],
                                        $authorRole === 'team_leader' => ['Leader', 'bg-aiu-gold-500 text-white'],
                                        default => ['Member', 'bg-aiu-ink-200 text-aiu-ink-700'],
                                    };
                                @endphp
                                <div class="flex items-start gap-3 p-3 rounded-xl {{ $isMine ? 'bg-emerald-50/50 ring-1 ring-emerald-200' : 'surface-soft' }}">
                                    <span class="shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-full bg-aiu-red text-white text-[11px] font-bold uppercase">
                                        {{ $initials }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                                            <span class="font-bold text-aiu-ink-900">{{ $m->user->name ?? 'Unknown' }}</span>
                                            <span class="text-[9px] uppercase tracking-wider px-1.5 py-0.5 rounded-md {{ $roleBadge[1] }} font-bold">{{ $roleBadge[0] }}</span>
                                            <span class="text-aiu-ink-400 ml-auto">{{ $m->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="mt-1 text-sm text-aiu-ink-700 whitespace-pre-wrap leading-relaxed">{{ $m->body }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-aiu-ink-400 italic text-center py-8">No messages yet — start the conversation.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Private mentor notes (organizers only) --}}
                <div class="card-3d rounded-2xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-aiu-ink-100 text-aiu-ink-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-ink-400 font-bold">Private Notes</p>
                            <h2 class="font-heading text-xl font-bold text-aiu-ink-900">Mentor-only observations</h2>
                            <p class="text-xs text-aiu-ink-600 mt-0.5">Visible to organizers — not shown to the team.</p>
                        </div>
                    </div>

                    <form wire:submit="postNote" class="flex gap-2 mb-5">
                        <input type="text" wire:model="newNote" placeholder="Write an advisory note (private)…"
                               class="input-3d flex-1 px-4 py-2.5 rounded-lg text-sm">
                        <button type="submit" class="btn-soft px-5 py-2.5 rounded-lg font-semibold text-sm">
                            Save note
                        </button>
                    </form>

                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @forelse ($notes as $n)
                            <div class="surface-soft rounded-lg p-4">
                                <p class="text-[11px] text-aiu-ink-400 mb-1">{{ $n->created_at->diffForHumans() }}</p>
                                <p class="text-sm text-aiu-ink-700 whitespace-pre-wrap">{{ $n->body }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-aiu-ink-400 italic text-center py-8">No private notes yet.</p>
                        @endforelse
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>
