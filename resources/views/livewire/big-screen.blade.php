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

    @if ($activeAttendanceSessions->isNotEmpty())
        <div class="px-12 py-3 bg-gradient-to-r from-emerald-50 via-white to-emerald-50 border-b border-aiu-line flex items-center gap-6 flex-wrap">
            <div class="flex items-center gap-3">
                <span class="relative inline-flex items-center justify-center w-3 h-3">
                    <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60 animate-ping"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <p class="text-[10px] uppercase tracking-[0.32em] text-emerald-700 font-bold">Live Attendance</p>
            </div>
            <div class="flex items-center gap-6 flex-1 flex-wrap">
                @foreach ($activeAttendanceSessions as $session)
                    <div class="flex items-baseline gap-2">
                        <span class="font-heading text-3xl font-bold text-emerald-700 tabular-nums leading-none">
                            {{ $session->attendances_count }}
                        </span>
                        <div class="flex flex-col">
                            <span class="font-heading text-sm font-bold text-aiu-ink-900 leading-tight">{{ $session->name }}</span>
                            <span class="text-[10px] uppercase tracking-wider text-aiu-ink-500">checked in</span>
                        </div>
                    </div>
                @endforeach
                @if ($activeAttendanceSessions->count() > 1)
                    <div class="flex items-baseline gap-2 ml-auto pl-6 border-l border-emerald-200">
                        <span class="font-heading text-3xl font-bold text-aiu-ink-900 tabular-nums leading-none">
                            {{ $todayCheckIns }}
                        </span>
                        <div class="flex flex-col">
                            <span class="text-[11px] uppercase tracking-wider text-aiu-ink-500 font-semibold">total today</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

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

    <main class="flex-1 px-12 py-8 overflow-y-auto">
        @include('partials.winners-hero', ['winners' => $winners, 'size' => 'md'])

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

    @if ($showcaseTeams->isNotEmpty() || $partners->isNotEmpty())
        <section class="border-t border-aiu-line bg-gradient-to-r from-aiu-ink-50 via-white to-aiu-ink-50 py-3 overflow-hidden">
            <div class="flex items-center gap-4 px-12 mb-2 flex-wrap">
                <span class="inline-block w-2 h-2 rounded-full bg-aiu-red animate-pulse"></span>
                <p class="text-[10px] uppercase tracking-[0.3em] text-aiu-red font-bold">Meet the Teams &amp; Partners</p>
                <p class="text-[11px] text-aiu-ink-400">
                    {{ $showcaseTeams->count() }} {{ \Illuminate\Support\Str::plural('team', $showcaseTeams->count()) }}
                    @if ($partners->isNotEmpty())
                        · {{ $partners->count() }} {{ \Illuminate\Support\Str::plural('partner', $partners->count()) }}
                    @endif
                </p>
            </div>
            <div class="bs-marquee">
                <div class="bs-marquee__track">
                    @for ($pass = 0; $pass < 2; $pass++)
                        @foreach ($showcaseTeams as $team)
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

                        @foreach ($partners as $partner)
                            @php
                                $orgName = $partner->organization ?: $partner->name;
                                $partnerLogo = $partner->org_logo_url ?: $partner->avatar_url;
                            @endphp
                            <div class="bs-marquee__team bs-marquee__partner">
                                <div class="bs-marquee__logo bs-marquee__logo--partner">
                                    @if ($partnerLogo)
                                        <img src="{{ $partnerLogo }}" alt="{{ $orgName }}" loading="lazy">
                                    @else
                                        <span class="bs-marquee__logo-initials">
                                            {{ \Illuminate\Support\Str::upper($partner->org_initials) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="bs-marquee__meta">
                                    <p class="bs-marquee__name">{{ $orgName }}</p>
                                    <p class="bs-marquee__tag">
                                        <span class="bs-marquee__badge">Partner</span>
                                        {{ $partner->name }}
                                    </p>
                                </div>
                                <div class="bs-marquee__sep"></div>
                            </div>
                        @endforeach
                    @endfor
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
            .bs-marquee__partner .bs-marquee__logo--partner {
                background: linear-gradient(135deg, #FEF1F3, #fff);
                box-shadow: 0 0 0 2px rgba(200,16,46,.18), 0 2px 8px rgba(0,0,0,.10);
            }
            .bs-marquee__badge {
                display: inline-block;
                background: #C8102E;
                color: #fff;
                font-size: 0.65rem;
                font-weight: 700;
                letter-spacing: 0.05em;
                padding: 1px 6px;
                border-radius: 4px;
                margin-right: 4px;
                text-transform: uppercase;
            }
            @keyframes bs-marquee-scroll {
                0%   { transform: translateX(0); }
                100% { transform: translateX(-50%); }
            }
        </style>
    @endif

    @if ($partners->isNotEmpty())
        <section class="px-12 py-3 bg-white/95 backdrop-blur-md border-t border-aiu-line flex items-center gap-6">
            <p class="text-[10px] uppercase tracking-[0.32em] text-aiu-ink-400 font-bold whitespace-nowrap">
                Sponsored by
            </p>
            <div class="flex items-center gap-6 flex-1 overflow-hidden">
                @foreach ($partners as $partner)
                    @php
                        $orgName = $partner->organization ?: $partner->name;
                        $partnerLogo = $partner->org_logo_url ?: $partner->avatar_url;
                    @endphp
                    <div class="flex items-center gap-3 flex-shrink-0" title="{{ $orgName }}">
                        <div class="w-14 h-14 rounded-lg bg-white border border-aiu-line/60 shadow-sm flex items-center justify-center p-1.5">
                            @if ($partnerLogo)
                                <img src="{{ $partnerLogo }}" alt="{{ $orgName }}" class="max-h-full max-w-full object-contain">
                            @else
                                <span class="font-heading font-bold text-aiu-red text-sm">
                                    {{ \Illuminate\Support\Str::upper($partner->org_initials) }}
                                </span>
                            @endif
                        </div>
                        <div>
                            <p class="font-heading font-bold text-sm text-aiu-ink-900 leading-tight">{{ $orgName }}</p>
                            <p class="text-[10px] text-aiu-ink-400">{{ $partner->name }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <footer class="py-3 px-12 bg-white/85 backdrop-blur-md border-t border-aiu-line flex items-center justify-between text-xs text-aiu-ink-600">
        <p>{{ $edition?->name }} · ACIE</p>
        <p class="flex items-center gap-2">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Live · auto-refresh every 3s
        </p>
    </footer>
</div>
