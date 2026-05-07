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
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-10">
    @if (!$team)
        <div class="card-3d rounded-3xl p-12 text-center max-w-2xl mx-auto">
            <div class="logo-plate inline-flex items-center justify-center w-20 h-20 rounded-3xl mb-6 mx-auto">
                <svg class="w-9 h-9 text-aiu-red" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 000 7.75"/>
                </svg>
            </div>
            <h2 class="font-heading text-3xl font-bold text-aiu-ink-900">No team assigned yet</h2>
            <p class="mt-3 text-base text-aiu-ink-600">
                ACIE staff must add you to a team before you can access the workspace.
            </p>
        </div>
    @else
        @php
            $progressPct = $totalSections > 0 ? round(($sectionsFilled / $totalSections) * 100) : 0;
            $checklistDone = count(array_filter($checklist));
            $checklistTotal = count($requiredChecks);
            $reportDone = $sectionsFilled >= $totalSections - 2; // allow 2 unfilled
            $submissionDone = $allSubmissions->contains(fn ($s) => $s->status !== 'draft');

            $stepDefs = [
                'overview'     => ['label' => 'Overview',          'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                'assignments'  => ['label' => 'Assignments',       'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                'report'       => ['label' => 'Solution Report',   'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                'submission'   => ['label' => 'Submission',        'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                'discussion'   => ['label' => 'Discussion',        'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                'team'         => ['label' => 'Team & Applications','icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z'],
            ];

            $totalAssignments = $assignments->count();
            $assignmentsWithAnyFiles = $assignmentSummaries->filter(fn ($s) => $s->files_count > 0)->count();

            $stepStatus = [
                'overview'    => 'home',
                'assignments' => $totalAssignments === 0 ? 'todo'
                                  : ($assignmentsWithAnyFiles >= $totalAssignments ? 'done'
                                  : ($assignmentsWithAnyFiles > 0 ? 'progress' : 'todo')),
                'report'      => $reportDone ? 'done' : ($sectionsFilled > 0 ? 'progress' : 'todo'),
                'submission'  => $submissionDone ? 'done' : ($checklistDone > 0 ? 'progress' : 'todo'),
                'discussion'  => array_sum($channelCounts) > 0 ? 'progress' : 'todo',
                'team'        => $pendingApplications->isNotEmpty() ? 'progress' : 'todo',
            ];

            $totalChannelMessages = array_sum($channelCounts);
        @endphp

        {{-- ========== HERO ========== --}}
        <header class="card-3d rounded-3xl overflow-hidden mb-5 relative">
            @if ($team->banner_path)
                <div class="h-32 lg:h-44 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $team->banner_path) }}');">
                    <div class="absolute inset-0 bg-gradient-to-t from-white via-white/70 to-transparent"></div>
                </div>
            @else
                <div class="absolute inset-0 bg-gradient-to-br from-aiu-red-50/60 via-white to-aiu-gold-50/40 pointer-events-none"></div>
            @endif
            <div class="relative p-5 lg:p-7 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 {{ $team->banner_path ? '-mt-16 lg:-mt-20' : '' }}">
                <div class="flex items-center gap-4 flex-1 min-w-0">
                    @if ($team->logo_path)
                        <img src="{{ asset('storage/' . $team->logo_path) }}" alt="{{ $team->name }}"
                             class="shrink-0 w-16 h-16 lg:w-20 lg:h-20 rounded-2xl object-cover ring-4 ring-white shadow-md">
                    @else
                        <div class="shrink-0 w-16 h-16 lg:w-20 lg:h-20 rounded-2xl bg-gradient-to-br from-aiu-red to-aiu-gold text-white flex items-center justify-center font-heading font-bold text-2xl lg:text-3xl ring-4 ring-white shadow-md">
                            {{ mb_substr($team->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full chip-3d
                                  text-aiu-red uppercase tracking-[0.22em] text-[10px] font-bold mb-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-aiu-red animate-pulse"></span>
                            Team Workspace
                        </p>
                        <h1 class="font-heading text-2xl lg:text-4xl font-bold text-aiu-ink-900 leading-tight truncate">
                            {{ $team->name }}
                        </h1>
                        @if ($team->tagline)
                            <p class="mt-0.5 text-sm text-aiu-ink-600 italic truncate">{{ $team->tagline }}</p>
                        @endif
                        <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-aiu-ink-600">
                            @if ($team->theme)
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3 h-3 text-aiu-red" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span>{{ $team->theme->name }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-aiu-red font-semibold">No theme yet</span>
                            @endif
                            @if ($team->is_finalist)
                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md bg-aiu-gold-50 text-aiu-gold-600 ring-1 ring-aiu-gold/30 text-[10px] uppercase tracking-wider font-bold">
                                    ★ Finalist
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-1 max-w-md">
                    @foreach ($team->members as $m)
                        <a href="{{ route('users.show', $m->id) }}" target="_blank"
                           class="inline-flex items-center gap-1 pl-0.5 pr-1.5 py-0.5 rounded-full chip-3d text-[11px] text-aiu-ink-700 hover:text-aiu-red transition"
                           title="{{ $m->name }}">
                            <x-avatar :user="$m" size="xs" :leader="$m->id === $team->leader_id" />
                            <span class="truncate max-w-[6rem]">{{ $m->name }}</span>
                            @if ($m->id === $team->leader_id)<span class="text-aiu-red">★</span>@endif
                        </a>
                    @endforeach
                </div>
            </div>
        </header>

        {{-- ========== STEPPER ========== --}}
        <nav class="card-3d rounded-2xl p-2 mb-6 sticky top-2 z-10 bg-white/95 backdrop-blur-sm">
            <div class="flex items-center gap-1 overflow-x-auto">
                @foreach ($stepDefs as $key => $def)
                    @php
                        $isActive = $step === $key;
                        $status = $stepStatus[$key];
                        $statusColor = match ($status) {
                            'done' => 'bg-emerald-500 text-white',
                            'progress' => 'bg-aiu-gold-500 text-white',
                            'home' => 'bg-aiu-ink-100 text-aiu-ink-700',
                            default => 'bg-aiu-ink-100 text-aiu-ink-400',
                        };
                    @endphp
                    <button type="button" wire:click="goTo('{{ $key }}')"
                        class="flex-1 min-w-[120px] inline-flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition whitespace-nowrap
                               {{ $isActive ? 'bg-aiu-red text-white shadow-md' : 'text-aiu-ink-700 hover:bg-aiu-ink-50' }}">
                        <span class="shrink-0 inline-flex items-center justify-center w-6 h-6 rounded-lg
                                     {{ $isActive ? 'bg-white/20' : $statusColor }}">
                            @if ($status === 'done')
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $def['icon'] }}"/>
                                </svg>
                            @endif
                        </span>
                        <span>{{ $def['label'] }}</span>
                        @if ($key === 'discussion' && $totalChannelMessages > 0 && !$isActive)
                            <span class="text-[10px] tabular-nums px-1.5 py-0.5 rounded-full bg-aiu-red text-white">{{ $totalChannelMessages }}</span>
                        @endif
                        @if ($key === 'team' && $pendingApplications->count() > 0 && !$isActive)
                            <span class="text-[10px] tabular-nums px-1.5 py-0.5 rounded-full bg-aiu-red text-white">{{ $pendingApplications->count() }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </nav>

        {{-- ========== STEP CONTENT ========== --}}

        @if ($step === 'overview')
            {{-- ===================== OVERVIEW ===================== --}}
            <div class="space-y-6">
                {{-- Big progress card --}}
                <section class="card-3d rounded-3xl p-6 lg:p-7">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 mb-5">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-ink-400 font-bold mb-1">Solution Report Progress</p>
                            <p class="font-heading text-2xl lg:text-3xl font-bold text-aiu-ink-900">
                                <span class="text-aiu-red tabular-nums">{{ $sectionsFilled }}</span>
                                <span class="text-aiu-ink-400 text-xl">/</span>
                                <span class="tabular-nums">{{ $totalSections }}</span>
                                <span class="text-base font-medium text-aiu-ink-600 ml-2">sections drafted</span>
                            </p>
                            <p class="mt-1 text-sm text-aiu-ink-600">{{ number_format($totalWords) }} words written</p>
                        </div>
                        <button type="button" wire:click="goTo('report')"
                                class="btn-aiu px-5 py-2.5 rounded-xl font-bold text-sm inline-flex items-center justify-center gap-2 self-start lg:self-auto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            {{ $sectionsFilled > 0 ? 'Continue editing' : 'Start writing' }}
                        </button>
                    </div>
                    <div class="relative h-2.5 rounded-full bg-aiu-ink-100 overflow-hidden">
                        <div class="absolute inset-y-0 left-0 rounded-full bg-gradient-to-r from-aiu-red via-aiu-red to-aiu-gold transition-all duration-500"
                             style="width: {{ $progressPct }}%"></div>
                    </div>
                </section>

                {{-- Theme picker (compact) --}}
                <section class="card-3d rounded-3xl p-6 lg:p-7">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-ink-400 font-bold">Step 0 · Theme</p>
                            <h2 class="font-heading text-lg font-bold text-aiu-ink-900 mt-0.5">Pick your challenge area</h2>
                        </div>
                        @if ($isThemeLockOpen)
                            <span class="inline-flex items-center gap-1.5 text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 font-bold">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Lock-in OPEN
                            </span>
                        @elseif ($team->theme_id)
                            <span class="inline-flex items-center gap-1.5 text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-full bg-aiu-ink-50 text-aiu-ink-600 ring-1 ring-aiu-line font-bold">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Locked
                            </span>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                        @foreach ($themes as $theme)
                            @php
                                $isSelected = $themeId === $theme->id;
                                $disabled = !$isThemeLockOpen && $team->theme_id && !$isSelected;
                            @endphp
                            <button type="button" wire:click="pickTheme({{ $theme->id }})"
                                @if($disabled) disabled @endif
                                class="text-left p-3 rounded-xl transition relative
                                       {{ $isSelected
                                          ? 'bg-gradient-to-br from-aiu-red-50 to-aiu-gold-50 ring-2 ring-aiu-red/40'
                                          : ($disabled
                                              ? 'opacity-40 cursor-not-allowed card-3d'
                                              : 'card-3d card-3d-hover') }}">
                                @if ($isSelected)
                                    <span class="absolute top-1.5 right-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full bg-aiu-red text-white">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                @endif
                                <p class="font-heading font-bold text-xs text-aiu-ink-900 pr-5">{{ $theme->name }}</p>
                            </button>
                        @endforeach
                    </div>
                </section>

                {{-- Submissions index (the user's request) --}}
                <section>
                    <div class="flex items-end justify-between mb-3">
                        <div>
                            <h2 class="font-heading text-xl font-bold text-aiu-ink-900">Your Submissions</h2>
                            <p class="text-xs text-aiu-ink-600 mt-0.5">One entry per stage — Round 1 is required, Finals only if you advance.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($rounds as $roundKey => $roundLabel)
                            @php
                                $sub = $allSubmissions->get($roundKey);
                                $unlocked = $roundKey === 'round1' || $finalsAllowed;
                                $isSubmittedRound = $sub && $sub->status !== 'draft';
                                $subChecklist = $sub ? app(\App\Services\SubmissionService::class)->checklist($sub) : array_fill_keys(array_keys($requiredChecks), false);
                                $subDone = count(array_filter($subChecklist));
                                $deadline = $roundKey === 'finals' ? 'May 8 · 12:55' : 'May 7 · 15:45';
                            @endphp
                            <div class="card-3d rounded-2xl overflow-hidden flex flex-col {{ !$unlocked ? 'opacity-60' : '' }}">
                                <div class="p-5 border-b border-aiu-line {{ $isSubmittedRound ? 'bg-gradient-to-br from-emerald-50/40 to-white' : 'bg-gradient-to-br from-aiu-red-50/30 to-white' }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-[10px] uppercase tracking-[0.22em] {{ $roundKey === 'finals' ? 'text-aiu-gold-600' : 'text-aiu-red' }} font-bold mb-1">
                                                {{ $roundLabel }}
                                            </p>
                                            <h3 class="font-heading text-xl font-bold text-aiu-ink-900">{{ $roundLabel }} entry</h3>
                                            <p class="text-xs text-aiu-ink-600 mt-1 inline-flex items-center gap-1.5">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Deadline {{ $deadline }}
                                            </p>
                                        </div>

                                        @if (!$unlocked)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] uppercase tracking-wider font-bold bg-aiu-ink-100 text-aiu-ink-500">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                Finalists only
                                            </span>
                                        @elseif ($isSubmittedRound)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] uppercase tracking-wider font-bold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                {{ ucfirst($sub->status) }}
                                            </span>
                                        @elseif ($sub)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] uppercase tracking-wider font-bold bg-aiu-gold-50 text-aiu-gold-600 ring-1 ring-aiu-gold/30">
                                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-aiu-gold-500"></span>
                                                Draft
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] uppercase tracking-wider font-bold bg-aiu-ink-100 text-aiu-ink-500">
                                                Not started
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="p-5 flex-1 flex flex-col gap-3">
                                    {{-- Checklist mini --}}
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 rounded-full bg-aiu-ink-100 overflow-hidden">
                                            <div class="h-full rounded-full transition-all {{ $subDone === $checklistTotal ? 'bg-emerald-500' : 'bg-aiu-red/70' }}"
                                                 style="width: {{ $checklistTotal > 0 ? round(($subDone / $checklistTotal) * 100) : 0 }}%"></div>
                                        </div>
                                        <span class="text-xs tabular-nums text-aiu-ink-600 font-semibold whitespace-nowrap">{{ $subDone }}/{{ $checklistTotal }}</span>
                                    </div>

                                    {{-- Submitted timestamp --}}
                                    @if ($isSubmittedRound)
                                        <p class="text-[11px] text-aiu-ink-600">
                                            Submitted {{ $sub->submitted_at?->format('M j, H:i') }}
                                        </p>
                                    @endif

                                    {{-- Open button --}}
                                    <button type="button"
                                        @if (!$unlocked) disabled @endif
                                        wire:click="openSubmission('{{ $roundKey }}')"
                                        class="mt-auto w-full px-4 py-2.5 rounded-xl text-sm font-bold inline-flex items-center justify-center gap-2 transition
                                               {{ $unlocked ? 'btn-aiu' : 'bg-aiu-ink-100 text-aiu-ink-400 cursor-not-allowed' }}">
                                        @if ($unlocked)
                                            {{ $isSubmittedRound ? 'View / update' : ($sub ? 'Continue draft' : 'Start submission') }}
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                        @else
                                            Locked until finals
                                        @endif
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- Team identity (leader only) --}}
                @if ($isLeader)
                    <section class="card-3d rounded-3xl p-6 lg:p-7" wire:ignore.self>
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-ink-400 font-bold">Team Identity</p>
                                <h2 class="font-heading text-lg font-bold text-aiu-ink-900 mt-0.5">Logo, banner & tagline</h2>
                                <p class="text-xs text-aiu-ink-600 mt-1">Shown on the public leaderboard and team page.</p>
                            </div>
                            @if (session('asset_status'))
                                <span class="text-[10px] uppercase tracking-wider px-2 py-1 rounded-md bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 font-bold inline-flex items-center gap-1"
                                      x-data x-init="setTimeout(() => $el.style.display = 'none', 3000)">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    {{ session('asset_status') }}
                                </span>
                            @elseif ($identitySaved)
                                <span class="text-[10px] uppercase tracking-wider px-2 py-1 rounded-md bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 font-bold inline-flex items-center gap-1"
                                      x-data x-init="setTimeout(() => $el.style.display = 'none', 3000)">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Saved
                                </span>
                            @endif
                        </div>

                        {{-- Tagline saves through Livewire (no file upload) --}}
                        <form wire:submit="saveTeamIdentity" class="space-y-4 mb-6">
                            <div>
                                <label class="block text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-1.5">Tagline (one-liner)</label>
                                <input type="text" wire:model="tagline" maxlength="160"
                                       placeholder="e.g. Adaptive traffic-signal control via deep RL"
                                       class="input-3d w-full px-3 py-2.5 rounded-lg text-sm">
                                @error('tagline') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="btn-aiu px-5 py-2.5 rounded-xl text-sm font-bold">Save tagline</button>
                            </div>
                        </form>

                        {{--
                            Logo & banner upload bypass Livewire entirely — they POST as
                            traditional multipart forms to a controller. This dodges the
                            Windows + `php artisan serve` quirk where a PHP startup
                            Notice on the request leaks into Livewire's JSON response.
                        --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-aiu-line">
                            <div class="space-y-2">
                                <label class="block text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold">Logo (square, ≤ 1MB)</label>
                                <form action="{{ route('workspace.logo.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                                    @csrf
                                    <div class="flex items-center gap-3">
                                        @if ($team->logo_path)
                                            <img src="{{ asset('storage/' . $team->logo_path) }}" class="w-14 h-14 rounded-xl object-cover ring-2 ring-aiu-line">
                                        @else
                                            <div class="w-14 h-14 rounded-xl bg-aiu-ink-100 text-aiu-ink-400 flex items-center justify-center text-xs">No logo</div>
                                        @endif
                                        <input type="file" name="logo" accept="image/*" required
                                               class="flex-1 text-xs file:mr-2 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-aiu-ink-100 file:text-aiu-ink-700 file:font-semibold file:cursor-pointer hover:file:bg-aiu-red hover:file:text-white">
                                    </div>
                                    @error('logo') <p class="text-xs text-aiu-red">{{ $message }}</p> @enderror
                                    <div class="flex justify-end">
                                        <button type="submit" class="btn-aiu px-3 py-1.5 rounded-lg text-xs font-semibold">
                                            {{ $team->logo_path ? 'Replace logo' : 'Upload logo' }}
                                        </button>
                                    </div>
                                </form>
                                @if ($team->logo_path)
                                    <form action="{{ route('workspace.logo.delete') }}" method="POST" class="text-right"
                                          onsubmit="return confirm('Remove the team logo?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-[11px] text-aiu-ink-500 hover:text-aiu-red">Remove logo</button>
                                    </form>
                                @endif
                            </div>

                            <div class="space-y-2">
                                <label class="block text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold">Banner (wide, ≤ 3MB)</label>
                                <form action="{{ route('workspace.banner.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                                    @csrf
                                    <div class="flex items-center gap-3">
                                        @if ($team->banner_path)
                                            <img src="{{ asset('storage/' . $team->banner_path) }}" class="w-24 h-14 rounded-lg object-cover ring-2 ring-aiu-line">
                                        @else
                                            <div class="w-24 h-14 rounded-lg bg-aiu-ink-100 text-aiu-ink-400 flex items-center justify-center text-xs">No banner</div>
                                        @endif
                                        <input type="file" name="banner" accept="image/*" required
                                               class="flex-1 text-xs file:mr-2 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-aiu-ink-100 file:text-aiu-ink-700 file:font-semibold file:cursor-pointer hover:file:bg-aiu-red hover:file:text-white">
                                    </div>
                                    @error('banner') <p class="text-xs text-aiu-red">{{ $message }}</p> @enderror
                                    <div class="flex justify-end">
                                        <button type="submit" class="btn-aiu px-3 py-1.5 rounded-lg text-xs font-semibold">
                                            {{ $team->banner_path ? 'Replace banner' : 'Upload banner' }}
                                        </button>
                                    </div>
                                </form>
                                @if ($team->banner_path)
                                    <form action="{{ route('workspace.banner.delete') }}" method="POST" class="text-right"
                                          onsubmit="return confirm('Remove the team banner?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-[11px] text-aiu-ink-500 hover:text-aiu-red">Remove banner</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </section>
                @endif

                {{-- Quick actions --}}
                <section class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <button type="button" wire:click="goTo('report')"
                            class="card-3d card-3d-hover rounded-2xl p-4 text-left flex items-center gap-3 group">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-aiu-red text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </span>
                        <div>
                            <p class="font-heading font-bold text-aiu-ink-900">Edit Solution Report</p>
                            <p class="text-[11px] text-aiu-ink-600">{{ $sectionsFilled }}/{{ $totalSections }} sections</p>
                        </div>
                    </button>
                    <button type="button" wire:click="goTo('discussion')"
                            class="card-3d card-3d-hover rounded-2xl p-4 text-left flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-600 text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </span>
                        <div>
                            <p class="font-heading font-bold text-aiu-ink-900">Discussion</p>
                            <p class="text-[11px] text-aiu-ink-600">{{ $totalChannelMessages }} messages across 3 channels</p>
                        </div>
                    </button>
                    <a href="{{ route('teams.show', $team->slug) }}" target="_blank"
                            class="card-3d card-3d-hover rounded-2xl p-4 text-left flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-aiu-ink-100 text-aiu-ink-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 3h7v7M21 3l-9 9M21 14v5a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h5"/></svg>
                        </span>
                        <div>
                            <p class="font-heading font-bold text-aiu-ink-900">Public preview</p>
                            <p class="text-[11px] text-aiu-ink-600">How judges & voters see your team</p>
                        </div>
                    </a>
                </section>
            </div>

        @elseif ($step === 'assignments')
            {{-- ===================== STEP · ASSIGNMENTS ===================== --}}
            @include('livewire.partials.team-workspace-assignments', [
                'team' => $team,
                'assignments' => $assignments,
                'activeAssignment' => $activeAssignment,
                'activeAssignmentSubmission' => $activeAssignmentSubmission,
                'assignmentSummaries' => $assignmentSummaries,
            ])

        @elseif ($step === 'report')
            {{-- ===================== STEP 1 · SOLUTION REPORT ===================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <aside class="lg:col-span-3">
                    <div class="card-3d rounded-2xl p-3 lg:sticky lg:top-20">
                        <div class="px-3 pt-3 pb-2">
                            <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-red font-bold">Step 1</p>
                            <p class="font-heading text-base font-bold text-aiu-ink-900">Solution Report</p>
                            <p class="text-[11px] text-aiu-ink-600 mt-0.5">{{ $sectionsFilled }}/{{ $totalSections }} sections · auto-saved</p>
                        </div>
                        <nav class="flex flex-col gap-0.5 mt-2">
                            @foreach ($sections as $key => $label)
                                @php
                                    $meta = $sectionMeta[$key] ?? [];
                                    $words = $wordCounts[$key] ?? 0;
                                    $rec = $meta['recommended'] ?? 200;
                                    $isActive = $activeSection === $key;
                                    $done = $words >= 20;
                                    $progress = min(100, $rec > 0 ? round(($words / $rec) * 100) : 0);
                                @endphp
                                <button type="button" wire:click="setSection('{{ $key }}')"
                                    class="group flex items-start gap-3 text-left px-3 py-2.5 rounded-xl transition
                                           {{ $isActive ? 'bg-aiu-red-50 ring-1 ring-aiu-red/20' : 'hover:bg-aiu-ink-50' }}">
                                    <span class="shrink-0 mt-0.5 inline-flex items-center justify-center w-7 h-7 rounded-lg
                                                 {{ $isActive ? 'bg-aiu-red text-white' : ($done ? 'bg-emerald-100 text-emerald-700' : 'bg-aiu-ink-100 text-aiu-ink-400') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath($meta['icon'] ?? '') }}"/>
                                        </svg>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-semibold leading-tight {{ $isActive ? 'text-aiu-red' : 'text-aiu-ink-900' }}">
                                            {{ $label }}
                                        </span>
                                        <span class="mt-1 flex items-center gap-1.5 text-[10px] text-aiu-ink-400">
                                            <span class="tabular-nums">{{ $words }}/{{ $rec }}</span>
                                            <span class="flex-1 h-1 rounded-full bg-aiu-ink-100 overflow-hidden">
                                                <span class="block h-full rounded-full {{ $done ? 'bg-emerald-500' : 'bg-aiu-red/60' }}" style="width: {{ $progress }}%"></span>
                                            </span>
                                        </span>
                                    </span>
                                </button>
                            @endforeach
                        </nav>
                    </div>
                </aside>

                <section class="lg:col-span-9 space-y-6">
                    @php
                        $meta = $sectionMeta[$activeSection] ?? [];
                        $currentWords = $wordCounts[$activeSection] ?? 0;
                        $rec = $meta['recommended'] ?? 200;
                        $hint = $meta['hint'] ?? '';
                        $placeholder = $meta['placeholder'] ?? 'Write your draft for this section…';
                        $iconName = $meta['icon'] ?? '';
                        $sectionPct = min(100, $rec > 0 ? round(($currentWords / $rec) * 100) : 0);
                        $savedAtIso = $savedAt[$activeSection] ?? null;
                    @endphp

                    <div class="card-3d rounded-3xl overflow-hidden"
                         x-data="{
                            savedAt: @js($savedAtIso),
                            savedLabel: 'Auto-saves while you type',
                            tick() {
                                if (!this.savedAt) return;
                                const diff = Math.max(0, Math.floor((Date.now() - new Date(this.savedAt).getTime())/1000));
                                this.savedLabel = diff < 5 ? 'Saved just now' : (diff < 60 ? `Saved ${diff}s ago` : `Saved ${Math.floor(diff/60)}m ago`);
                            }
                         }"
                         x-init="tick(); setInterval(()=>tick(), 2000)"
                         wire:key="editor-{{ $activeSection }}">
                        <div class="flex items-start justify-between gap-4 p-6 lg:p-7 border-b border-aiu-line bg-gradient-to-br from-white to-aiu-ink-50/40">
                            <div class="flex items-start gap-4 min-w-0">
                                <span class="shrink-0 inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-aiu-red text-white">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath($iconName) }}"/>
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-ink-400 font-bold mb-1">Section</p>
                                    <h2 class="font-heading text-2xl font-bold text-aiu-ink-900 leading-tight">{{ $sections[$activeSection] }}</h2>
                                    @if ($hint)
                                        <p class="mt-2 text-sm text-aiu-ink-600 leading-relaxed">{{ $hint }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="p-6 lg:p-7">
                            <textarea wire:model.live.debounce.800ms="drafts.{{ $activeSection }}"
                                      rows="14"
                                      placeholder="{{ $placeholder }}"
                                      class="input-3d w-full px-5 py-4 rounded-2xl text-aiu-ink-900 placeholder-aiu-ink-400 font-mono text-sm leading-relaxed resize-y"></textarea>

                            <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-2 text-xs text-aiu-ink-600">
                                        <span class="tabular-nums font-bold {{ $currentWords >= $rec ? 'text-emerald-600' : 'text-aiu-ink-900' }}">{{ $currentWords }}</span>
                                        <span class="text-aiu-ink-400">/ {{ $rec }} words recommended</span>
                                    </div>
                                    <div class="w-32 h-1.5 rounded-full bg-aiu-ink-100 overflow-hidden">
                                        <div class="h-full rounded-full {{ $currentWords >= $rec ? 'bg-emerald-500' : 'bg-gradient-to-r from-aiu-red to-aiu-gold' }}"
                                             style="width: {{ $sectionPct }}%"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <span wire:loading.remove wire:target="drafts.{{ $activeSection }},saveDraft"
                                          class="inline-flex items-center gap-1.5 text-emerald-600 font-semibold">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span x-text="savedLabel">Auto-saves while you type</span>
                                    </span>
                                    <span wire:loading wire:target="drafts.{{ $activeSection }},saveDraft"
                                          class="inline-flex items-center gap-1.5 text-aiu-gold-600 font-semibold">
                                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                                        Saving…
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Wizard navigation footer --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-4 rounded-2xl card-3d">
                        <button type="button" wire:click="goTo('overview')" class="btn-soft px-5 py-2.5 rounded-xl text-sm font-semibold inline-flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            Back to overview
                        </button>
                        <p class="text-xs text-aiu-ink-600 self-center">Drafts auto-save · move on whenever you're ready</p>
                        <button type="button" wire:click="goTo('submission')" class="btn-aiu px-5 py-2.5 rounded-xl text-sm font-bold inline-flex items-center justify-center gap-2">
                            Continue to Submission
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </section>
            </div>

        @elseif ($step === 'submission')
            {{-- ===================== STEP 2 · SUBMISSION ===================== --}}
            <div class="space-y-6">
                <div class="card-3d rounded-3xl overflow-hidden">
                    <div class="p-6 lg:p-7 border-b border-aiu-line bg-gradient-to-br from-aiu-red-50/40 via-white to-aiu-gold-50/30">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <span class="shrink-0 inline-flex items-center justify-center w-12 h-12 rounded-2xl btn-aiu">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-red font-bold mb-1">Step 2</p>
                                    <h2 class="font-heading text-2xl font-bold text-aiu-ink-900">{{ $rounds[$activeRound] ?? 'Round 1' }} entry</h2>
                                    <p class="text-xs text-aiu-ink-600 mt-1">
                                        @if ($isSubmitted)
                                            <span class="text-emerald-600 font-bold">✓ Submitted</span> on {{ $submission->submitted_at?->format('Y-m-d H:i') }}
                                        @elseif ($isSubmissionWindowOpen)
                                            <span class="text-emerald-600 font-bold">Window open</span> ·
                                            @if ($activeRound === 'finals') deadline 12:55 (May 8) @else deadline 15:45 (May 7) @endif
                                        @else
                                            @if ($activeRound === 'finals')
                                                Opens after finalist announcement (12:15, May 8)
                                            @else
                                                Opens on May 7 at 15:30
                                            @endif
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @if ($isSubmitted)
                                <span class="self-start sm:self-center px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 text-[10px] uppercase tracking-wider font-bold whitespace-nowrap">
                                    {{ ucfirst($submission->status) }}
                                </span>
                            @endif
                        </div>

                        {{-- Round tabs --}}
                        <div class="mt-5 flex gap-1 p-1 rounded-xl bg-white/60 ring-1 ring-aiu-line">
                            @foreach ($rounds as $roundKey => $roundLabel)
                                @php
                                    $isActive = $activeRound === $roundKey;
                                    $roundSub = $allSubmissions->get($roundKey);
                                    $isLockedFinals = $roundKey === 'finals' && !$finalsAllowed;
                                    $tabState = match (true) {
                                        $isLockedFinals => 'locked',
                                        $roundSub && $roundSub->status !== 'draft' => 'submitted',
                                        $roundSub => 'draft',
                                        default => 'empty',
                                    };
                                @endphp
                                <button type="button"
                                    @if ($isLockedFinals) disabled title="Only finalist teams may submit for the Finals round" @endif
                                    wire:click="setRound('{{ $roundKey }}')"
                                    class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-xs sm:text-sm font-semibold transition
                                           {{ $isActive ? 'bg-aiu-red text-white shadow-sm' : ($isLockedFinals ? 'opacity-40 cursor-not-allowed text-aiu-ink-500' : 'text-aiu-ink-700 hover:bg-aiu-ink-50') }}">
                                    @if ($tabState === 'locked')
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    @elseif ($tabState === 'submitted')
                                        <svg class="w-3.5 h-3.5 {{ $isActive ? 'text-white' : 'text-emerald-600' }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    @elseif ($tabState === 'draft')
                                        <span class="inline-block w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-white' : 'bg-aiu-gold-500' }}"></span>
                                    @endif
                                    <span>{{ $roundLabel }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-6 lg:p-7 space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                            @foreach ($requiredChecks as $key => $label)
                                @php $ok = $checklist[$key] ?? false; @endphp
                                <div class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl
                                            {{ $ok ? 'bg-emerald-50 ring-1 ring-emerald-200 text-emerald-700' : 'bg-aiu-ink-50/60 ring-1 ring-aiu-line text-aiu-ink-600' }}">
                                    <span class="shrink-0 inline-flex items-center justify-center w-5 h-5 rounded-full {{ $ok ? 'bg-emerald-500 text-white' : 'bg-white ring-1 ring-aiu-line text-aiu-ink-400' }}">
                                        @if ($ok)
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/></svg>
                                        @endif
                                    </span>
                                    <span class="text-xs font-semibold">{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>

                        <fieldset @if($isSubmitted) disabled @endif class="space-y-5 {{ $isSubmitted ? 'opacity-60' : '' }}">
                            <div>
                                <label class="block text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">
                                    Solution Report (PDF, max 5 pages)
                                </label>
                                @if ($submission?->report_pdf_path)
                                    <p class="text-xs text-emerald-600 mb-2 font-semibold inline-flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        Uploaded: {{ basename($submission->report_pdf_path) }}
                                    </p>
                                @endif
                                <div class="flex items-center gap-3 p-3 rounded-xl border-2 border-dashed border-aiu-line hover:border-aiu-red/40 transition bg-aiu-ink-50/30">
                                    <svg class="w-7 h-7 text-aiu-ink-400 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <input type="file" wire:model="reportPdf" accept=".pdf"
                                           class="flex-1 text-sm text-aiu-ink-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-aiu-ink-200 file:text-aiu-ink-700 file:cursor-pointer file:font-semibold hover:file:bg-aiu-ink-300 file:text-xs">
                                </div>
                                @error('reportPdf') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">Slide Deck URL</label>
                                    <input type="url" wire:model="slidesUrl" placeholder="https://..." class="input-3d w-full px-3 py-2.5 rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">Code Repository</label>
                                    <input type="url" wire:model="repoUrl" placeholder="https://github.com/..." class="input-3d w-full px-3 py-2.5 rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">Demo Video URL</label>
                                    <input type="url" wire:model="videoUrl" placeholder="https://youtube.com/..." class="input-3d w-full px-3 py-2.5 rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">AI Tools Disclosure</label>
                                    <input type="text" wire:model="aiDisclosure" placeholder="ChatGPT for ideation, Copilot for code..." class="input-3d w-full px-3 py-2.5 rounded-lg text-sm">
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                                <button type="button" wire:click="saveSubmissionDraft" class="btn-soft px-4 py-2.5 rounded-lg font-semibold text-sm">
                                    Save draft
                                </button>
                                @if ($isLeader)
                                    <button type="button" wire:click="submitFinal"
                                            wire:confirm="This will lock your {{ $rounds[$activeRound] ?? '' }} submission. Continue?"
                                            @disabled(!$isSubmissionWindowOpen || $isSubmitted)
                                            class="btn-aiu px-5 py-2.5 rounded-lg font-bold uppercase tracking-wider text-xs inline-flex items-center justify-center gap-2">
                                        @if (!$isSubmitted)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                        {{ $isSubmitted ? 'Submitted' : 'Submit ' . ($rounds[$activeRound] ?? 'entry') }}
                                    </button>
                                @else
                                    <span class="text-xs text-aiu-ink-400 italic px-2">Only the team leader may submit.</span>
                                @endif
                            </div>
                            @if ($submitError)
                                <p class="text-xs text-aiu-red mt-2 px-3 py-2 rounded-lg bg-aiu-red-50 ring-1 ring-aiu-red/20">{{ $submitError }}</p>
                            @endif
                        </fieldset>
                    </div>
                </div>

                {{-- Wizard navigation footer --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-4 rounded-2xl card-3d">
                    <button type="button" wire:click="goTo('report')" class="btn-soft px-5 py-2.5 rounded-xl text-sm font-semibold inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        Back to Solution Report
                    </button>
                    <button type="button" wire:click="goTo('overview')" class="btn-soft px-5 py-2.5 rounded-xl text-sm font-semibold inline-flex items-center justify-center gap-2">
                        Back to Overview
                    </button>
                    <button type="button" wire:click="goTo('discussion')" class="btn-aiu px-5 py-2.5 rounded-xl text-sm font-bold inline-flex items-center justify-center gap-2">
                        Open Discussion
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

        @elseif ($step === 'discussion')
            {{-- ===================== DISCUSSION ===================== --}}
            @php
                $channelMeta = [
                    'team'   => ['label' => 'Internal',  'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 000 7.75', 'desc' => 'Visible to your team only'],
                    'mentor' => ['label' => 'Mentor',    'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z', 'desc' => 'Visible to your assigned mentor'],
                    'judge'  => ['label' => 'Judges',    'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'desc' => 'Visible to judges scoring your team'],
                ];
            @endphp
            <div class="card-3d rounded-3xl overflow-hidden">
                <div class="p-6 lg:p-7 border-b border-aiu-line">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-aiu-ink-100 text-aiu-ink-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </span>
                        <div>
                            <h2 class="font-heading text-xl font-bold text-aiu-ink-900">Discussion</h2>
                            <p class="text-xs text-aiu-ink-600">Pick a channel — each thread is visible only to its participants.</p>
                        </div>
                    </div>

                    <div class="flex gap-1 p-1 rounded-xl bg-aiu-ink-50/60 ring-1 ring-aiu-line">
                        @foreach ($channels as $key => $label)
                            @php
                                $isActive = $activeChannel === $key;
                                $count = $channelCounts[$key] ?? 0;
                                $meta = $channelMeta[$key];
                            @endphp
                            <button type="button" wire:click="setChannel('{{ $key }}')"
                                class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-xs sm:text-sm font-semibold transition
                                       {{ $isActive ? 'bg-white shadow-sm text-aiu-red ring-1 ring-aiu-red/20' : 'text-aiu-ink-600 hover:text-aiu-ink-900' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $meta['icon'] }}"/>
                                </svg>
                                <span>{{ $meta['label'] }}</span>
                                @if ($count > 0)
                                    <span class="text-[10px] tabular-nums px-1.5 py-0.5 rounded-full {{ $isActive ? 'bg-aiu-red text-white' : 'bg-aiu-ink-200 text-aiu-ink-600' }}">{{ $count }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    <p class="mt-2 text-[11px] text-aiu-ink-400">{{ $channelMeta[$activeChannel]['desc'] }}</p>
                </div>

                <div class="p-6 lg:p-7">
                    <form wire:submit="postComment" class="flex gap-2 mb-5">
                        <input type="text" wire:model="newComment" wire:key="msg-{{ $activeChannel }}"
                               placeholder="@if ($activeChannel === 'mentor')Ask your mentor a question…@elseif ($activeChannel === 'judge')Send a clarification to the judges…@else{{ 'Share an update with your team…' }}@endif"
                               class="input-3d flex-1 px-4 py-2.5 rounded-lg text-sm">
                        <button type="submit" class="btn-aiu px-5 py-2.5 rounded-lg font-bold text-sm inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Send
                        </button>
                    </form>
                    <div class="space-y-2.5 max-h-[60vh] overflow-y-auto pr-1" wire:key="thread-{{ $activeChannel }}">
                        @forelse ($comments as $c)
                            @php
                                $initials = collect(explode(' ', $c->user->name ?? '?'))->take(2)->map(fn($p)=>mb_substr($p,0,1))->implode('');
                                $authorRole = $c->user?->roles?->first()?->name ?? 'team_member';
                                $isMine = $c->user_id === auth()->id();
                                $roleBadge = match (true) {
                                    in_array($authorRole, ['judge']) => ['Judge', 'bg-aiu-red text-white'],
                                    in_array($authorRole, ['mentor']) => ['Mentor', 'bg-emerald-600 text-white'],
                                    in_array($authorRole, ['super_admin']) => ['Admin', 'bg-aiu-ink-900 text-white'],
                                    in_array($authorRole, ['team_leader']) => ['Leader', 'bg-aiu-gold-500 text-white'],
                                    default => ['Member', 'bg-aiu-ink-200 text-aiu-ink-700'],
                                };
                            @endphp
                            <div class="flex items-start gap-3 p-3 rounded-xl {{ $isMine ? 'bg-aiu-red-50/50 ring-1 ring-aiu-red/10' : 'surface-soft' }}">
                                <span class="shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-full bg-aiu-red text-white text-[11px] font-bold uppercase">
                                    {{ $initials }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                                        <span class="font-bold text-aiu-ink-900">{{ $c->user->name ?? 'Unknown' }}</span>
                                        <span class="text-[9px] uppercase tracking-wider px-1.5 py-0.5 rounded-md {{ $roleBadge[1] }} font-bold">{{ $roleBadge[0] }}</span>
                                        <span class="text-aiu-ink-400 ml-auto">{{ $c->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-1 text-sm text-aiu-ink-700 whitespace-pre-wrap leading-relaxed">{{ $c->body }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10">
                                <svg class="w-10 h-10 mx-auto text-aiu-ink-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <p class="text-sm text-aiu-ink-400 italic">No messages in this channel yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        @elseif ($step === 'team')
            {{-- ===================== TEAM & APPLICATIONS ===================== --}}
            <div class="space-y-6">
                {{-- Recruiting controls (leader only) --}}
                @if ($isLeader)
                    <section class="card-3d rounded-3xl p-6 lg:p-7">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-ink-400 font-bold">Recruiting</p>
                                <h2 class="font-heading text-xl font-bold text-aiu-ink-900 mt-0.5">Find more members</h2>
                                <p class="text-xs text-aiu-ink-600 mt-1">When recruiting is on, your team appears in the public Community → Recruiting Teams list.</p>
                            </div>
                            <a href="{{ route('community.teams') }}" target="_blank" class="text-xs text-aiu-red font-semibold hover:underline shrink-0">View public list →</a>
                        </div>

                        <div class="surface-soft rounded-xl p-4 mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-sm text-aiu-ink-900">Open for applications</p>
                                <p class="text-xs text-aiu-ink-600">Toggle off to hide your team from the recruitment list.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="isRecruiting" class="sr-only peer">
                                <div class="w-12 h-6 bg-aiu-ink-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow peer-checked:bg-aiu-red"></div>
                            </label>
                        </div>
                        @error('isRecruiting')
                            <div class="mb-3 px-3 py-2 rounded-lg bg-aiu-red-50 text-aiu-red ring-1 ring-aiu-red/20 text-xs">{{ $message }}</div>
                        @enderror

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-aiu-ink-700 mb-1.5">Recruitment message</label>
                                <textarea wire:model="recruitmentMessage" rows="3" maxlength="1000"
                                          placeholder="Describe your team and what kind of teammate you're looking for…"
                                          class="input-3d w-full px-3 py-2.5 rounded-lg text-sm leading-relaxed"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-aiu-ink-700 mb-1.5">Skills you need</label>
                                <input type="text" wire:model="lookingForSkills" maxlength="200"
                                       placeholder="e.g. React, ML, UI/UX, Pitching"
                                       class="input-3d w-full px-3 py-2.5 rounded-lg text-sm">
                            </div>
                            <div class="flex items-center justify-between">
                                @if ($recruitingSaved)
                                    <p class="text-xs text-emerald-600 font-semibold">{{ $recruitingSaved }}</p>
                                @else
                                    <span></span>
                                @endif
                                <button wire:click="saveRecruiting" type="button" class="btn-aiu px-5 py-2 rounded-lg text-sm font-semibold">
                                    Save recruiting settings
                                </button>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- Current members --}}
                <section class="card-3d rounded-3xl p-6 lg:p-7">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-ink-400 font-bold">Members</p>
                            <h2 class="font-heading text-xl font-bold text-aiu-ink-900 mt-0.5">{{ $team->members->count() }} {{ $team->members->count() === 1 ? 'member' : 'members' }}</h2>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($team->members as $m)
                            @php $isMe = $m->id === auth()->id(); $isTeamLeader = $m->id === $team->leader_id; @endphp
                            <div class="surface-soft rounded-xl p-3 flex items-center gap-3 hover:ring-1 hover:ring-aiu-red/30 transition">
                                <a href="{{ route('users.show', $m->id) }}" target="_blank" class="shrink-0">
                                    <x-avatar :user="$m" size="md" :leader="$isTeamLeader" />
                                </a>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('users.show', $m->id) }}" target="_blank" class="block">
                                        <p class="font-semibold text-sm text-aiu-ink-900 truncate hover:text-aiu-red transition">
                                            {{ $m->name }}
                                            @if ($isTeamLeader)
                                                <span class="text-aiu-red text-xs">★ Leader</span>
                                            @endif
                                            @if ($isMe)
                                                <span class="text-aiu-ink-400 text-[10px]">(you)</span>
                                            @endif
                                        </p>
                                    </a>
                                    @if ($m->pivot->role_in_team)
                                        <p class="text-xs text-aiu-ink-600 truncate">{{ $m->pivot->role_in_team }}</p>
                                    @endif
                                    <p class="text-[10px] text-aiu-ink-400 mt-0.5">Joined {{ \Carbon\Carbon::parse($m->pivot->created_at)->diffForHumans() }}</p>
                                </div>
                                @if ($isLeader && !$isTeamLeader)
                                    <div class="shrink-0 relative" x-data="{ open: false }">
                                        <button type="button" @click="open = !open"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-aiu-ink-400 hover:text-aiu-red hover:bg-aiu-red-50 transition">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z"/></svg>
                                        </button>
                                        <div x-show="open" @click.outside="open = false" x-transition.opacity
                                             class="absolute right-0 top-9 z-20 rounded-xl p-1.5 min-w-[200px] bg-white shadow-xl ring-1 ring-aiu-line" style="display: none">
                                            <button type="button"
                                                    wire:click="transferLeadership({{ $m->id }})"
                                                    wire:confirm="Make {{ $m->name }} the new team leader? You will become a regular member."
                                                    @click="open = false"
                                                    class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-aiu-ink-700 hover:bg-aiu-gold-50 hover:text-aiu-gold-700 transition text-left">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.366-2.445a1 1 0 00-1.176 0l-3.367 2.445c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                                                Make team leader
                                            </button>
                                            <button type="button"
                                                    wire:click="kickMember({{ $m->id }})"
                                                    wire:confirm="Remove {{ $m->name }} from the team? This cannot be undone."
                                                    @click="open = false"
                                                    class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-aiu-red hover:bg-aiu-red-50 transition text-left">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22"/></svg>
                                                Remove from team
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @unless ($isLeader)
                        <div class="mt-5 pt-5 border-t border-aiu-line flex items-center justify-between gap-3">
                            <p class="text-xs text-aiu-ink-500">Want to step away from this team? You can rejoin or apply to another later.</p>
                            <button type="button"
                                    wire:click="leaveTeam"
                                    wire:confirm="Leave {{ $team->name }}? You'll be removed from the team and can apply to others."
                                    class="btn-soft px-4 py-2 rounded-lg text-xs font-bold text-aiu-red hover:bg-aiu-red-50 ring-1 ring-aiu-red/20 inline-flex items-center gap-1.5 shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Leave team
                            </button>
                        </div>
                    @endunless

                    @if ($isLeader && $team->members->count() === 1)
                        <div class="mt-5 pt-5 border-t border-aiu-red/20 flex items-center justify-between gap-3 bg-aiu-red-50/40 -mx-6 -mb-6 lg:-mx-7 lg:-mb-7 px-6 py-4 lg:px-7 rounded-b-3xl">
                            <div>
                                <p class="font-semibold text-sm text-aiu-red">Disband this team</p>
                                <p class="text-xs text-aiu-ink-600 mt-0.5">You're the only member. Disbanding withdraws the team and rejects any pending applications.</p>
                            </div>
                            <button type="button"
                                    wire:click="disbandTeam"
                                    wire:confirm="Disband {{ $team->name }}? This withdraws the team from the hackathon. You can register a new team after."
                                    class="btn-soft px-4 py-2 rounded-lg text-xs font-bold text-aiu-red ring-1 ring-aiu-red/30 bg-white hover:bg-aiu-red hover:text-white inline-flex items-center gap-1.5 shrink-0 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22"/></svg>
                                Disband team
                            </button>
                        </div>
                    @endif
                </section>

                {{-- Pending applications (visible to all members, decisions leader-only) --}}
                <section class="card-3d rounded-3xl p-6 lg:p-7">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-ink-400 font-bold">Inbox</p>
                            <h2 class="font-heading text-xl font-bold text-aiu-ink-900 mt-0.5">
                                Pending applications
                                @if ($pendingApplications->isNotEmpty())
                                    <span class="ml-2 inline-flex items-center justify-center min-w-[1.5rem] h-6 px-2 rounded-full bg-aiu-red text-white text-xs">{{ $pendingApplications->count() }}</span>
                                @endif
                            </h2>
                            @unless ($isLeader)
                                <p class="text-xs text-aiu-ink-500 mt-1">All members can see applicants. Only the team leader can accept or reject.</p>
                            @endunless
                        </div>
                    </div>

                    @if ($applicationError)
                        <div class="mb-4 px-4 py-2 rounded-lg bg-aiu-red-50 text-aiu-red ring-1 ring-aiu-red/20 text-sm">
                            {{ $applicationError }}
                        </div>
                    @endif

                        @if ($pendingApplications->isEmpty())
                            <div class="surface-soft rounded-xl p-6 text-center text-sm text-aiu-ink-500">
                                No pending applications. Applications will appear here when people request to join your team.
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach ($pendingApplications as $app)
                                    <div class="surface-soft rounded-xl p-4 lg:p-5">
                                        <div class="flex items-start gap-3">
                                            <a href="{{ route('users.show', $app->user_id) }}" target="_blank" class="shrink-0">
                                                <x-avatar :user="$app->user" size="md" />
                                            </a>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-3 mb-1">
                                                    <div>
                                                        <a href="{{ route('users.show', $app->user_id) }}" target="_blank" class="font-semibold text-sm text-aiu-ink-900 hover:text-aiu-red">
                                                            {{ $app->user->name }}
                                                        </a>
                                                        <p class="text-[11px] text-aiu-ink-500">
                                                            {{ $app->user->email }}
                                                            @if ($app->user->institution)
                                                                <span class="text-aiu-ink-400">·</span> {{ $app->user->institution }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <span class="text-[11px] text-aiu-ink-400 shrink-0">{{ $app->created_at->diffForHumans() }}</span>
                                                </div>

                                                @if ($app->skills)
                                                    <p class="mt-1 text-xs"><span class="font-semibold text-aiu-ink-700">Skills:</span> <span class="text-aiu-ink-600">{{ $app->skills }}</span></p>
                                                @endif
                                                <p class="mt-2 text-sm text-aiu-ink-700 leading-relaxed whitespace-pre-line">{{ $app->message }}</p>

                                                @if ($isLeader)
                                                    <div class="mt-3">
                                                        <label class="block text-[11px] font-semibold text-aiu-ink-700 mb-1">Optional response message</label>
                                                        <input type="text" wire:model="applicationResponses.{{ $app->id }}" maxlength="500"
                                                               placeholder="Add a short note (sent to the applicant)"
                                                               class="input-3d w-full px-3 py-2 rounded-lg text-xs">
                                                    </div>

                                                    <div class="mt-3 flex items-center justify-end gap-2">
                                                        <button wire:click="rejectApplication({{ $app->id }})"
                                                                wire:confirm="Reject this application?"
                                                                class="btn-soft px-3 py-1.5 rounded-lg text-xs font-semibold">
                                                            Reject
                                                        </button>
                                                        <button wire:click="approveApplication({{ $app->id }})"
                                                                wire:confirm="Approve this applicant and add them to your team?"
                                                                class="btn-aiu px-4 py-1.5 rounded-lg text-xs font-semibold">
                                                            Approve & add
                                                        </button>
                                                    </div>
                                                @else
                                                    <p class="mt-3 text-[11px] text-aiu-ink-400 italic">Waiting on team leader's decision.</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if ($reviewedApplications->isNotEmpty())
                            <div class="mt-6 pt-4 border-t border-aiu-line">
                                <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-ink-400 font-bold mb-3">Recent decisions</p>
                                <div class="space-y-2">
                                    @foreach ($reviewedApplications as $app)
                                        <div class="flex items-center gap-3 text-xs text-aiu-ink-600 px-3 py-2 rounded-lg surface-soft">
                                            <x-avatar :user="$app->user" size="xs" />
                                            <span class="font-semibold text-aiu-ink-700">{{ $app->user->name }}</span>
                                            <span class="text-aiu-ink-400">·</span>
                                            <span class="font-semibold {{ $app->status === 'approved' ? 'text-emerald-600' : ($app->status === 'rejected' ? 'text-aiu-red' : 'text-aiu-ink-500') }}">
                                                {{ ucfirst($app->status) }}
                                            </span>
                                            <span class="text-aiu-ink-400 ml-auto">{{ optional($app->reviewed_at)->diffForHumans() }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </section>
            </div>
        @endif
    @endif
</div>
