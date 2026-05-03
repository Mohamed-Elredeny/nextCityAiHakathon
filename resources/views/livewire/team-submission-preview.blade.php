@php
    $iconPath = function (string $name): string {
        return match ($name) {
            'document-text'         => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'exclamation-triangle'  => 'M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z',
            'light-bulb'            => 'M9.66 17h4.68m-2.34 4v-4m-7-1a8 8 0 1116 0c0 2.21-1 4.18-2.5 5.5L14 23H10l-1.5-3.5C7 18.18 6 16.21 6 14z',
            'cpu-chip'              => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 7h10v10H7V7z',
            'chart-bar'             => 'M3 3v18h18M7 14v4m4-9v9m4-13v13m4-7v7',
            'user-group'            => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9-2.13a4 4 0 11-8 0 4 4 0 018 0zm5-2a3 3 0 100-6 3 3 0 000 6zM6 11a3 3 0 100-6 3 3 0 000 6z',
            'book-open'             => 'M12 6.25v13M3 5.5h6a3 3 0 013 3v11a3 3 0 00-3-3H3v-11zm18 0h-6a3 3 0 00-3 3v11a3 3 0 013-3h6v-11z',
            'sparkles'              => 'M5 3v4m-2-2h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
            default                 => 'M5 13l4 4L19 7',
        };
    };

    $statusBadge = match ($submission?->status) {
        'submitted' => ['Submitted', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'validated' => ['Validated', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'accepted'  => ['Accepted',  'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'flagged'   => ['Flagged',   'bg-amber-50 text-amber-700 ring-amber-200'],
        'rejected'  => ['Rejected',  'bg-aiu-red-50 text-aiu-red ring-aiu-red/20'],
        'draft'     => ['In progress','bg-aiu-ink-50 text-aiu-ink-600 ring-aiu-line'],
        default     => ['Not started','bg-aiu-ink-50 text-aiu-ink-600 ring-aiu-line'],
    };
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
    {{-- Hero with banner + logo --}}
    <header class="card-3d rounded-3xl overflow-hidden mb-6 lg:mb-8 relative">
        @if ($team->banner_path)
            <div class="h-40 lg:h-56 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $team->banner_path) }}');">
                <div class="absolute inset-0 h-40 lg:h-56 bg-gradient-to-t from-white via-white/40 to-transparent"></div>
            </div>
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-aiu-red-50/60 via-white to-aiu-gold-50/40 pointer-events-none"></div>
        @endif

        <div class="relative p-6 lg:p-8 {{ $team->banner_path ? '-mt-20 lg:-mt-28' : '' }}">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs text-aiu-ink-600 hover:text-aiu-red transition mb-5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back to leaderboard
            </a>

            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div class="flex items-end gap-4 flex-1 min-w-0">
                    @if ($team->logo_path)
                        <img src="{{ asset('storage/' . $team->logo_path) }}" alt="{{ $team->name }}"
                             class="shrink-0 w-20 h-20 lg:w-28 lg:h-28 rounded-2xl object-cover ring-4 ring-white shadow-lg">
                    @else
                        <div class="shrink-0 w-20 h-20 lg:w-28 lg:h-28 rounded-2xl bg-gradient-to-br from-aiu-red to-aiu-gold text-white flex items-center justify-center font-heading font-bold text-3xl lg:text-5xl ring-4 ring-white shadow-lg">
                            {{ mb_substr($team->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full chip-3d text-aiu-red uppercase tracking-[0.22em] text-[10px] font-bold">
                                Team Submission
                            </span>
                            @if ($team->is_finalist)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-aiu-gold-50 text-aiu-gold-600 ring-1 ring-aiu-gold/30 text-[10px] uppercase tracking-wider font-bold">
                                    ★ Finalist
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] uppercase tracking-wider font-bold ring-1 {{ $statusBadge[1] }}">
                                {{ $statusBadge[0] }}
                            </span>
                        </div>
                        <h1 class="font-heading text-3xl lg:text-5xl font-bold text-aiu-ink-900 leading-tight">
                            {{ $team->name }}
                        </h1>
                        @if ($team->tagline)
                            <p class="mt-1 text-base text-aiu-ink-600 italic">{{ $team->tagline }}</p>
                        @endif
                        @if ($team->theme)
                            <p class="mt-2 text-sm text-aiu-ink-600 inline-flex items-center gap-2">
                                <svg class="w-4 h-4 text-aiu-red" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v18l7-4 7 4V3"/></svg>
                                <span>Theme: <span class="text-aiu-ink-900 font-semibold">{{ $team->theme->name }}</span></span>
                            </p>
                        @endif
                    </div>
                </div>

                <div class="card-3d rounded-2xl px-5 py-4 bg-white/80 backdrop-blur-sm">
                    <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-ink-400 font-bold mb-2">Members ({{ $team->members->count() }})</p>
                    <div class="flex flex-wrap gap-1.5 max-w-md">
                        @foreach ($team->members as $m)
                            <a href="{{ route('users.show', $m->id) }}"
                               class="inline-flex items-center gap-1.5 pl-1 pr-2 py-0.5 rounded-full chip-3d text-xs text-aiu-ink-700 hover:text-aiu-red transition">
                                <x-avatar :user="$m" size="xs" :leader="$m->id === $team->leader_id" />
                                <span class="truncate max-w-[8rem]">{{ $m->name }}</span>
                                @if ($m->id === $team->leader_id)
                                    <span class="text-aiu-red text-xs">★</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Team composition: which roles are filled / missing --}}
    @php
        $coverage = $team->role_coverage;
        $membersByRole = $team->teamMembers->groupBy('role_category');
        $missingCount = count($coverage['missing']);
    @endphp
    <section class="card-3d rounded-3xl p-6 lg:p-7 mb-6 lg:mb-8">
        <div class="flex items-center justify-between gap-3 mb-5">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-aiu-red-50 text-aiu-red">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9-2.13a4 4 0 11-8 0 4 4 0 018 0zm5-2a3 3 0 100-6 3 3 0 000 6zM6 11a3 3 0 100-6 3 3 0 000 6z"/></svg>
                </span>
                <div>
                    <h2 class="font-heading text-xl font-bold text-aiu-ink-900">Team Composition</h2>
                    <p class="text-xs text-aiu-ink-600">
                        {{ count($coverage['filled']) }} of 3 core roles filled
                        @if ($missingCount > 0)
                            · <span class="text-aiu-red font-semibold">missing {{ $missingCount }}</span>
                        @endif
                    </p>
                </div>
            </div>
            @if ($missingCount === 0)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 text-[10px] uppercase tracking-wider font-bold">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Complete
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @foreach (\App\Models\User::ROLE_CATEGORIES as $key => $label)
                @php
                    $people = $membersByRole->get($key, collect());
                    $filled = $people->isNotEmpty();
                @endphp
                <div class="relative rounded-2xl p-4 ring-1 transition
                            {{ $filled
                                ? 'bg-white ring-aiu-line'
                                : 'bg-aiu-red-50/40 ring-aiu-red/20 ring-dashed' }}">
                    <div class="flex items-center justify-between mb-2.5">
                        <p class="font-heading text-sm font-bold {{ $filled ? 'text-aiu-ink-900' : 'text-aiu-red' }}">
                            {{ $label }}
                        </p>
                        @if ($filled)
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </span>
                        @else
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-white text-aiu-red ring-1 ring-aiu-red/30">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </span>
                        @endif
                    </div>

                    @if ($filled)
                        <div class="space-y-1.5">
                            @foreach ($people as $tm)
                                @if ($tm->user)
                                    <a href="{{ route('users.show', $tm->user->id) }}"
                                       class="flex items-center gap-2 group">
                                        <x-avatar :user="$tm->user" size="xs" :leader="$tm->is_leader" />
                                        <span class="text-xs text-aiu-ink-700 group-hover:text-aiu-red truncate">{{ $tm->user->name }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-aiu-red/80 italic leading-snug">
                            No {{ strtolower($label) }} on this team yet.
                        </p>
                        @if ($team->is_recruiting)
                            <a href="{{ route('community.teams') }}"
                               class="inline-flex items-center gap-1 mt-2 text-[11px] font-semibold text-aiu-red hover:underline">
                                Apply to fill this role
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    {{-- Round tabs (only show if at least one submission exists) --}}
    @if ($submissionsByRound->isNotEmpty())
        <section class="card-3d rounded-3xl p-2 mb-4">
            <div class="flex gap-1">
                @foreach ($rounds as $roundKey => $roundLabel)
                    @php
                        $roundSub = $submissionsByRound->get($roundKey);
                        $isActive = $activeRound === $roundKey;
                        $exists = $roundSub !== null;
                    @endphp
                    <button type="button"
                        @if (!$exists) disabled @endif
                        wire:click="setRound('{{ $roundKey }}')"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition
                               {{ $isActive ? 'bg-aiu-red text-white shadow-sm' : ($exists ? 'text-aiu-ink-700 hover:bg-aiu-ink-50' : 'opacity-40 cursor-not-allowed text-aiu-ink-500') }}">
                        @if ($exists && $roundSub->status !== 'draft')
                            <svg class="w-4 h-4 {{ $isActive ? 'text-white' : 'text-emerald-600' }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        @endif
                        <span>{{ $roundLabel }}</span>
                        @if (!$exists)
                            <span class="text-[10px] uppercase tracking-wider px-1.5 py-0.5 rounded-md bg-aiu-ink-100 text-aiu-ink-500 font-bold">N/A</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Submission artifacts --}}
    @if ($submission)
        <section class="card-3d rounded-3xl p-6 lg:p-7 mb-6 lg:mb-8">
            <div class="flex items-center justify-between gap-3 mb-5">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl btn-aiu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </span>
                    <div>
                        <h2 class="font-heading text-xl font-bold text-aiu-ink-900">{{ $rounds[$activeRound] ?? 'Round 1' }} Submission</h2>
                        @if ($submission->submitted_at)
                            <p class="text-xs text-aiu-ink-600">Submitted on {{ $submission->submitted_at->format('M j, Y · H:i') }}</p>
                        @endif
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-md text-[10px] uppercase tracking-wider font-bold ring-1 {{ $submission->status === 'draft' ? 'bg-aiu-ink-50 text-aiu-ink-600 ring-aiu-line' : 'bg-emerald-50 text-emerald-700 ring-emerald-200' }}">
                    {{ ucfirst($submission->status) }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @if ($submission->report_pdf_path)
                    <a href="{{ asset('storage/' . $submission->report_pdf_path) }}" target="_blank" rel="noopener"
                       class="group card-3d card-3d-hover rounded-2xl p-4 transition flex items-start gap-3">
                        <span class="shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-xl bg-aiu-red-50 text-aiu-red group-hover:bg-aiu-red group-hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-[0.18em] text-aiu-ink-400 font-bold">PDF</p>
                            <p class="font-heading font-bold text-sm text-aiu-ink-900">Solution Report</p>
                        </div>
                    </a>
                @endif
                @if ($submission->slides_url)
                    <a href="{{ $submission->slides_url }}" target="_blank" rel="noopener"
                       class="group card-3d card-3d-hover rounded-2xl p-4 transition flex items-start gap-3">
                        <span class="shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-xl bg-aiu-ink-100 text-aiu-ink-700 group-hover:bg-aiu-red group-hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14-7H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-[0.18em] text-aiu-ink-400 font-bold">Link</p>
                            <p class="font-heading font-bold text-sm text-aiu-ink-900">Slide Deck</p>
                        </div>
                    </a>
                @endif
                @if ($submission->repo_url)
                    <a href="{{ $submission->repo_url }}" target="_blank" rel="noopener"
                       class="group card-3d card-3d-hover rounded-2xl p-4 transition flex items-start gap-3">
                        <span class="shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-xl bg-aiu-ink-100 text-aiu-ink-700 group-hover:bg-aiu-red group-hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-[0.18em] text-aiu-ink-400 font-bold">Code</p>
                            <p class="font-heading font-bold text-sm text-aiu-ink-900">Repository</p>
                        </div>
                    </a>
                @endif
                @if ($submission->video_url)
                    <a href="{{ $submission->video_url }}" target="_blank" rel="noopener"
                       class="group card-3d card-3d-hover rounded-2xl p-4 transition flex items-start gap-3">
                        <span class="shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-xl bg-aiu-ink-100 text-aiu-ink-700 group-hover:bg-aiu-red group-hover:text-white transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-[0.18em] text-aiu-ink-400 font-bold">Video</p>
                            <p class="font-heading font-bold text-sm text-aiu-ink-900">Demo</p>
                        </div>
                    </a>
                @endif
            </div>

            @if ($submission->ai_disclosure_text)
                <div class="mt-5 p-4 rounded-2xl bg-aiu-ink-50/60 ring-1 ring-aiu-line">
                    <p class="text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-1.5 inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-aiu-red" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4m-2-2h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        AI Tools Disclosure
                    </p>
                    <p class="text-sm text-aiu-ink-700 leading-relaxed whitespace-pre-wrap">{{ $submission->ai_disclosure_text }}</p>
                </div>
            @endif
        </section>
    @else
        <section class="card-3d rounded-3xl p-10 mb-6 lg:mb-8 text-center">
            <svg class="w-12 h-12 mx-auto text-aiu-ink-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="font-heading text-lg font-bold text-aiu-ink-900">No {{ $rounds[$activeRound] ?? '' }} submission yet</p>
            <p class="mt-1 text-sm text-aiu-ink-600">This team hasn't submitted for this round.</p>
        </section>
    @endif

    {{-- Solution Report sections --}}
    <section class="card-3d rounded-3xl overflow-hidden">
        <div class="p-6 lg:p-7 border-b border-aiu-line bg-gradient-to-br from-white to-aiu-ink-50/40">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-aiu-red-50 text-aiu-red">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.25v13M3 5.5h6a3 3 0 013 3v11a3 3 0 00-3-3H3v-11zm18 0h-6a3 3 0 00-3 3v11a3 3 0 013-3h6v-11z"/></svg>
                </span>
                <div>
                    <h2 class="font-heading text-xl font-bold text-aiu-ink-900">Solution Report</h2>
                    <p class="text-xs text-aiu-ink-600">{{ $drafts->count() }} of {{ count($sections) }} sections drafted by the team</p>
                </div>
            </div>
        </div>

        <div class="p-6 lg:p-7 space-y-6">
            @php $anyContent = false; @endphp
            @foreach ($sections as $key => $label)
                @php
                    $body = optional($drafts->get($key))->body;
                    if (filled($body)) { $anyContent = true; }
                    $meta = $sectionMeta[$key] ?? [];
                    $iconName = $meta['icon'] ?? '';
                @endphp
                @if (filled($body))
                    <article class="border-l-4 border-aiu-red/20 pl-4 py-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-aiu-ink-100 text-aiu-ink-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath($iconName) }}"/></svg>
                            </span>
                            <h3 class="font-heading font-bold text-aiu-ink-900">{{ $label }}</h3>
                        </div>
                        <p class="text-sm text-aiu-ink-700 leading-relaxed whitespace-pre-wrap">{{ $body }}</p>
                    </article>
                @endif
            @endforeach

            @unless ($anyContent)
                <div class="text-center py-12">
                    <svg class="w-12 h-12 mx-auto text-aiu-ink-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="font-heading text-lg font-bold text-aiu-ink-900">No content yet</p>
                    <p class="mt-1 text-sm text-aiu-ink-600">This team hasn't filled in their solution report.</p>
                </div>
            @endunless
        </div>
    </section>
</div>
