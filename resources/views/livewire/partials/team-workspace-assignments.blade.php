@if ($assignments->isEmpty())
    <div class="card-3d rounded-2xl p-8 text-center">
        <div class="w-14 h-14 rounded-2xl bg-aiu-ink-50 mx-auto mb-3 flex items-center justify-center">
            <svg class="w-7 h-7 text-aiu-ink-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <p class="font-heading text-lg font-bold text-aiu-ink-700">No assignments yet</p>
        <p class="text-sm text-aiu-ink-500 mt-1">When the organizers post one, it will show up here.</p>
    </div>
@else
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Sidebar: list of assignments --}}
        <aside class="lg:col-span-4 xl:col-span-3">
            <div class="card-3d rounded-2xl p-3 lg:sticky lg:top-20 space-y-1.5">
                <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-red font-bold px-2 pt-2 pb-1">
                    Assignments ({{ $assignments->count() }})
                </p>
                @foreach ($assignments as $a)
                    @php
                        $isActive = $activeAssignment && $activeAssignment->id === $a->id;
                        $summary = $assignmentSummaries->get($a->id);
                        $fileCount = $summary?->files_count ?? 0;
                        $isOpen = $a->isOpen();
                        $isPast = $a->isPastDeadline();
                    @endphp
                    <button type="button" wire:click="selectAssignment({{ $a->id }})"
                        class="w-full text-left px-3 py-2.5 rounded-xl transition flex items-start gap-2
                               {{ $isActive ? 'bg-aiu-red text-white shadow-sm' : 'hover:bg-aiu-ink-50 text-aiu-ink-700' }}">
                        <span class="mt-0.5 inline-flex items-center justify-center w-5 h-5 rounded-md flex-shrink-0
                                     {{ $isActive ? 'bg-white/20 text-white'
                                        : ($fileCount > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-aiu-ink-100 text-aiu-ink-400') }}">
                            @if ($fileCount > 0)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/></svg>
                            @endif
                        </span>
                        <span class="flex-1 min-w-0">
                            <span class="block font-semibold text-sm truncate">{{ $a->title }}</span>
                            <span class="block text-[11px] mt-0.5 {{ $isActive ? 'text-white/80' : 'text-aiu-ink-500' }}">
                                @if ($a->deadline_at)
                                    @if ($isPast)
                                        <span class="{{ $isActive ? '' : 'text-red-500' }} font-semibold">Closed</span>
                                    @else
                                        Due {{ $a->deadline_at->format('M d, H:i') }}
                                    @endif
                                @else
                                    Open
                                @endif
                                · {{ $fileCount }} {{ \Illuminate\Support\Str::plural('file', $fileCount) }}
                            </span>
                        </span>
                        @php
                            $scoresCol = $summary?->scores ?? collect();
                            $hasGrades = $a->release_grades && $scoresCol->isNotEmpty();
                            $avg = $hasGrades ? round((float) $scoresCol->avg('score'), 2) : null;
                            $maxLabel = rtrim(rtrim(number_format((float)$a->max_score, 2), '0'), '.');
                        @endphp
                        @if ($hasGrades)
                            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tabular-nums flex-shrink-0
                                         {{ $isActive ? 'bg-white/25 text-white' : 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200' }}">
                                {{ rtrim(rtrim(number_format((float)$avg, 2), '0'), '.') }}/{{ $maxLabel }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        </aside>

        {{-- Main content for active assignment --}}
        <section class="lg:col-span-8 xl:col-span-9">
            @if (! $activeAssignment)
                <div class="card-3d rounded-2xl p-8 text-center text-aiu-ink-500">
                    Pick an assignment from the left.
                </div>
            @else
                @php
                    $isOpen = $activeAssignment->isOpen();
                    $isPast = $activeAssignment->isPastDeadline();
                    $files = $activeAssignmentSubmission?->files ?? collect();
                    $maxFiles = (int) $activeAssignment->max_files;
                    $atLimit = $files->count() >= $maxFiles;
                @endphp

                <div class="card-3d rounded-2xl overflow-hidden">
                    {{-- Header --}}
                    <div class="px-5 py-4 border-b border-aiu-line bg-gradient-to-r from-aiu-ink-50/40 to-white">
                        <div class="flex items-start justify-between gap-3 flex-wrap">
                            <div class="flex-1 min-w-0">
                                <h2 class="font-heading text-xl font-bold text-aiu-ink-900">{{ $activeAssignment->title }}</h2>
                                @if ($activeAssignment->description)
                                    <p class="text-sm text-aiu-ink-600 mt-1 whitespace-pre-wrap">{{ $activeAssignment->description }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                @if ($isPast)
                                    <span class="inline-block px-2.5 py-1 rounded-full bg-red-50 text-red-700 text-xs font-bold ring-1 ring-red-200">
                                        Closed
                                    </span>
                                @elseif (! $isOpen)
                                    <span class="inline-block px-2.5 py-1 rounded-full bg-aiu-ink-100 text-aiu-ink-700 text-xs font-bold ring-1 ring-aiu-line">
                                        Not yet open
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold ring-1 ring-emerald-200">
                                        Open
                                    </span>
                                @endif
                                @if ($activeAssignment->deadline_at)
                                    <p class="text-[11px] text-aiu-ink-500 mt-1.5 tabular-nums">
                                        Due {{ $activeAssignment->deadline_at->format('M d, Y H:i') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-aiu-ink-500">
                            <span>Max <strong>{{ $maxFiles }}</strong> files</span>
                            <span>·</span>
                            <span>Max <strong>{{ number_format($activeAssignment->max_file_size_kb / 1024, 1) }} MB</strong> per file</span>
                            @if (! empty($activeAssignment->accepted_extensions))
                                <span>·</span>
                                <span>Accepted: <strong>{{ implode(', ', $activeAssignment->accepted_extensions) }}</strong></span>
                            @endif
                        </div>
                    </div>

                    {{-- Grades --}}
                    @php
                        $scores = $activeAssignmentSubmission?->scores ?? collect();
                        $showGrades = $activeAssignment->release_grades && $scores->isNotEmpty();
                        $maxScore = (float) $activeAssignment->max_score;
                        $maxScoreLabel = rtrim(rtrim(number_format($maxScore, 2), '0'), '.');
                        $avgScore = $scores->isNotEmpty() ? round((float) $scores->avg('score'), 2) : null;
                    @endphp
                    @if ($showGrades)
                        <div class="px-5 py-4 border-b border-aiu-line bg-emerald-50/40">
                            <div class="flex items-center justify-between flex-wrap gap-3 mb-3">
                                <p class="text-[10px] uppercase tracking-[0.22em] text-emerald-700 font-bold">Judges' grades</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-[11px] uppercase tracking-wider text-aiu-ink-500 font-semibold">Average</span>
                                    <span class="font-heading text-3xl font-bold text-emerald-700 tabular-nums leading-none">
                                        {{ rtrim(rtrim(number_format((float)$avgScore, 2), '0'), '.') }}
                                    </span>
                                    <span class="text-sm text-aiu-ink-500 tabular-nums">/ {{ $maxScoreLabel }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                @foreach ($scores as $s)
                                    <div class="rounded-xl border border-emerald-200/70 bg-white p-3 flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-full overflow-hidden bg-aiu-ink-100 flex items-center justify-center flex-shrink-0 ring-1 ring-emerald-200">
                                            @if ($s->judge?->avatar_path)
                                                <img src="{{ asset('storage/' . $s->judge->avatar_path) }}" class="w-full h-full object-cover" alt="">
                                            @else
                                                <span class="text-[11px] font-semibold text-aiu-ink-500">
                                                    {{ collect(explode(' ', $s->judge?->name ?? '?'))->take(2)->map(fn($p) => mb_substr($p,0,1))->implode('') ?: '?' }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                                <p class="font-semibold text-sm text-aiu-ink-900 truncate">{{ $s->judge?->name ?? 'Judge' }}</p>
                                                <p class="font-heading font-bold text-emerald-700 tabular-nums text-base">
                                                    {{ rtrim(rtrim(number_format((float)$s->score, 2), '0'), '.') }}
                                                    <span class="text-aiu-ink-400 text-xs font-normal">/ {{ $maxScoreLabel }}</span>
                                                </p>
                                            </div>
                                            @if ($s->feedback)
                                                <p class="mt-1.5 text-sm text-aiu-ink-700 whitespace-pre-wrap bg-aiu-ink-50/50 rounded p-2">{{ $s->feedback }}</p>
                                            @endif
                                            <p class="mt-1 text-[10px] text-aiu-ink-400">
                                                Graded {{ $s->graded_at?->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @elseif ($scores->isNotEmpty() && ! $activeAssignment->release_grades)
                        <div class="px-5 py-3 border-b border-aiu-line bg-amber-50">
                            <p class="text-sm text-amber-900">
                                <strong>Grades pending release.</strong> Judges have started grading; results will appear here once organizers release them.
                            </p>
                        </div>
                    @endif

                    {{-- Notes --}}
                    <div class="px-5 py-4 border-b border-aiu-line">
                        <label class="block text-xs uppercase tracking-wider font-bold text-aiu-ink-600 mb-1.5">
                            Team notes (visible to organizers)
                        </label>
                        <textarea wire:model="assignmentNotes" rows="3"
                            @if ($isPast || ! $isOpen) disabled @endif
                            placeholder="Optional summary of what you're submitting, links, etc."
                            class="w-full px-3 py-2 rounded-lg border border-aiu-line text-sm focus:border-aiu-red focus:ring-1 focus:ring-aiu-red disabled:bg-aiu-ink-50 disabled:cursor-not-allowed"></textarea>
                        @if (! $isPast && $isOpen)
                            <button type="button" wire:click="saveAssignmentNotes"
                                class="mt-2 px-3 py-1.5 rounded-lg bg-aiu-ink-700 text-white text-xs font-semibold hover:bg-aiu-ink-900 transition">
                                Save notes
                            </button>
                        @endif
                    </div>

                    {{-- Upload row --}}
                    @if ($isOpen && ! $isPast)
                        <div class="px-5 py-4 border-b border-aiu-line bg-aiu-ink-50/30">
                            @if ($atLimit)
                                <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-sm p-3">
                                    You've reached the {{ $maxFiles }}-file limit. Remove an older file to upload a new one.
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div class="md:col-span-2">
                                        <label class="block text-xs uppercase tracking-wider font-bold text-aiu-ink-600 mb-1.5">
                                            Add a file
                                        </label>
                                        <input type="file" wire:model="newAssignmentFile"
                                            class="block w-full text-sm text-aiu-ink-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-aiu-red file:text-white hover:file:bg-red-700 cursor-pointer">
                                        @error('newAssignmentFile')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs uppercase tracking-wider font-bold text-aiu-ink-600 mb-1.5">
                                            Comment (optional)
                                        </label>
                                        <input type="text" wire:model="assignmentFileComment"
                                            placeholder="e.g. v2, final draft…"
                                            class="w-full px-3 py-2 rounded-lg border border-aiu-line text-sm focus:border-aiu-red focus:ring-1 focus:ring-aiu-red">
                                    </div>
                                </div>
                                <button type="button" wire:click="uploadAssignmentFile"
                                    wire:loading.attr="disabled" wire:target="uploadAssignmentFile,newAssignmentFile"
                                    class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-aiu-red text-white text-sm font-semibold hover:bg-red-700 disabled:opacity-60 transition">
                                    <span wire:loading.remove wire:target="uploadAssignmentFile">Upload file</span>
                                    <span wire:loading wire:target="uploadAssignmentFile">Uploading…</span>
                                </button>
                            @endif

                            @if ($assignmentError)
                                <div class="mt-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm p-2.5">
                                    {{ $assignmentError }}
                                </div>
                            @endif
                            @if ($assignmentSaved)
                                <div class="mt-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm p-2.5">
                                    {{ $assignmentSaved }}
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Files list --}}
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-heading font-bold text-aiu-ink-900">
                                Submitted files <span class="text-aiu-ink-400 font-normal">({{ $files->count() }} / {{ $maxFiles }})</span>
                            </h3>
                            @if ($activeAssignmentSubmission?->last_activity_at)
                                <span class="text-[11px] text-aiu-ink-500">
                                    Last activity {{ $activeAssignmentSubmission->last_activity_at->diffForHumans() }}
                                    @if ($activeAssignmentSubmission->lastActivityBy)
                                        by {{ $activeAssignmentSubmission->lastActivityBy->name }}
                                    @endif
                                </span>
                            @endif
                        </div>

                        @if ($files->isEmpty())
                            <div class="border-2 border-dashed border-aiu-line rounded-xl p-8 text-center text-sm text-aiu-ink-500">
                                No files yet. Upload your first file above.
                            </div>
                        @else
                            <div class="space-y-2">
                                @foreach ($files as $file)
                                    <div class="border border-aiu-line rounded-xl p-3 flex items-start gap-3 bg-white hover:shadow-sm transition">
                                        <div class="w-10 h-10 rounded-lg bg-aiu-ink-50 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-aiu-ink-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <a href="{{ $file->url }}" target="_blank"
                                               class="text-sm font-semibold text-aiu-red hover:underline truncate block">
                                                {{ $file->original_name }}
                                            </a>
                                            <div class="text-[11px] text-aiu-ink-500 mt-0.5">
                                                {{ $file->human_size }}
                                                · uploaded by <strong>{{ $file->uploader?->name ?? '—' }}</strong>
                                                · {{ $file->created_at?->diffForHumans() }}
                                            </div>
                                            @if ($file->comment)
                                                <div class="mt-2 text-xs text-aiu-ink-700 bg-aiu-ink-50/60 rounded p-2 whitespace-pre-wrap">
                                                    {{ $file->comment }}
                                                </div>
                                            @endif
                                        </div>
                                        @if (! $isPast && $isOpen && ($file->uploaded_by === auth()->id() || $team->leader_id === auth()->id()))
                                            <button type="button"
                                                wire:click="deleteAssignmentFile({{ $file->id }})"
                                                wire:confirm="Remove this file?"
                                                class="text-xs text-red-600 hover:text-red-700 px-2 py-1 rounded hover:bg-red-50 flex-shrink-0"
                                                title="Remove">
                                                Remove
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </section>
    </div>
@endif
