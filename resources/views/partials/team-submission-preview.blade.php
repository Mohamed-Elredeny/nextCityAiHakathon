@php
    // Pick the right submission row: prefer finals if it exists (newer), else round1
    $allSubs = $team->relationLoaded('submissions')
        ? $team->submissions->keyBy('round')
        : \App\Models\Submission::where('team_id', $team->id)->get()->keyBy('round');
    $sub = $allSubs->get(\App\Models\Submission::ROUND_FINALS) ?? $allSubs->get(\App\Models\Submission::ROUND_ONE);
    $teamMembersForRoles = $team->relationLoaded('teamMembers')
        ? $team->teamMembers
        : \App\Models\TeamMember::with('user')->where('team_id', $team->id)->get();
    $coverage = $team->role_coverage;
    $membersByRole = $teamMembersForRoles->groupBy('role_category');
    $sections = [
        'problem'       => 'Problem Statement',
        'solution'      => 'Proposed Solution',
        'architecture'  => 'Technical Architecture',
        'impact'        => 'Impact & Scalability',
        'roles'         => 'Team Roles',
        'references'    => 'References & Resources',
        'ai_disclosure' => 'AI Tools Disclosure',
    ];

    $hasLinks = $sub && ($sub->video_url || $sub->slides_url || $sub->repo_url || $sub->report_pdf_path);
    $hasContent = collect(array_keys($sections))
        ->some(fn ($k) => filled(optional($drafts->get($k))->body));
    $hasAiDisclosure = $sub && filled($sub->ai_disclosure_text);
@endphp

<div class="card-3d rounded-2xl p-6 lg:p-7 mb-6 bg-gradient-to-b from-white to-aiu-ink-50/40">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-5 pb-5 border-b border-aiu-line">
        <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-red font-bold">
                    Team Submission · {{ $team->theme?->name ?? 'No theme' }}
                </p>
                @if ($sub)
                    <span class="text-[9px] uppercase tracking-wider px-1.5 py-0.5 rounded-md bg-aiu-ink-100 text-aiu-ink-700 font-bold">
                        {{ $sub->round === 'finals' ? 'Finals entry' : 'Round 1 entry' }}
                    </span>
                @endif
            </div>
            <h3 class="font-heading text-2xl font-bold text-aiu-ink-900 mt-1">{{ $team->name }}</h3>
            @if ($team->tagline)
                <p class="mt-1 text-sm text-aiu-ink-600 italic leading-snug">{{ $team->tagline }}</p>
            @endif
            @if ($team->is_finalist)
                <span class="inline-block mt-2 text-[9px] uppercase tracking-[0.18em] px-1.5 py-0.5 rounded-md
                             bg-aiu-red-50 text-aiu-red font-bold ring-1 ring-aiu-red/20">
                    Finalist
                </span>
            @endif
            @if ($allSubs->count() > 1)
                <p class="mt-2 text-[11px] text-aiu-ink-600 inline-flex items-center gap-1.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Updated for finals — showing latest. Round 1 also on file.
                </p>
            @endif
        </div>

        @if ($hasLinks)
            <div class="flex flex-wrap gap-2 sm:justify-end">
                @if ($sub->video_url)
                    <a href="{{ $sub->video_url }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg
                              bg-aiu-red-50 text-aiu-red ring-1 ring-aiu-red/20
                              text-[11px] font-semibold hover:bg-aiu-red hover:text-white transition">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        Watch demo
                    </a>
                @endif
                @if ($sub->slides_url)
                    <a href="{{ $sub->slides_url }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg chip-3d
                              text-[11px] font-semibold text-aiu-ink-700 hover:text-aiu-red transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14-7H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z"/>
                        </svg>
                        Slides
                    </a>
                @endif
                @if ($sub->repo_url)
                    <a href="{{ $sub->repo_url }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg chip-3d
                              text-[11px] font-semibold text-aiu-ink-700 hover:text-aiu-red transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        Code
                    </a>
                @endif
                @if ($sub->report_pdf_path)
                    <a href="{{ asset('storage/' . $sub->report_pdf_path) }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg chip-3d
                              text-[11px] font-semibold text-aiu-ink-700 hover:text-aiu-red transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Report
                    </a>
                @endif
            </div>
        @endif
    </div>

    {{-- Role coverage strip (visible to judges & mentors) --}}
    <div class="mb-5 p-4 rounded-2xl bg-aiu-ink-50/50 ring-1 ring-aiu-line">
        <div class="flex items-center justify-between gap-3 mb-3">
            <p class="text-[10px] uppercase tracking-[0.18em] text-aiu-ink-400 font-bold">Team Composition</p>
            @if (count($coverage['missing']) === 0)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 text-[10px] uppercase tracking-wider font-bold">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    All 3 roles
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-aiu-red-50 text-aiu-red ring-1 ring-aiu-red/20 text-[10px] uppercase tracking-wider font-bold">
                    Missing {{ count($coverage['missing']) }}
                </span>
            @endif
        </div>
        <div class="grid grid-cols-3 gap-2">
            @foreach (\App\Models\User::ROLE_CATEGORIES as $key => $label)
                @php
                    $people = $membersByRole->get($key, collect());
                    $filled = $people->isNotEmpty();
                @endphp
                <div class="rounded-xl px-3 py-2.5 ring-1
                            {{ $filled ? 'bg-white ring-aiu-line' : 'bg-aiu-red-50/30 ring-aiu-red/20 ring-dashed' }}">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        @if ($filled)
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <svg class="w-3.5 h-3.5 text-aiu-red" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        @endif
                        <p class="text-[11px] font-bold {{ $filled ? 'text-aiu-ink-900' : 'text-aiu-red' }}">{{ $label }}</p>
                    </div>
                    @if ($filled)
                        <p class="text-[11px] text-aiu-ink-600 leading-snug truncate">
                            {{ $people->pluck('user.name')->filter()->implode(', ') }}
                        </p>
                    @else
                        <p class="text-[11px] text-aiu-red/80 italic">— vacant —</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    @if ($hasContent || $hasAiDisclosure)
        <div class="space-y-5">
            @foreach ($sections as $key => $label)
                @php $body = optional($drafts->get($key))->body; @endphp
                @if (filled($body))
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-1.5">{{ $label }}</p>
                        <p class="text-sm text-aiu-ink-700 leading-relaxed whitespace-pre-wrap">{{ $body }}</p>
                    </div>
                @endif
            @endforeach

            @if ($hasAiDisclosure)
                <div>
                    <p class="text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-1.5">
                        AI Tools Disclosure (final submission)
                    </p>
                    <p class="text-sm text-aiu-ink-700 leading-relaxed whitespace-pre-wrap">{{ $sub->ai_disclosure_text }}</p>
                </div>
            @endif
        </div>
    @else
        <p class="text-sm text-aiu-ink-400 italic">This team hasn't filled in their workspace yet.</p>
    @endif
</div>
