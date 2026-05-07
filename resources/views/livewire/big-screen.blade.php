<div wire:poll.3s class="h-screen w-screen overflow-hidden flex flex-col">
    <header class="flex items-center justify-between px-12 py-7 border-b border-aiu-line bg-white/85 backdrop-blur-md">
        <div class="flex items-center gap-5">
            <div class="logo-plate w-20 h-20 rounded-2xl flex items-center justify-center p-3">
                <img src="{{ asset('img/aec-logo.png') }}" alt="AIU" class="w-full h-full object-contain">
            </div>
            <div>
                <p class="text-aiu-red uppercase tracking-[0.32em] text-xs font-bold">Alamein International University</p>
                <h1 class="font-heading text-3xl font-bold leading-tight text-aiu-ink-900">Next City AI Hackathon · Live</h1>
            </div>
        </div>
        <div class="text-right">
            @if ($currentPhase)
                <p class="text-[11px] uppercase tracking-[0.28em] text-aiu-ink-400 font-bold">Current Phase</p>
                <p class="font-heading text-2xl font-bold text-aiu-ink-900">{{ $currentPhase->label }}</p>
                <p class="text-sm text-aiu-red tabular-nums font-bold mt-1">
                    Ends {{ $currentPhase->ends_at->format('H:i') }}
                </p>
            @else
                <p class="text-aiu-ink-400 text-lg">No active phase</p>
            @endif
        </div>
    </header>

    @if ($nowPitching && $nowPitching->team)
        <div class="px-12 py-5 bg-gradient-to-r from-aiu-red-50 via-white to-aiu-gold-50 border-b border-aiu-line flex items-center gap-6">
            <div class="w-16 h-16 rounded-2xl btn-aiu-solid flex items-center justify-center animate-pulse">
                <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.28em] text-aiu-red font-bold">Now Pitching</p>
                <p class="font-heading text-4xl font-bold text-aiu-ink-900">{{ $nowPitching->team->name }}</p>
            </div>
        </div>
    @endif

    <main class="flex-1 px-12 py-8 overflow-hidden">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-aiu-red uppercase tracking-[0.28em] text-xs font-bold">Live Leaderboard</p>
                <h2 class="font-heading text-3xl font-bold text-aiu-ink-900">{{ $round === 'finals' ? 'Finals' : 'Round 1' }} · Top 10</h2>
            </div>
            <p class="text-aiu-ink-400 text-sm tabular-nums">{{ $serverNow->format('Y-m-d H:i:s') }}</p>
        </div>

        <div class="space-y-2.5 h-full overflow-y-auto pb-4">
            @forelse ($teams as $i => $team)
                @php $isFirst = $i === 0; $isPodium = $i < 3; @endphp
                <div class="row-anim grid grid-cols-12 items-center gap-4 px-6 py-4 rounded-2xl card-3d
                            {{ $isFirst ? 'bg-gradient-to-r from-aiu-gold-50/60 via-white to-aiu-red-50/40' : '' }}">
                    <div class="col-span-1">
                        <span class="font-heading inline-flex items-center justify-center w-14 h-14 rounded-xl text-2xl font-bold tabular-nums
                            {{ $isFirst
                               ? 'btn-aiu-solid'
                               : ($isPodium
                                   ? 'bg-aiu-red-50 text-aiu-red ring-1 ring-aiu-red/20'
                                   : 'bg-aiu-ink-50 text-aiu-ink-600 ring-1 ring-aiu-line') }}">
                            {{ $i + 1 }}
                        </span>
                    </div>
                    <div class="col-span-5">
                        <p class="font-heading font-bold text-2xl text-aiu-ink-900">{{ $team->name }}</p>
                        <p class="text-sm text-aiu-ink-600 mt-0.5">{{ $team->theme?->name ?? '—' }}</p>
                    </div>
                    <div class="col-span-2 text-center">
                        <p class="text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold">Judges</p>
                        <p class="font-heading text-xl font-bold text-aiu-ink-700 tabular-nums">
                            {{ $team->avg_total !== null ? number_format($team->avg_total, 2) : '—' }}
                        </p>
                        <p class="text-[10px] text-aiu-ink-400 mt-0.5">{{ $team->judge_count }} judges</p>
                    </div>
                    <div class="col-span-2 text-center">
                        <p class="text-[10px] uppercase tracking-wider text-aiu-red font-bold">Votes</p>
                        <p class="font-heading text-xl font-bold text-aiu-ink-700 tabular-nums">{{ $team->vote_count }}</p>
                    </div>
                    <div class="col-span-2 text-right">
                        @if ($team->final_score !== null)
                            <p class="text-[10px] uppercase tracking-wider text-aiu-red font-bold mb-1">Final</p>
                            <p class="font-heading text-5xl font-bold tabular-nums {{ $isFirst ? 'text-aiu-red' : 'text-aiu-ink-900' }}">
                                {{ number_format($team->final_score, 2) }}
                            </p>
                        @else
                            <p class="text-aiu-ink-400 text-xl italic">awaiting</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-32 text-aiu-ink-400">
                    <p class="font-heading text-2xl">Standings will appear here as judges lock scores.</p>
                </div>
            @endforelse
        </div>
    </main>

    @if ($showcaseTeams->isNotEmpty())
        @php
            // Duplicate the list once so the CSS marquee can loop seamlessly.
            $ribbon = $showcaseTeams->concat($showcaseTeams);
        @endphp
        <section class="border-t border-aiu-line bg-gradient-to-r from-aiu-ink-50 via-white to-aiu-ink-50 py-3 overflow-hidden">
            <div class="flex items-center gap-4 px-12 mb-2">
                <span class="inline-block w-2 h-2 rounded-full bg-aiu-red animate-pulse"></span>
                <p class="text-[10px] uppercase tracking-[0.3em] text-aiu-red font-bold">Meet the Teams</p>
                <p class="text-[11px] text-aiu-ink-400">{{ $showcaseTeams->count() }} {{ \Illuminate\Support\Str::plural('team', $showcaseTeams->count()) }}</p>
            </div>
            <div class="bs-marquee">
                <div class="bs-marquee__track">
                    @foreach ($ribbon as $team)
                        <div class="bs-marquee__team">
                            <div class="bs-marquee__logo">
                                @if ($team->logo_path)
                                    <img src="{{ $team->logo_url }}" alt="{{ $team->name }}" loading="lazy">
                                @else
                                    <span class="bs-marquee__logo-initials">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($team->name, 0, 2)) }}
                                    </span>
                                @endif
                            </div>
                            <div class="bs-marquee__meta">
                                <p class="bs-marquee__name">{{ $team->name }}</p>
                                @if ($team->tagline)
                                    <p class="bs-marquee__tag">{{ \Illuminate\Support\Str::limit($team->tagline, 60) }}</p>
                                @endif
                            </div>
                            <div class="bs-marquee__members">
                                @foreach ($team->members as $member)
                                    <div class="bs-marquee__avatar" title="{{ $member->name }}">
                                        @if ($member->avatar_path)
                                            <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" loading="lazy">
                                        @else
                                            <span>{{ $member->initials }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="bs-marquee__sep"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <style>
            .bs-marquee { width: 100%; overflow: hidden; }
            .bs-marquee__track {
                display: inline-flex;
                align-items: center;
                gap: 0;
                white-space: nowrap;
                animation: bs-marquee-scroll 200s linear infinite;
                will-change: transform;
            }
            .bs-marquee:hover .bs-marquee__track { animation-play-state: paused; }
            .bs-marquee__team {
                display: inline-flex;
                align-items: center;
                gap: 1rem;
                padding: 0 0.75rem;
                flex-shrink: 0;
            }
            .bs-marquee__logo {
                width: 88px; height: 88px;
                border-radius: 18px;
                background: #fff;
                box-shadow: 0 2px 8px rgba(0,0,0,.10);
                display: flex; align-items: center; justify-content: center;
                padding: 8px;
                flex-shrink: 0;
            }
            .bs-marquee__logo img {
                max-width: 100%; max-height: 100%;
                object-fit: contain;
            }
            .bs-marquee__logo-initials {
                font-family: 'Ubuntu', sans-serif;
                font-weight: 700;
                font-size: 1.5rem;
                color: #C8102E;
                letter-spacing: 0.05em;
            }
            .bs-marquee__meta {
                min-width: 0;
                max-width: 220px;
            }
            .bs-marquee__name {
                font-family: 'Ubuntu', system-ui, sans-serif;
                font-weight: 700;
                font-size: 1.15rem;
                color: #1a1a1a;
                line-height: 1.15;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .bs-marquee__tag {
                font-size: 0.78rem;
                color: #6b7280;
                margin-top: 3px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .bs-marquee__members {
                display: inline-flex;
                align-items: center;
            }
            .bs-marquee__members > .bs-marquee__avatar + .bs-marquee__avatar {
                margin-left: -14px;
            }
            .bs-marquee__avatar {
                width: 72px; height: 72px;
                border-radius: 9999px;
                background: #e5e7eb;
                box-shadow: 0 0 0 3px #fff, 0 2px 4px rgba(0,0,0,.08);
                overflow: hidden;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            .bs-marquee__avatar img {
                width: 100%; height: 100%;
                object-fit: cover;
            }
            .bs-marquee__avatar span {
                font-family: 'Ubuntu', sans-serif;
                font-weight: 700;
                font-size: 1rem;
                color: #6b7280;
            }
            .bs-marquee__sep {
                width: 1px;
                height: 72px;
                background: linear-gradient(180deg, transparent, #d1d5db, transparent);
                margin: 0 0.75rem;
            }
            @keyframes bs-marquee-scroll {
                0%   { transform: translateX(0); }
                100% { transform: translateX(-50%); }
            }
        </style>
    @endif

    <footer class="py-3 px-12 bg-white/85 backdrop-blur-md border-t border-aiu-line flex items-center justify-between text-xs text-aiu-ink-600">
        <p>{{ $edition?->name }} · ACIE</p>
        <p class="flex items-center gap-2">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Live · auto-refresh every 3s
        </p>
    </footer>
</div>
