@php
    $roleBadge = match ($primaryRole) {
        'super_admin' => ['Admin', 'bg-aiu-ink-900 text-white'],
        'judge' => ['Judge', 'bg-aiu-red text-white'],
        'mentor' => ['Mentor', 'bg-emerald-600 text-white'],
        'team_leader' => ['Team Leader', 'bg-aiu-gold-500 text-white'],
        'team_member' => ['Team Member', 'bg-aiu-ink-200 text-aiu-ink-700'],
        'voter' => ['Voter', 'bg-aiu-ink-100 text-aiu-ink-600'],
        default => ['Participant', 'bg-aiu-ink-100 text-aiu-ink-600'],
    };
@endphp

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
    <a href="{{ url()->previous() }}" class="inline-flex items-center gap-1.5 text-xs text-aiu-ink-600 hover:text-aiu-red transition mb-5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Back
    </a>

    <header class="card-3d rounded-3xl overflow-hidden mb-6 relative">
        <div class="absolute inset-0 bg-gradient-to-br from-aiu-red-50/60 via-white to-aiu-gold-50/40 pointer-events-none"></div>
        <div class="relative p-6 lg:p-8 flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
            <x-avatar :user="$user" size="2xl" class="ring-4 ring-aiu-red/20" />
            <div class="flex-1 min-w-0">
                <span class="inline-flex items-center gap-1.5 text-[10px] uppercase tracking-wider px-2.5 py-0.5 rounded-md font-bold {{ $roleBadge[1] }}">
                    {{ $roleBadge[0] }}
                </span>
                <h1 class="font-heading text-3xl lg:text-4xl font-bold text-aiu-ink-900 mt-2">{{ $user->name }}</h1>
                @if ($user->headline)
                    <p class="mt-1 text-sm text-aiu-ink-700">{{ $user->headline }}</p>
                @endif
                @if ($user->institution)
                    <p class="mt-0.5 text-xs text-aiu-ink-500 inline-flex items-center gap-1.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        {{ $user->institution }}
                    </p>
                @endif

                @if (!empty($user->social_links))
                    <div class="mt-3 flex flex-wrap gap-2 justify-center sm:justify-start">
                        @foreach (['linkedin' => 'LinkedIn', 'github' => 'GitHub', 'twitter' => 'Twitter', 'website' => 'Website'] as $key => $label)
                            @if (!empty($user->social_links[$key]))
                                <a href="{{ $user->social_links[$key] }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md chip-3d text-[11px] font-semibold text-aiu-ink-700 hover:text-aiu-red transition">
                                    {{ $label }}
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 3h7v7M21 3l-9 9"/></svg>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </header>

    @if ($user->bio)
        <section class="card-3d rounded-3xl p-6 lg:p-7 mb-6">
            <h2 class="font-heading text-lg font-bold text-aiu-ink-900 mb-3">About</h2>
            <p class="text-sm text-aiu-ink-700 leading-relaxed whitespace-pre-wrap">{{ $user->bio }}</p>
        </section>
    @endif

    @if ($teams->isNotEmpty())
        <section class="card-3d rounded-3xl p-6 lg:p-7">
            <h2 class="font-heading text-lg font-bold text-aiu-ink-900 mb-4">Teams</h2>
            <div class="space-y-2">
                @foreach ($teams as $team)
                    <a href="{{ route('teams.show', $team->slug) }}"
                       class="card-3d card-3d-hover rounded-xl p-3 flex items-center gap-3">
                        @if ($team->logo_path)
                            <img src="{{ asset('storage/' . $team->logo_path) }}" alt="{{ $team->name }}" class="w-10 h-10 rounded-lg object-cover">
                        @else
                            <span class="w-10 h-10 rounded-lg bg-aiu-red/10 text-aiu-red font-heading font-bold flex items-center justify-center">
                                {{ mb_substr($team->name, 0, 1) }}
                            </span>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="font-heading font-bold text-sm text-aiu-ink-900">{{ $team->name }}</p>
                            <p class="text-[11px] text-aiu-ink-600">{{ $team->theme?->name ?? '—' }}</p>
                        </div>
                        @if ($team->pivot->is_leader ?? false)
                            <span class="text-[9px] uppercase tracking-wider px-1.5 py-0.5 rounded-md bg-aiu-gold-500 text-white font-bold">Leader</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
