<div class="min-h-screen aiu-bg-soft py-8 px-4 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <header class="mb-6 flex items-center justify-between flex-wrap gap-3">
            <div>
                <p class="text-aiu-red uppercase tracking-[0.28em] text-xs font-bold">Judge Panel</p>
                <h1 class="font-heading text-3xl font-bold text-aiu-ink-900">Assignments Grading</h1>
                <p class="text-sm text-aiu-ink-600 mt-1">
                    Grade pre-event assignments from the teams. Round 1 / Finals scoring is on
                    <a href="{{ route('judge') }}" class="text-aiu-red hover:underline">the main judge dashboard</a>.
                </p>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            {{-- Sidebar: assignments --}}
            <aside class="lg:col-span-3">
                <div class="card-3d rounded-2xl p-2 lg:sticky lg:top-4">
                    <p class="px-2 pt-2 pb-1 text-[10px] uppercase tracking-[0.22em] text-aiu-red font-bold">
                        Assignments ({{ $assignments->count() }})
                    </p>
                    @forelse ($assignments as $a)
                        @php $isActive = $assignment && $assignment->id === $a->id; @endphp
                        <button type="button" wire:click="selectAssignment({{ $a->id }})"
                            class="w-full text-left px-3 py-2.5 rounded-xl transition
                                   {{ $isActive ? 'bg-aiu-red text-white shadow-sm' : 'hover:bg-aiu-ink-50 text-aiu-ink-700' }}">
                            <p class="font-semibold text-sm truncate">{{ $a->title }}</p>
                            <p class="text-[11px] mt-0.5 {{ $isActive ? 'text-white/80' : 'text-aiu-ink-500' }}">
                                {{ $a->submissions_count }} {{ \Illuminate\Support\Str::plural('team', $a->submissions_count) }}
                                · /{{ rtrim(rtrim(number_format((float)$a->max_score, 2), '0'), '.') }}
                            </p>
                        </button>
                    @empty
                        <p class="px-3 py-4 text-xs text-aiu-ink-500 italic">No assignments yet.</p>
                    @endforelse
                </div>
            </aside>

            {{-- Middle: teams --}}
            <section class="lg:col-span-4">
                @if (! $assignment)
                    <div class="card-3d rounded-2xl p-8 text-center text-aiu-ink-500 text-sm">
                        Pick an assignment from the left to see teams.
                    </div>
                @else
                    <div class="card-3d rounded-2xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-aiu-line bg-aiu-ink-50/40">
                            <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-red font-bold">Teams ({{ $submissions->count() }})</p>
                            <p class="text-sm font-semibold text-aiu-ink-900 mt-0.5 truncate">{{ $assignment->title }}</p>
                        </div>
                        @if ($submissions->isEmpty())
                            <p class="p-6 text-sm text-aiu-ink-500 italic text-center">No teams have submitted any files yet.</p>
                        @else
                            <div class="divide-y divide-aiu-line/60 max-h-[70vh] overflow-y-auto">
                                @foreach ($submissions as $sub)
                                    @php
                                        $isActive = $submission && $submission->id === $sub->id;
                                        $myScore = $sub->scores->first();
                                    @endphp
                                    <button type="button" wire:click="selectSubmission({{ $sub->id }})"
                                        class="w-full text-left px-4 py-3 flex items-center gap-3 transition
                                               {{ $isActive ? 'bg-aiu-red-50' : 'hover:bg-aiu-ink-50' }}">
                                        @if ($sub->team->logo_path)
                                            <img src="{{ asset('storage/' . $sub->team->logo_path) }}" alt=""
                                                 class="w-10 h-10 rounded-lg object-cover flex-shrink-0 ring-1 ring-aiu-line">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-aiu-ink-100 flex items-center justify-center font-heading font-bold text-sm text-aiu-ink-500 flex-shrink-0">
                                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($sub->team->name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-sm text-aiu-ink-900 truncate">{{ $sub->team->name }}</p>
                                            <p class="text-[11px] text-aiu-ink-500">
                                                {{ $sub->files_count }} {{ \Illuminate\Support\Str::plural('file', $sub->files_count) }}
                                                @if ($sub->last_activity_at)
                                                    · {{ $sub->last_activity_at->diffForHumans() }}
                                                @endif
                                            </p>
                                        </div>
                                        @if ($myScore)
                                            <span class="text-[10px] font-bold tabular-nums px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200 flex-shrink-0">
                                                {{ rtrim(rtrim(number_format((float)$myScore->score, 2), '0'), '.') }}/{{ rtrim(rtrim(number_format((float)$assignment->max_score, 2), '0'), '.') }}
                                            </span>
                                        @else
                                            <span class="text-[10px] uppercase tracking-wider text-aiu-ink-400 font-semibold flex-shrink-0">to grade</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </section>

            {{-- Right: grading form --}}
            <section class="lg:col-span-5">
                @if (! $submission)
                    <div class="card-3d rounded-2xl p-8 text-center text-aiu-ink-500 text-sm">
                        Pick a team to grade.
                    </div>
                @else
                    <div class="card-3d rounded-2xl overflow-hidden">
                        <div class="px-5 py-4 border-b border-aiu-line bg-gradient-to-r from-aiu-red-50/40 to-white">
                            <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-red font-bold">Grading</p>
                            <h2 class="font-heading text-lg font-bold text-aiu-ink-900 mt-0.5">
                                {{ $submission->team->name }}
                            </h2>
                            <p class="text-xs text-aiu-ink-500 mt-0.5">{{ $submission->assignment->title }}</p>
                        </div>

                        @if ($submission->notes)
                            <div class="px-5 py-3 border-b border-aiu-line bg-amber-50/50">
                                <p class="text-[10px] uppercase tracking-wide text-amber-800 font-bold mb-1">Team notes</p>
                                <p class="text-sm text-amber-900 whitespace-pre-wrap">{{ $submission->notes }}</p>
                            </div>
                        @endif

                        <div class="px-5 py-4 border-b border-aiu-line">
                            <p class="text-[10px] uppercase tracking-wide text-aiu-ink-500 font-bold mb-2">
                                Submitted files ({{ $submission->files->count() }})
                            </p>
                            @if ($submission->files->isEmpty())
                                <p class="text-xs italic text-aiu-ink-500">No files.</p>
                            @else
                                <div class="space-y-1.5">
                                    @foreach ($submission->files as $file)
                                        <a href="{{ $file->url }}" target="_blank"
                                           class="block p-2.5 rounded-lg border border-aiu-line hover:border-aiu-red/40 hover:shadow-sm transition">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-aiu-ink-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <span class="text-sm font-medium text-aiu-red truncate flex-1">{{ $file->original_name }}</span>
                                                <span class="text-[10px] text-aiu-ink-400">{{ $file->human_size }}</span>
                                            </div>
                                            <p class="text-[10px] text-aiu-ink-500 mt-0.5">
                                                by {{ $file->uploader?->name ?? '—' }} · {{ $file->created_at?->diffForHumans() }}
                                            </p>
                                            @if ($file->comment)
                                                <p class="text-[11px] text-aiu-ink-700 bg-aiu-ink-50/60 rounded p-1.5 mt-1.5 whitespace-pre-wrap">{{ $file->comment }}</p>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Grading form --}}
                        <div class="px-5 py-5 space-y-3">
                            <div>
                                <label class="block text-xs uppercase tracking-wider font-bold text-aiu-ink-700 mb-1.5">
                                    Score (out of {{ rtrim(rtrim(number_format((float)$submission->assignment->max_score, 2), '0'), '.') }})
                                </label>
                                <input type="number" wire:model="score" step="0.5"
                                    min="0" max="{{ $submission->assignment->max_score }}"
                                    class="w-full px-3 py-2 rounded-lg border border-aiu-line text-lg font-bold tabular-nums focus:border-aiu-red focus:ring-1 focus:ring-aiu-red">
                                @error('score')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs uppercase tracking-wider font-bold text-aiu-ink-700 mb-1.5">
                                    Feedback (visible to team)
                                </label>
                                <textarea wire:model="feedback" rows="4"
                                    placeholder="What was strong, what needs improvement?"
                                    class="w-full px-3 py-2 rounded-lg border border-aiu-line text-sm focus:border-aiu-red focus:ring-1 focus:ring-aiu-red"></textarea>
                                @error('feedback')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            @if ($error)
                                <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm p-2.5">{{ $error }}</div>
                            @endif
                            @if ($saved)
                                <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm p-2.5">{{ $saved }}</div>
                            @endif

                            <button type="button" wire:click="saveScore"
                                wire:loading.attr="disabled" wire:target="saveScore"
                                class="w-full px-4 py-2.5 rounded-lg bg-aiu-red text-white font-semibold hover:bg-red-700 disabled:opacity-60 transition">
                                <span wire:loading.remove wire:target="saveScore">Save grade</span>
                                <span wire:loading wire:target="saveScore">Saving…</span>
                            </button>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
