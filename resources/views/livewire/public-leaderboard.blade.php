<div wire:poll.5s
     x-data="{
        fs: false,
        toggleFs() {
            const el = this.$refs.scoreboard;
            if (!document.fullscreenElement) {
                (el.requestFullscreen ? el.requestFullscreen() :
                 el.webkitRequestFullscreen ? el.webkitRequestFullscreen() : Promise.resolve()
                ).then(()=>{ this.fs = true; }).catch(()=>{ this.fs = !this.fs; });
            } else {
                document.exitFullscreen();
                this.fs = false;
            }
        }
     }"
     x-on:fullscreenchange.window="fs = !!document.fullscreenElement"
     x-on:keydown.escape.window="fs = false">

    <section class="relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-12 lg:py-16">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-8">
                <div class="max-w-2xl">
                    <p class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full chip-3d
                              text-aiu-red uppercase tracking-[0.22em] text-[11px] font-bold">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-aiu-red animate-pulse"></span>
                        Live · {{ $edition?->name ?? 'Hackathon' }}
                    </p>
                    <h1 class="mt-5 font-heading font-bold text-4xl lg:text-6xl leading-[1.05] tracking-tight text-aiu-ink-900">
                        Next City AI Hackathon
                        <span class="block mt-2 bg-gradient-to-r from-aiu-red to-aiu-red-700 bg-clip-text text-transparent">Scoreboard</span>
                    </h1>
                    <p class="mt-5 text-base lg:text-lg text-aiu-ink-600 leading-relaxed max-w-xl">
                        Live scores from every judge across the official 5-criterion rubric (each weighted 20%).
                    </p>
                </div>

                <div class="flex flex-col gap-4 lg:items-end">
                    @if ($currentPhase)
                        <div class="card-3d rounded-2xl px-5 py-4 min-w-[260px]">
                            <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-ink-400 font-bold">Current Phase</p>
                            <p class="mt-1 font-heading text-lg font-bold text-aiu-ink-900">{{ $currentPhase->label }}</p>
                            @if ($nextDeadline)
                                <p class="mt-1 text-xs text-aiu-ink-600">
                                    Ends <span class="text-aiu-red font-bold tabular-nums">{{ $nextDeadline->format('H:i') }}</span>
                                </p>
                            @endif
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-2">
                        <div class="inline-flex p-1 rounded-xl chip-3d gap-1">
                            <button wire:click="setRound('round1')"
                                class="px-4 sm:px-5 py-2 rounded-lg text-sm font-semibold transition
                                       {{ $round === 'round1' ? 'btn-aiu' : 'text-aiu-ink-700 hover:bg-aiu-ink-50' }}">
                                Round 1
                            </button>
                            <button wire:click="setRound('finals')"
                                class="px-4 sm:px-5 py-2 rounded-lg text-sm font-semibold transition
                                       {{ $round === 'finals' ? 'btn-aiu' : 'text-aiu-ink-700 hover:bg-aiu-ink-50' }}">
                                Finals
                            </button>
                        </div>
                        <button @click="toggleFs()" type="button"
                                class="btn-soft px-3 py-2.5 rounded-xl hidden sm:flex items-center gap-2 text-sm font-semibold"
                                :title="fs ? 'Exit fullscreen' : 'Enter fullscreen'">
                            <template x-if="!fs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4h4M4 16v4h4M20 8V4h-4M20 16v4h-4"/>
                                </svg>
                            </template>
                            <template x-if="fs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4M9 9H4M15 9V4M15 9h5M9 15v5M9 15H4M15 15v5M15 15h5"/>
                                </svg>
                            </template>
                            <span x-text="fs ? 'Exit' : 'Fullscreen'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-[100rem] mx-auto px-6 lg:px-8 pb-12">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
            @if ($nowPitching && $nowPitching->team)
                <div class="card-3d lg:col-span-2 rounded-2xl p-5 lg:p-6 flex items-center gap-5
                            bg-gradient-to-r from-aiu-red-50 via-white to-aiu-gold-50">
                    <div class="shrink-0 w-14 h-14 rounded-2xl btn-aiu flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.22em] text-aiu-red font-bold">Now Pitching</p>
                        <p class="mt-0.5 font-heading text-2xl font-bold text-aiu-ink-900">{{ $nowPitching->team->name }}</p>
                    </div>
                </div>
            @endif

            <a href="{{ route('vote') }}"
               class="card-3d card-3d-hover {{ ($nowPitching && $nowPitching->team) ? '' : 'lg:col-span-3' }} rounded-2xl p-5 flex items-center gap-4 group transition">
                <div wire:ignore class="shrink-0 bg-white p-1.5 rounded-lg ring-1 ring-aiu-line">
                    <div id="home-vote-qr"></div>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-aiu-red font-bold">People's Choice</p>
                    <p class="mt-0.5 font-heading text-lg font-bold text-aiu-ink-900">Scan to vote</p>
                    <p class="text-xs text-aiu-ink-600 mt-1">One vote per person · One per device</p>
                </div>
                <svg class="w-5 h-5 text-aiu-ink-400 group-hover:text-aiu-red transition" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
        <script>
            (function () {
                const el = document.getElementById('home-vote-qr');
                if (el && !el.firstChild && typeof QRCode !== 'undefined') {
                    new QRCode(el, {
                        text: {{ Js::from(route('vote')) }},
                        width: 70, height: 70,
                        colorDark: '#0F172A', colorLight: '#FFFFFF',
                        correctLevel: QRCode.CorrectLevel.H,
                    });
                }
            })();
        </script>

        @if (! empty($partners) && $partners->isNotEmpty())
            <section class="card-3d rounded-2xl mb-6 overflow-hidden bg-gradient-to-br from-white via-aiu-ink-50/40 to-white">
                <div class="px-6 py-5">
                    <div class="flex items-center justify-center gap-3 mb-5">
                        <span class="h-px w-12 bg-aiu-red/30"></span>
                        <p class="text-[11px] uppercase tracking-[0.4em] text-aiu-red font-bold">Our Partners</p>
                        <span class="h-px w-12 bg-aiu-red/30"></span>
                    </div>
                    <div class="flex flex-wrap items-stretch justify-center gap-5 sm:gap-8">
                        @foreach ($partners as $partner)
                            @php
                                $orgName = $partner->organization ?: $partner->name;
                                $href = $partner->org_url ?: null;
                                // Prefer the dedicated org logo, fall back to the
                                // person's avatar (admins sometimes upload the
                                // company logo there by mistake — show it anyway).
                                $partnerLogo = $partner->org_logo_url ?: $partner->avatar_url;
                            @endphp
                            <a @if ($href) href="{{ $href }}" target="_blank" rel="noopener" @else href="javascript:void(0)" @endif
                               class="group flex flex-col items-center gap-2.5 transition"
                               title="{{ $orgName }} — {{ $partner->name }}">
                                <div class="w-48 h-36 sm:w-64 sm:h-40 flex items-center justify-center bg-white rounded-2xl shadow-sm border border-aiu-line/60 p-2.5 group-hover:shadow-lg group-hover:border-aiu-red/40 group-hover:-translate-y-0.5 transition">
                                    @if ($partnerLogo)
                                        <img src="{{ $partnerLogo }}" alt="{{ $orgName }}" class="w-full h-full object-contain">
                                    @else
                                        <span class="font-heading font-bold text-3xl text-aiu-red tracking-wide">
                                            {{ \Illuminate\Support\Str::upper($partner->org_initials) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-center">
                                    <p class="text-sm font-heading font-bold text-aiu-ink-900 group-hover:text-aiu-red transition">
                                        {{ $orgName }}
                                    </p>
                                    <p class="text-[11px] text-aiu-ink-500 mt-0.5">{{ $partner->name }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if ((! empty($showcaseTeams) && $showcaseTeams->isNotEmpty()) || (! empty($partners) && $partners->isNotEmpty()))
            <section class="lb-marquee-wrap card-3d rounded-2xl mb-6 overflow-hidden">
                <div class="flex items-center gap-3 px-6 pt-4 flex-wrap">
                    <span class="inline-block w-2 h-2 rounded-full bg-aiu-red animate-pulse"></span>
                    <p class="text-[10px] uppercase tracking-[0.3em] text-aiu-red font-bold">Meet the Teams &amp; Partners</p>
                    <p class="text-[11px] text-aiu-ink-400">
                        {{ $showcaseTeams->count() }} {{ \Illuminate\Support\Str::plural('team', $showcaseTeams->count()) }}
                        @if ($partners->isNotEmpty())
                            · {{ $partners->count() }} {{ \Illuminate\Support\Str::plural('partner', $partners->count()) }}
                        @endif
                    </p>
                </div>
                <div class="lb-marquee py-4">
                    <div class="lb-marquee__track">
                        @for ($pass = 0; $pass < 2; $pass++)
                            {{-- Teams --}}
                            @foreach ($showcaseTeams as $team)
                                <a href="{{ route('teams.show', $team->slug) }}" class="lb-marquee__team">
                                    <div class="lb-marquee__logo">
                                        @if ($team->logo_path)
                                            <img src="{{ $team->logo_url }}" alt="{{ $team->name }}" loading="lazy">
                                        @else
                                            <span class="lb-marquee__logo-initials">
                                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($team->name, 0, 2)) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="lb-marquee__meta">
                                        <p class="lb-marquee__name">{{ $team->name }}</p>
                                        @if ($team->tagline)
                                            <p class="lb-marquee__tag">{{ \Illuminate\Support\Str::limit($team->tagline, 60) }}</p>
                                        @endif
                                    </div>
                                    <div class="lb-marquee__members">
                                        @foreach ($team->members as $member)
                                            <div class="lb-marquee__avatar" title="{{ $member->name }}">
                                                @if ($member->avatar_path)
                                                    <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" loading="lazy">
                                                @else
                                                    <span>{{ $member->initials }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="lb-marquee__sep"></div>
                                </a>
                            @endforeach

                            {{-- Partners --}}
                            @foreach ($partners as $partner)
                                @php
                                    $orgName = $partner->organization ?: $partner->name;
                                    $href = $partner->org_url ?: '#';
                                    $partnerLogo = $partner->org_logo_url ?: $partner->avatar_url;
                                @endphp
                                <a href="{{ $href }}" @if ($partner->org_url) target="_blank" rel="noopener" @endif
                                   class="lb-marquee__team lb-marquee__partner">
                                    <div class="lb-marquee__logo lb-marquee__logo--partner">
                                        @if ($partnerLogo)
                                            <img src="{{ $partnerLogo }}" alt="{{ $orgName }}" loading="lazy">
                                        @else
                                            <span class="lb-marquee__logo-initials">
                                                {{ \Illuminate\Support\Str::upper($partner->org_initials) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="lb-marquee__meta">
                                        <p class="lb-marquee__name">{{ $orgName }}</p>
                                        <p class="lb-marquee__tag">
                                            <span class="lb-marquee__badge">Partner</span>
                                            {{ $partner->name }}
                                        </p>
                                    </div>
                                    <div class="lb-marquee__sep"></div>
                                </a>
                            @endforeach
                        @endfor
                    </div>
                </div>
            </section>

            <style>
                .lb-marquee-wrap { background: linear-gradient(90deg, #fafafa, #fff, #fafafa); }
                .lb-marquee { width: 100%; overflow: hidden; }
                .lb-marquee__track {
                    display: inline-flex;
                    align-items: center;
                    white-space: nowrap;
                    animation: lb-marquee-scroll 200s linear infinite;
                    will-change: transform;
                }
                .lb-marquee:hover .lb-marquee__track { animation-play-state: paused; }
                .lb-marquee__team {
                    display: inline-flex;
                    align-items: center;
                    gap: 1rem;
                    padding: 0 0.75rem;
                    flex-shrink: 0;
                    text-decoration: none;
                }
                .lb-marquee__team:hover .lb-marquee__name { color: #C8102E; }
                .lb-marquee__logo {
                    width: 76px; height: 76px;
                    border-radius: 16px;
                    background: #fff;
                    box-shadow: 0 2px 8px rgba(0,0,0,.10);
                    display: flex; align-items: center; justify-content: center;
                    padding: 7px;
                    flex-shrink: 0;
                }
                .lb-marquee__logo img { max-width: 100%; max-height: 100%; object-fit: contain; }
                .lb-marquee__logo-initials {
                    font-family: 'Ubuntu', sans-serif;
                    font-weight: 700;
                    font-size: 1.35rem;
                    color: #C8102E;
                    letter-spacing: 0.05em;
                }
                .lb-marquee__meta { min-width: 0; max-width: 200px; }
                .lb-marquee__name {
                    font-family: 'Ubuntu', sans-serif;
                    font-weight: 700;
                    font-size: 1.05rem;
                    color: #1a1a1a;
                    line-height: 1.15;
                    transition: color .15s;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                .lb-marquee__tag {
                    font-size: 0.75rem;
                    color: #6b7280;
                    margin-top: 3px;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                .lb-marquee__members { display: inline-flex; align-items: center; }
                .lb-marquee__members > .lb-marquee__avatar + .lb-marquee__avatar { margin-left: -12px; }
                .lb-marquee__avatar {
                    width: 64px; height: 64px;
                    border-radius: 9999px;
                    background: #e5e7eb;
                    box-shadow: 0 0 0 3px #fff, 0 2px 4px rgba(0,0,0,.08);
                    overflow: hidden;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                }
                .lb-marquee__avatar img { width: 100%; height: 100%; object-fit: cover; }
                .lb-marquee__avatar span {
                    font-family: 'Ubuntu', sans-serif;
                    font-weight: 700;
                    font-size: 0.95rem;
                    color: #6b7280;
                }
                .lb-marquee__sep {
                    width: 1px;
                    height: 64px;
                    background: linear-gradient(180deg, transparent, #d1d5db, transparent);
                    margin: 0 0.75rem;
                }
                .lb-marquee__partner .lb-marquee__logo--partner {
                    background: linear-gradient(135deg, #FEF1F3, #fff);
                    box-shadow: 0 0 0 2px rgba(200,16,46,.15), 0 2px 8px rgba(0,0,0,.10);
                }
                .lb-marquee__badge {
                    display: inline-block;
                    background: #C8102E;
                    color: #fff;
                    font-size: 0.6rem;
                    font-weight: 700;
                    letter-spacing: 0.05em;
                    padding: 1px 6px;
                    border-radius: 4px;
                    margin-right: 4px;
                    text-transform: uppercase;
                }
                @keyframes lb-marquee-scroll {
                    0%   { transform: translateX(0); }
                    100% { transform: translateX(-50%); }
                }
            </style>
        @endif

        <div x-ref="scoreboard"
             :class="fs ? 'fixed inset-0 z-[100] aiu-bg-soft overflow-y-auto p-6 lg:p-10' : ''">

            <template x-if="fs">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="logo-plate w-14 h-14 rounded-xl flex items-center justify-center p-2">
                            <img src="{{ asset('img/aec-logo.png') }}" alt="AIU" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <p class="text-aiu-red uppercase tracking-[0.32em] text-xs font-bold">Live Scoreboard</p>
                            <h2 class="font-heading text-3xl font-bold text-aiu-ink-900">{{ $edition?->name }}</h2>
                        </div>
                    </div>
                    <button @click="toggleFs()" class="btn-soft px-4 py-2 rounded-xl flex items-center gap-2 text-sm font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Exit
                    </button>
                </div>
            </template>

            <div class="card-3d rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-aiu-ink-50/60 border-b border-aiu-line">
                                <th class="px-2 sm:px-4 py-4 text-left text-[10px] uppercase tracking-[0.18em] text-aiu-ink-400 font-bold w-12 sm:w-14">#</th>
                                <th class="px-2 sm:px-4 py-4 text-left text-[10px] uppercase tracking-[0.18em] text-aiu-ink-400 font-bold min-w-[140px] sm:min-w-[180px]">Team</th>
                                <th class="hidden sm:table-cell px-4 py-4 text-left text-[10px] uppercase tracking-[0.18em] text-aiu-ink-400 font-bold min-w-[160px]">Theme</th>

                                <th class="hidden md:table-cell px-4 py-4 text-center text-[10px] uppercase tracking-[0.18em] text-aiu-ink-700 font-bold min-w-[100px] border-l border-aiu-line/60">
                                    <p class="font-heading normal-case tracking-normal text-xs text-aiu-ink-900">Judges Avg</p>
                                    <p class="text-[9px] text-aiu-red font-bold mt-0.5">{{ ($judgesWeight * 100) }}%</p>
                                </th>
                                <th class="hidden md:table-cell px-4 py-4 text-center text-[10px] uppercase tracking-[0.18em] text-aiu-ink-700 font-bold min-w-[100px] border-l border-aiu-line/60">
                                    <p class="font-heading normal-case tracking-normal text-xs text-aiu-ink-900">Votes</p>
                                    <p class="text-[9px] text-aiu-red font-bold mt-0.5">{{ ($popularityWeight * 100) }}%</p>
                                </th>
                                <th class="px-3 sm:px-4 py-4 text-right text-[10px] uppercase tracking-[0.18em] text-aiu-red font-bold min-w-[90px] sm:min-w-[120px] border-l-2 border-aiu-red/30">Final</th>

                                @foreach ($judges as $i => $judge)
                                    <th class="hidden lg:table-cell px-3 py-4 text-center text-[10px] uppercase tracking-wider text-aiu-ink-700 font-bold min-w-[120px] border-l border-aiu-line/60 {{ $loop->first ? 'border-l-2 border-aiu-red/30' : '' }}"
                                        title="{{ $judge->name }}{{ $judge->institution ? ' · '.$judge->institution : '' }}">
                                        <p class="font-heading normal-case tracking-normal text-xs text-aiu-ink-900">Judge {{ $i + 1 }}</p>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($teams as $i => $team)
                                @php
                                    $isFirst = $i === 0;
                                    $isPodium = $i < 3;
                                    $isExpanded = $expandedTeamId === $team->id;
                                    // 3 fixed cols (#, Team, Theme) + N judge cols + 3 trailing (Judges Avg, Votes, Final)
                                    $totalCols = 6 + count($judges);
                                @endphp
                                <tr wire:click="toggleExpanded({{ $team->id }})"
                                    class="cursor-pointer border-b border-aiu-line hover:bg-aiu-ink-50/40 transition
                                           {{ $isFirst ? 'bg-gradient-to-r from-aiu-gold-50/60 via-white to-aiu-red-50/30' : '' }}
                                           {{ $isExpanded ? 'bg-aiu-red-50/40' : '' }}">
                                    <td class="px-2 sm:px-4 py-4 align-middle">
                                        <div class="flex items-center gap-2">
                                            <span class="font-heading inline-flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-xl text-sm font-bold tabular-nums
                                                         {{ $isFirst
                                                            ? 'btn-aiu'
                                                            : ($isPodium
                                                                ? 'chip-3d text-aiu-red'
                                                                : 'chip-3d text-aiu-ink-600') }}">
                                                {{ $i + 1 }}
                                            </span>
                                            <svg class="w-4 h-4 text-aiu-ink-400 transition-transform {{ $isExpanded ? 'rotate-180 text-aiu-red' : '' }}"
                                                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </td>

                                    <td class="px-2 sm:px-4 py-4 align-middle">
                                        <div class="flex items-start gap-2.5">
                                            @if ($team->logo_path)
                                                <img src="{{ asset('storage/' . $team->logo_path) }}" alt="{{ $team->name }}"
                                                     class="shrink-0 w-10 h-10 rounded-lg object-cover ring-1 ring-aiu-line">
                                            @else
                                                <div class="shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-aiu-red/15 to-aiu-gold/15 text-aiu-red font-heading font-bold text-base flex items-center justify-center">
                                                    {{ mb_substr($team->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <p class="font-heading font-bold text-aiu-ink-900 text-sm sm:text-base">{{ $team->name }}</p>
                                                {{-- On mobile, surface theme inline since the Theme column is hidden --}}
                                                <p class="sm:hidden text-[11px] text-aiu-ink-600 mt-0.5 truncate">{{ $team->theme?->name ?? '—' }}</p>
                                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                                    @if ($team->is_finalist)
                                                        <span class="text-[9px] uppercase tracking-[0.18em] px-1.5 py-0.5 rounded-md
                                                                     bg-aiu-red-50 text-aiu-red font-bold ring-1 ring-aiu-red/20">
                                                            Finalist
                                                        </span>
                                                    @endif
                                                    @if ($isFirst && $team->avg_total !== null)
                                                        <span class="text-[9px] uppercase tracking-[0.18em] px-1.5 py-0.5 rounded-md
                                                                     bg-aiu-gold-50 text-aiu-gold-600 font-bold ring-1 ring-aiu-gold/30">
                                                            Leading
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <a href="{{ route('teams.show', $team->slug) }}"
                                               wire:click.stop
                                               title="View team submission"
                                               class="shrink-0 inline-flex items-center justify-center w-7 h-7 rounded-lg text-aiu-ink-400 hover:text-aiu-red hover:bg-aiu-red-50 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 3h7v7M21 3l-9 9M21 14v5a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h5"/></svg>
                                            </a>
                                        </div>
                                    </td>

                                    <td class="hidden sm:table-cell px-4 py-4 align-middle text-aiu-ink-600 text-xs">
                                        {{ $team->theme?->name ?? '—' }}
                                    </td>

                                    {{-- Judges Avg --}}
                                    <td class="hidden md:table-cell px-4 py-4 text-center align-middle border-l border-aiu-line/60">
                                        @if ($team->avg_total !== null)
                                            <p class="font-heading text-lg font-bold text-aiu-ink-900 tabular-nums leading-none">
                                                {{ number_format($team->avg_total, 2) }}
                                            </p>
                                            <p class="text-[10px] text-aiu-ink-400 mt-0.5">{{ $team->judge_count }} judges</p>
                                        @else
                                            <span class="text-aiu-ink-400 text-xs italic">—</span>
                                        @endif
                                    </td>

                                    {{-- Votes --}}
                                    <td class="hidden md:table-cell px-4 py-4 text-center align-middle border-l border-aiu-line/60">
                                        @if ($team->vote_count > 0)
                                            <p class="font-heading text-lg font-bold text-aiu-ink-900 tabular-nums leading-none">
                                                {{ $team->vote_count }}
                                            </p>
                                            <p class="text-[10px] text-aiu-red font-semibold mt-0.5 tabular-nums" title="Normalized popularity score (top team = 10.00)">
                                                {{ number_format((float) $team->popularity, 2) }}/10
                                            </p>
                                        @else
                                            <p class="text-aiu-ink-400 text-xs italic">no votes</p>
                                        @endif
                                    </td>

                                    {{-- Final score --}}
                                    <td class="px-3 sm:px-4 py-4 align-middle text-right border-l-2 border-aiu-red/30
                                               {{ $isFirst ? 'bg-aiu-red-50/50' : '' }}">
                                        @if ($team->final_score !== null)
                                            <p class="font-heading text-xl sm:text-2xl font-bold tabular-nums leading-none
                                                      {{ $isFirst ? 'text-aiu-red' : 'text-aiu-ink-900' }}">
                                                {{ number_format($team->final_score, 2) }}
                                            </p>
                                            <p class="text-[10px] text-aiu-ink-400 font-semibold mt-1">/ 10.00</p>
                                        @else
                                            <span class="text-aiu-ink-400 text-xs italic">awaiting</span>
                                        @endif
                                    </td>

                                    @foreach ($judges as $i => $judge)
                                        @php $cell = $scoreLookup[$team->id][$judge->id] ?? null; @endphp
                                        <td class="hidden lg:table-cell px-3 py-4 text-center align-middle border-l border-aiu-line/60 {{ $loop->first ? 'border-l-2 border-aiu-red/30' : '' }}">
                                            <p class="text-[10px] uppercase tracking-[0.14em] text-aiu-ink-400 font-bold leading-tight">Judge {{ $i + 1 }}</p>
                                            <p class="font-heading text-[11px] font-semibold text-aiu-ink-900 truncate leading-tight mt-0.5" title="{{ $judge->name }}{{ $judge->institution ? ' · '.$judge->institution : '' }}">
                                                {{ $judge->name }}
                                            </p>
                                            @if ($cell)
                                                <p class="font-heading text-xl font-bold text-aiu-ink-900 tabular-nums leading-none mt-1.5">
                                                    {{ number_format((float) $cell->weighted_total, 2) }}
                                                </p>
                                                @if (filled($cell->comment))
                                                    <p class="mt-1.5 text-[10px] text-aiu-ink-600 italic line-clamp-2 leading-tight"
                                                       title="{{ $cell->comment }}">
                                                        “{{ $cell->comment }}”
                                                    </p>
                                                @endif
                                            @else
                                                <span class="block mt-1.5 text-aiu-ink-400 text-xs italic">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                {{-- Expanded detail row: per-judge per-criterion breakdown --}}
                                @if ($isExpanded)
                                    <tr class="bg-aiu-ink-50/50 border-b-2 border-aiu-red/20">
                                        <td colspan="{{ $totalCols }}" class="px-4 lg:px-8 py-6">
                                            <div class="flex items-center gap-3 mb-4">
                                                <div class="h-px flex-1 bg-aiu-line"></div>
                                                <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-red font-bold">
                                                    {{ $team->name }} · Detailed Breakdown
                                                </p>
                                                <div class="h-px flex-1 bg-aiu-line"></div>
                                            </div>

                                            @if ($team->tagline)
                                                <p class="text-sm text-aiu-ink-700 italic mb-4 max-w-3xl mx-auto text-center">
                                                    {{ $team->tagline }}
                                                </p>
                                            @endif

                                            @php $cov = $team->role_coverage; @endphp
                                            <div class="flex flex-wrap items-center justify-center gap-2 mb-5">
                                                <span class="text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mr-1">Roles:</span>
                                                @foreach (\App\Models\User::ROLE_CATEGORIES as $key => $label)
                                                    @php $has = in_array($key, $cov['filled'], true); @endphp
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ring-1
                                                                 {{ $has
                                                                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                                                    : 'bg-aiu-red-50 text-aiu-red ring-aiu-red/20' }}">
                                                        @if ($has)
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                        @else
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        @endif
                                                        {{ $label }}
                                                    </span>
                                                @endforeach
                                                @if (!empty($cov['missing']))
                                                    <span class="text-[10px] text-aiu-red font-semibold ml-1">· Missing {{ count($cov['missing']) }} role{{ count($cov['missing']) === 1 ? '' : 's' }}</span>
                                                @endif
                                            </div>

                                            <div class="overflow-x-auto card-3d rounded-xl">
                                                <table class="w-full text-sm">
                                                    <thead class="bg-white">
                                                        <tr class="border-b border-aiu-line">
                                                            <th class="px-4 py-3 text-left text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold min-w-[180px]">Judge</th>
                                                            @foreach ($criteria as $key => $info)
                                                                <th class="px-3 py-3 text-center text-[10px] uppercase tracking-wider text-aiu-ink-700 font-bold border-l border-aiu-line/50">
                                                                    <p class="font-heading text-xs text-aiu-ink-900 normal-case tracking-normal">{{ $info['label'] }}</p>
                                                                    <p class="text-[9px] text-aiu-red font-bold mt-0.5">{{ ($info['weight'] * 100) }}%</p>
                                                                </th>
                                                            @endforeach
                                                            <th class="px-4 py-3 text-right text-[10px] uppercase tracking-wider text-aiu-red font-bold border-l-2 border-aiu-red/30 min-w-[90px]">Weighted</th>
                                                            <th class="px-4 py-3 text-left text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold border-l border-aiu-line/50 min-w-[260px]">Comment</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($judges as $judge)
                                                            @php $cell = $scoreLookup[$team->id][$judge->id] ?? null; @endphp
                                                            <tr class="border-b border-aiu-line last:border-b-0 hover:bg-aiu-ink-50/40">
                                                                <td class="px-4 py-3 align-middle">
                                                                    <p class="font-heading font-bold text-aiu-ink-900 text-sm">{{ $judge->name }}</p>
                                                                    @if ($judge->institution)
                                                                        <p class="text-[10px] text-aiu-ink-400 truncate">{{ $judge->institution }}</p>
                                                                    @endif
                                                                </td>
                                                                @foreach ($criteria as $key => $info)
                                                                    @php $val = $cell ? (float) $cell->{$key} : null; @endphp
                                                                    <td class="px-3 py-3 text-center align-middle border-l border-aiu-line/50">
                                                                        @if ($val !== null)
                                                                            <p class="font-mono font-bold text-aiu-ink-900 tabular-nums text-base">
                                                                                {{ number_format($val, 1) }}
                                                                            </p>
                                                                            <div class="mt-1 h-1 rounded-full bg-aiu-ink-100 overflow-hidden">
                                                                                <div class="h-full rounded-full bg-gradient-to-r from-aiu-red to-aiu-red-700"
                                                                                     style="width: {{ $val * 10 }}%"></div>
                                                                            </div>
                                                                        @else
                                                                            <span class="text-aiu-ink-400 text-xs italic">—</span>
                                                                        @endif
                                                                    </td>
                                                                @endforeach
                                                                <td class="px-4 py-3 align-middle text-right border-l-2 border-aiu-red/30">
                                                                    @if ($cell)
                                                                        <p class="font-heading text-xl font-bold text-aiu-red tabular-nums">
                                                                            {{ number_format((float) $cell->weighted_total, 2) }}
                                                                        </p>
                                                                    @else
                                                                        <span class="text-aiu-ink-400 italic">—</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-3 align-middle border-l border-aiu-line/50">
                                                                    @if ($cell && filled($cell->comment))
                                                                        <p class="text-xs text-aiu-ink-700 italic">
                                                                            <span class="text-aiu-red font-bold not-italic">"</span>{{ $cell->comment }}<span class="text-aiu-red font-bold not-italic">"</span>
                                                                        </p>
                                                                    @else
                                                                        <span class="text-aiu-ink-400 text-xs italic">No comment</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="{{ 6 + count($judges) }}" class="px-6 py-20 text-center">
                                        <p class="font-heading text-xl font-bold text-aiu-ink-900">No teams registered yet</p>
                                        <p class="mt-2 text-sm text-aiu-ink-600">Once ACIE staff register teams in the admin panel, they will appear here in real time.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3 text-center text-[11px] text-aiu-ink-400 space-y-1">
                <p>
                    <span class="text-aiu-ink-700 font-semibold">Tip:</span> click any team row to see the full per-criterion breakdown.
                </p>
                <p>
                    <span class="font-semibold text-aiu-ink-700">Final score</span> =
                    Judges Avg × <span class="text-aiu-red font-bold">{{ ($judgesWeight * 100) }}%</span>
                    + Popularity × <span class="text-aiu-red font-bold">{{ ($popularityWeight * 100) }}%</span>
                    <span class="text-aiu-ink-300">·</span>
                    Popularity normalized so the most-voted team = 10.00
                </p>
                <p>
                    Judge weights: Innovation 20% · Technical 25% · Impact 20% · UX 15% · Pitch 10% · Business 10%
                </p>
            </div>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-aiu-ink-400">
            <p class="flex items-center gap-2">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Auto-refreshes every 5 seconds
            </p>
            <p class="tabular-nums">
                Server time: <span class="text-aiu-ink-700 font-semibold">{{ $serverNow->format('Y-m-d H:i:s') }}</span>
            </p>
        </div>


    </section>
</div>
