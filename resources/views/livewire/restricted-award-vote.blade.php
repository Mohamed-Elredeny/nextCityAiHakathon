<div class="min-h-screen bg-aiu-cream">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16">

        {{-- Header --}}
        <div class="mb-10 lg:mb-14 text-center">
            <p class="text-[11px] uppercase tracking-[0.28em] text-aiu-red font-bold">Special Awards · Restricted Voting</p>
            <h1 class="font-heading text-3xl lg:text-5xl font-bold text-aiu-ink-900 mt-3">Cast your vote</h1>
            <p class="mt-4 text-aiu-ink-600 max-w-2xl mx-auto leading-relaxed">
                These two awards are decided by the people who lived the event with you — team members,
                judges, mentors, and organizers. One vote per award, and you can change your pick while
                voting is open.
            </p>
        </div>

        {{-- Eligibility / phase gates --}}
        @auth
            @if (!$isEligible)
                <div class="card-3d rounded-2xl p-8 text-center max-w-2xl mx-auto">
                    <div class="inline-flex w-14 h-14 rounded-full bg-aiu-red-50 text-aiu-red items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.732-3L13.732 4a2 2 0 00-3.464 0L3.27 16A2 2 0 005 19z"/></svg>
                    </div>
                    <h2 class="font-heading text-xl font-bold text-aiu-ink-900">Not eligible to vote</h2>
                    <p class="mt-3 text-sm text-aiu-ink-600">
                        These awards are restricted to participants who are part of a team, or to judges, mentors and organizers.
                        Public attendees can vote on the People's Choice award instead.
                    </p>
                    <div class="mt-6 flex justify-center gap-3">
                        <a href="{{ route('vote') }}" class="btn-aiu px-5 py-2.5 rounded-lg text-sm font-bold">People's Choice voting</a>
                        <a href="{{ route('home') }}" class="px-5 py-2.5 rounded-lg text-sm font-bold ring-1 ring-aiu-line text-aiu-ink-700 hover:bg-white">Back to leaderboard</a>
                    </div>
                </div>
            @elseif (!$isOpen)
                <div class="card-3d rounded-2xl p-8 text-center max-w-2xl mx-auto">
                    <div class="inline-flex w-14 h-14 rounded-full bg-aiu-ink-100 text-aiu-ink-600 items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h2 class="font-heading text-xl font-bold text-aiu-ink-900">Voting is not open right now</h2>
                    <p class="mt-3 text-sm text-aiu-ink-600">
                        @if ($phase)
                            Voting window: <strong>{{ $phase->starts_at?->format('H:i') }} – {{ $phase->ends_at?->format('H:i') }}</strong>.
                        @else
                            Voting will open shortly before the awards ceremony.
                        @endif
                    </p>
                </div>
            @else
                {{-- Voter context strip --}}
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3 px-4 py-3 rounded-xl bg-white ring-1 ring-aiu-line">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-aiu-red-50 text-aiu-red font-heading font-bold text-sm">
                            {{ Auth::user()->initials }}
                        </span>
                        <div>
                            <p class="font-heading text-sm font-bold text-aiu-ink-900">{{ Auth::user()->name }}</p>
                            <p class="text-[11px] uppercase tracking-wider text-aiu-ink-400 font-bold">
                                voting as {{ str_replace('_', ' ', $voterRole) }}
                            </p>
                        </div>
                    </div>
                    @if ($phase)
                        <p class="text-[11px] text-aiu-ink-600">
                            Window closes <strong>{{ $phase->ends_at?->format('H:i') }}</strong>
                        </p>
                    @endif
                </div>

                @if ($message)
                    <div class="mb-4 p-3 rounded-lg bg-emerald-50 text-emerald-800 text-sm font-semibold ring-1 ring-emerald-200">
                        {{ $message }}
                    </div>
                @endif
                @if ($error)
                    <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-800 text-sm font-semibold ring-1 ring-red-200">
                        {{ $error }}
                    </div>
                @endif

                @if ($teams->isEmpty())
                    <div class="card-3d rounded-2xl p-8 text-center">
                        <p class="text-aiu-ink-600">No teams are currently eligible for voting.</p>
                    </div>
                @else
                    @foreach ($awards as $awardKey => $awardLabel)
                        <section class="mb-10 last:mb-0">
                            <div class="flex items-end justify-between mb-4">
                                <div>
                                    <p class="text-[11px] uppercase tracking-[0.22em] text-aiu-red font-bold">Award</p>
                                    <h2 class="font-heading text-2xl lg:text-3xl font-bold text-aiu-ink-900 mt-1">{{ $awardLabel }}</h2>
                                </div>
                                @if ($myVotes[$awardKey])
                                    <p class="text-xs text-aiu-ink-600">
                                        Your pick:
                                        <strong class="text-aiu-ink-900">
                                            {{ optional($teams->firstWhere('id', $myVotes[$awardKey]))->name ?? 'Team' }}
                                        </strong>
                                    </p>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach ($teams as $team)
                                    @php $picked = $myVotes[$awardKey] === $team->id; @endphp
                                    <button type="button"
                                            wire:click="vote('{{ $awardKey }}', {{ $team->id }})"
                                            wire:loading.attr="disabled"
                                            class="text-left p-4 rounded-xl ring-1 transition flex items-start gap-3
                                                   {{ $picked
                                                        ? 'bg-aiu-red text-white ring-aiu-red shadow-md'
                                                        : 'bg-white ring-aiu-line hover:ring-aiu-red/40 hover:bg-aiu-cream' }}">
                                        @if ($team->logo_path)
                                            <img src="{{ asset('storage/' . $team->logo_path) }}" alt="{{ $team->name }}"
                                                 class="shrink-0 w-11 h-11 rounded-lg object-cover ring-1 {{ $picked ? 'ring-white/40' : 'ring-aiu-line' }}">
                                        @else
                                            <div class="shrink-0 w-11 h-11 rounded-lg flex items-center justify-center font-heading font-bold text-base
                                                        {{ $picked ? 'bg-white/15 text-white' : 'bg-aiu-red-50 text-aiu-red' }}">
                                                {{ mb_substr($team->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <p class="font-heading font-bold text-sm leading-tight {{ $picked ? 'text-white' : 'text-aiu-ink-900' }}">
                                                {{ $team->name }}
                                            </p>
                                            <p class="mt-0.5 text-[11px] truncate {{ $picked ? 'text-white/80' : 'text-aiu-ink-500' }}">
                                                {{ $team->theme?->name ?? '—' }}
                                            </p>
                                            @if ($picked)
                                                <p class="mt-2 inline-flex items-center gap-1 text-[10px] uppercase tracking-wider font-bold">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                    Your vote
                                                </p>
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                @endif
            @endif
        @else
            <div class="card-3d rounded-2xl p-8 text-center max-w-2xl mx-auto">
                <h2 class="font-heading text-xl font-bold text-aiu-ink-900">Sign in to vote</h2>
                <p class="mt-3 text-sm text-aiu-ink-600">
                    Special-award voting is restricted to registered participants, judges, mentors and organizers.
                </p>
                <div class="mt-6 flex justify-center gap-3">
                    <a href="{{ route('login') }}" class="btn-aiu px-5 py-2.5 rounded-lg text-sm font-bold">Sign in</a>
                    <a href="{{ route('vote') }}" class="px-5 py-2.5 rounded-lg text-sm font-bold ring-1 ring-aiu-line text-aiu-ink-700 hover:bg-white">People's Choice (no login)</a>
                </div>
            </div>
        @endauth

    </div>
</div>
