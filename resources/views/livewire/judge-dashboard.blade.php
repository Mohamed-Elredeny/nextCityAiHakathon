<div class="max-w-7xl mx-auto px-6 lg:px-8 py-10">
    <header class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-8">
        <div>
            <p class="inline-flex items-center gap-2 px-3 py-1 rounded-full chip-3d
                      text-aiu-red uppercase tracking-[0.22em] text-[10px] font-bold mb-3">
                Judge Console
            </p>
            <h1 class="font-heading text-4xl lg:text-5xl font-bold text-aiu-ink-900">Score Submissions</h1>
            <p class="mt-2 text-sm text-aiu-ink-600">
                Score each assigned team on the official 6-criterion rubric. Once locked, scores cannot be edited.
            </p>
        </div>
        <div class="inline-flex p-1 rounded-xl chip-3d gap-1 self-start lg:self-auto">
            <button wire:click="setRound('round1')"
                class="px-5 py-2 rounded-lg text-sm font-semibold transition
                       {{ $round === 'round1' ? 'btn-aiu' : 'text-aiu-ink-700 hover:bg-aiu-ink-50' }}">
                Round 1
            </button>
            <button wire:click="setRound('finals')"
                class="px-5 py-2 rounded-lg text-sm font-semibold transition
                       {{ $round === 'finals' ? 'btn-aiu' : 'text-aiu-ink-700 hover:bg-aiu-ink-50' }}">
                Finals
            </button>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <aside class="lg:col-span-4">
            <div class="card-3d rounded-2xl p-3 lg:sticky lg:top-24">
                <div class="px-3 pt-2 pb-3">
                    <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-ink-400 font-bold">
                        Assigned Teams ({{ count($teams) }})
                    </p>
                </div>

                {{-- Search + theme filter --}}
                <div class="px-2 mb-3 space-y-2">
                    <div class="relative">
                        <input type="search" wire:model.live.debounce.300ms="teamSearch" placeholder="Search team…"
                               class="input-3d w-full pl-8 pr-3 py-2 rounded-lg text-xs">
                        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-aiu-ink-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    @if ($availableThemes->isNotEmpty())
                        <div class="flex flex-wrap gap-1">
                            <button type="button" wire:click="setThemeFilter(null)"
                                class="px-2 py-1 rounded-md text-[10px] uppercase tracking-wider font-bold transition
                                       {{ $themeFilter === null ? 'bg-aiu-red text-white' : 'bg-aiu-ink-100 text-aiu-ink-600 hover:bg-aiu-ink-200' }}">
                                All themes
                            </button>
                            @foreach ($availableThemes as $t)
                                <button type="button" wire:click="setThemeFilter({{ $t->id }})"
                                    class="px-2 py-1 rounded-md text-[10px] uppercase tracking-wider font-bold transition
                                           {{ $themeFilter === $t->id ? 'bg-aiu-red text-white' : 'bg-aiu-ink-100 text-aiu-ink-700 hover:bg-aiu-ink-200' }}">
                                    {{ Str::limit($t->name, 18) }}
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @if ($themeFilter || filled($teamSearch))
                        <button type="button" wire:click="clearFilters" class="text-[10px] text-aiu-red hover:underline">
                            Clear filters
                        </button>
                    @endif
                </div>

                <div class="flex flex-col gap-1 max-h-[60vh] overflow-y-auto">
                    @forelse ($teams as $team)
                        @php
                            $isSelected = $selectedTeamId === $team->id;
                            $score = $team->my_score;
                            $isLockedTeam = $score?->locked_at !== null;
                        @endphp
                        <button wire:click="selectTeam({{ $team->id }})" type="button"
                            class="text-left px-3 py-3 rounded-lg transition flex items-start gap-2.5
                                   {{ $isSelected
                                      ? 'bg-aiu-red-50 ring-1 ring-aiu-red/30'
                                      : 'hover:bg-aiu-ink-50' }}">
                            @if ($team->logo_path)
                                <img src="{{ asset('storage/' . $team->logo_path) }}" alt="{{ $team->name }}"
                                     class="shrink-0 w-9 h-9 rounded-lg object-cover ring-1 ring-aiu-line">
                            @else
                                <div class="shrink-0 w-9 h-9 rounded-lg bg-gradient-to-br from-aiu-red/15 to-aiu-gold/15 text-aiu-red font-heading font-bold text-sm flex items-center justify-center">
                                    {{ mb_substr($team->name, 0, 1) }}
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="font-heading font-bold text-sm text-aiu-ink-900 truncate">{{ $team->name }}</p>
                                <p class="text-[11px] text-aiu-ink-600 truncate">{{ $team->theme?->name ?? '—' }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                @if ($isLockedTeam)
                                    <span class="text-[10px] uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 font-bold">Locked</span>
                                    <p class="mt-1 font-mono text-xs text-aiu-red font-bold tabular-nums">{{ number_format((float) $score->weighted_total, 2) }}</p>
                                @elseif ($score)
                                    <span class="text-[10px] uppercase tracking-wider px-2 py-0.5 rounded-full bg-aiu-ink-50 text-aiu-ink-600 ring-1 ring-aiu-line font-bold">Draft</span>
                                @else
                                    <span class="text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold">Pending</span>
                                @endif
                            </div>
                        </button>
                    @empty
                        <p class="px-3 py-6 text-center text-sm text-aiu-ink-400">
                            @if ($themeFilter || filled($teamSearch))
                                No teams match your filters.
                            @else
                                No teams have been assigned to you for this round yet.
                            @endif
                        </p>
                    @endforelse
                </div>
            </div>
        </aside>

        <section class="lg:col-span-8">
            @if (!$selectedTeam)
                <div class="card-3d rounded-2xl p-12 text-center">
                    <div class="logo-plate inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-4">
                        <svg class="w-7 h-7 text-aiu-red" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/>
                        </svg>
                    </div>
                    <p class="font-heading text-xl font-bold text-aiu-ink-900">Select a team to start scoring</p>
                    <p class="mt-2 text-sm text-aiu-ink-600">Pick from your assigned teams on the left.</p>
                </div>
            @else
                @include('partials.team-submission-preview', [
                    'team' => $selectedTeam,
                    'drafts' => $selectedTeamDrafts,
                ])

                <div class="card-3d rounded-2xl p-6 lg:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-red font-bold">Score this team</p>
                            <h2 class="font-heading text-2xl font-bold text-aiu-ink-900 mt-1">{{ $selectedTeam->name }}</h2>
                            <p class="text-sm text-aiu-ink-600">{{ $selectedTeam->theme?->name ?? '—' }}</p>
                        </div>
                        @if ($isLocked)
                            <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 text-[10px] uppercase tracking-wider font-bold">Locked</span>
                        @endif
                    </div>

                    <fieldset @if($isLocked) disabled @endif class="{{ $isLocked ? 'opacity-70' : '' }} space-y-5">
                        @foreach ($weights as $criterion => $weight)
                            @php $current = (int) round((float) $scores[$criterion]); @endphp
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-sm font-bold text-aiu-ink-900 capitalize">
                                        {{ str_replace('_', ' ', $criterion) }}
                                        <span class="ml-2 text-[10px] uppercase tracking-wider text-aiu-red font-bold">{{ ($weight * 100) }}%</span>
                                    </label>
                                    <span class="font-mono text-lg font-bold text-aiu-red tabular-nums w-12 text-right">
                                        {{ $current ?: '—' }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-10 gap-1.5">
                                    @for ($n = 1; $n <= 10; $n++)
                                        <button type="button"
                                                wire:click="$set('scores.{{ $criterion }}', {{ $n }})"
                                                @if($isLocked) disabled @endif
                                                class="h-9 rounded-md text-xs font-bold tabular-nums ring-1 transition
                                                       {{ $current === $n
                                                            ? 'bg-aiu-red text-white ring-aiu-red shadow-sm'
                                                            : 'bg-white text-aiu-ink-700 ring-aiu-line hover:ring-aiu-red/40 hover:text-aiu-red' }}">
                                            {{ $n }}
                                        </button>
                                    @endfor
                                </div>
                            </div>
                        @endforeach

                        <div>
                            <label class="block text-sm font-bold text-aiu-ink-900 mb-2">Comment (optional)</label>
                            <textarea wire:model="comment" rows="3" placeholder="Notes for the team or organizers..."
                                      class="input-3d w-full px-4 py-3 rounded-lg text-sm"></textarea>
                        </div>
                    </fieldset>

                    <div class="mt-6 p-4 rounded-xl surface-soft ring-1 ring-aiu-red/20 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold">Weighted Total</p>
                            <p class="font-heading text-3xl font-bold text-aiu-red tabular-nums">{{ number_format($weightedPreview, 2) }}</p>
                        </div>
                        <p class="text-xs text-aiu-ink-600 max-w-xs text-right">
                            Innovation 20% · Technical 25% · Impact 20% · UX 15% · Pitch 10% · Business 10%
                        </p>
                    </div>

                    @if ($errorMessage)
                        <p class="mt-4 px-4 py-2 rounded-lg bg-aiu-red-50 text-aiu-red ring-1 ring-aiu-red/20 text-sm">{{ $errorMessage }}</p>
                    @endif
                    @if ($successMessage)
                        <p class="mt-4 px-4 py-2 rounded-lg bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 text-sm">{{ $successMessage }}</p>
                    @endif

                    @unless ($isLocked)
                        <div class="mt-6 flex flex-col sm:flex-row gap-3">
                            <button wire:click="saveDraft" class="btn-soft px-5 py-2.5 rounded-lg font-semibold text-sm">
                                Save draft
                            </button>
                            <button wire:click="lockScore"
                                    wire:confirm="Locking is final — you cannot edit this score afterward. Continue?"
                                    class="btn-aiu px-5 py-2.5 rounded-lg font-bold uppercase tracking-wider text-xs">
                                Lock final score
                            </button>
                        </div>

                        <details class="mt-6 pt-5 border-t border-aiu-line">
                            <summary class="cursor-pointer text-xs text-aiu-ink-500 hover:text-aiu-red font-semibold inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
                                I have a conflict of interest with this team
                            </summary>
                            <div class="mt-3 surface-soft rounded-xl p-4 space-y-3">
                                <p class="text-xs text-aiu-ink-600 leading-relaxed">If you can't fairly evaluate this team (e.g. you mentored them, they're family, you have a business relationship), recuse yourself here. Your draft will be discarded and the team removed from your list.</p>
                                <textarea wire:model="recuseReason" rows="2" placeholder="Briefly describe the conflict (visible to organizers only)…"
                                          class="input-3d w-full px-3 py-2 rounded-lg text-xs"></textarea>
                                <button wire:click="selfRecuse"
                                        wire:confirm="Recuse yourself from {{ $selectedTeam->name }}? Your draft for this team will be lost."
                                        class="btn-soft px-4 py-1.5 rounded-lg text-xs font-bold text-aiu-red ring-1 ring-aiu-red/20 hover:bg-aiu-red-50">
                                    Recuse from this team
                                </button>
                            </div>
                        </details>
                    @endunless
                </div>

                {{-- Judge ↔ Team channel --}}
                <div class="card-3d rounded-2xl overflow-hidden mt-6">
                    <div class="p-6 border-b border-aiu-line bg-gradient-to-r from-aiu-red-50/40 via-white to-white">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-aiu-red text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </span>
                            <div>
                                <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-red font-bold">Judge Channel</p>
                                <h2 class="font-heading text-xl font-bold text-aiu-ink-900">Q&amp;A with {{ $selectedTeam->name }}</h2>
                                <p class="text-xs text-aiu-ink-600 mt-0.5">Ask the team for clarifications. They see your messages in their workspace.</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <form wire:submit="sendMessage" class="flex gap-2 mb-5">
                            <input type="text" wire:model="newMessage" placeholder="Ask the team a clarifying question…"
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
                                        $authorRole === 'judge' => ['Judge', 'bg-aiu-red text-white'],
                                        $authorRole === 'super_admin' => ['Admin', 'bg-aiu-ink-900 text-white'],
                                        $authorRole === 'team_leader' => ['Leader', 'bg-aiu-gold-500 text-white'],
                                        default => ['Member', 'bg-aiu-ink-200 text-aiu-ink-700'],
                                    };
                                @endphp
                                <div class="flex items-start gap-3 p-3 rounded-xl {{ $isMine ? 'bg-aiu-red-50/50 ring-1 ring-aiu-red/10' : 'surface-soft' }}">
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
                                <p class="text-sm text-aiu-ink-400 italic text-center py-8">No messages yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>
