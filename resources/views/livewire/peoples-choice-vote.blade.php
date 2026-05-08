<div class="max-w-7xl mx-auto px-6 py-12"
     x-data="voteFingerprint()"
     x-init="init()">

    {{-- Page-wide honeypot: any bot scraping the form will eagerly fill this. --}}
    <div aria-hidden="true" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">
        <label>Website (leave blank)</label>
        <input type="text" wire:model="hp_website" tabindex="-1" autocomplete="off">
    </div>

    {{-- Browser-side device fingerprint. Stable across IP changes. Stored in
         localStorage so even cookie-clearing doesn't generate a fresh ID. --}}
    <input type="hidden" wire:model.live="deviceFingerprint" :value="fp">

    <script>
        function voteFingerprint() {
            return {
                fp: '',
                async init() {
                    try {
                        const stored = localStorage.getItem('aiu_vote_fp');
                        if (stored && /^[a-f0-9]{64}$/.test(stored)) {
                            this.fp = stored;
                            this.$dispatch('input'); // sync to Livewire
                            return;
                        }
                        const generated = await this.generate();
                        this.fp = generated;
                        try { localStorage.setItem('aiu_vote_fp', generated); } catch (e) {}
                        this.$dispatch('input');
                    } catch (e) {
                        // Fingerprinting failed — server still has IP/cookie checks
                    }
                },
                async generate() {
                    // Canvas fingerprint
                    let canvasData = '';
                    try {
                        const c = document.createElement('canvas');
                        c.width = 240; c.height = 60;
                        const ctx = c.getContext('2d');
                        ctx.textBaseline = 'top';
                        ctx.font = '14px Arial';
                        ctx.fillStyle = '#f60';
                        ctx.fillRect(125, 1, 62, 20);
                        ctx.fillStyle = '#069';
                        ctx.fillText('AIU Hackathon \u{1F3AF}', 2, 15);
                        ctx.fillStyle = 'rgba(102,204,0,0.7)';
                        ctx.fillText('AIU Hackathon \u{1F3AF}', 4, 17);
                        canvasData = c.toDataURL();
                    } catch (e) {}

                    // WebGL renderer (vendor-specific GPU info)
                    let gpu = '';
                    try {
                        const gl = document.createElement('canvas').getContext('webgl');
                        if (gl) {
                            const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
                            if (debugInfo) {
                                gpu = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL) + '|'
                                    + gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
                            }
                        }
                    } catch (e) {}

                    const components = [
                        navigator.userAgent || '',
                        navigator.language || '',
                        navigator.languages ? navigator.languages.join(',') : '',
                        navigator.platform || '',
                        screen.width + 'x' + screen.height + 'x' + screen.colorDepth,
                        new Date().getTimezoneOffset(),
                        Intl.DateTimeFormat().resolvedOptions().timeZone || '',
                        navigator.hardwareConcurrency || 0,
                        navigator.deviceMemory || 0,
                        navigator.maxTouchPoints || 0,
                        canvasData,
                        gpu,
                    ].join('||');

                    const buf = new TextEncoder().encode(components);
                    const hash = await crypto.subtle.digest('SHA-256', buf);
                    return Array.from(new Uint8Array(hash))
                        .map(b => b.toString(16).padStart(2, '0')).join('');
                },
            };
        }
    </script>

    <header class="mb-8 text-center">
        <p class="inline-flex items-center gap-2 px-3 py-1 rounded-full chip-3d
                  text-aiu-red uppercase tracking-[0.22em] text-[10px] font-bold mb-4">
            People's Choice
        </p>
        <h1 class="font-heading text-4xl font-bold text-aiu-ink-900">Cast your vote</h1>
        <p class="mt-3 text-sm text-aiu-ink-600 max-w-xl mx-auto">
            Browse each team's work — watch the demo, check the deck, read their problem statement,
            then vote for the one that impressed you most. One vote per person.
        </p>
    </header>

    @if ($votingPaused)
        <div class="max-w-xl mx-auto mb-6 px-5 py-4 rounded-xl card-3d bg-amber-50 ring-1 ring-amber-200 text-center">
            <div class="text-amber-900 font-heading font-bold text-lg mb-1">⏸️ Voting paused</div>
            <p class="text-sm text-amber-800">Voting has been paused by the organizers. Please check back later.</p>
        </div>
    @elseif ($voteRequiresLogin && !auth()->check())
        <div class="max-w-xl mx-auto mb-6 px-5 py-4 rounded-xl card-3d bg-aiu-red-50 ring-1 ring-aiu-red/30 text-center">
            <div class="text-aiu-red font-heading font-bold text-lg mb-1">🔐 Sign in required</div>
            <p class="text-sm text-aiu-ink-700 mb-3">Voting now requires a registered account. Sign in to cast your vote.</p>
            <a href="{{ route('login') }}" class="btn-aiu inline-flex items-center px-5 py-2 rounded-lg font-bold text-xs uppercase tracking-wider">
                Sign in to vote
            </a>
        </div>
    @endif

    @if ($message)
        <div class="max-w-md mx-auto mb-6 px-4 py-3 rounded-lg card-3d
                    {{ str_starts_with($message, 'Thanks') ? 'bg-gradient-to-r from-emerald-50 to-white text-emerald-800' : 'bg-aiu-red-50 text-aiu-red' }}
                    text-sm text-center font-semibold">
            {{ $message }}
        </div>
    @endif

    @if ($myVoteTeamId)
        {{-- Already voted state --}}
        <div class="card-3d rounded-2xl p-8 max-w-lg mx-auto text-center">
            <div class="logo-plate inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4 p-3">
                <svg class="w-full h-full text-aiu-red" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
            </div>
            <p class="text-aiu-red uppercase tracking-[0.22em] text-[10px] font-bold">Vote recorded</p>
            <h2 class="font-heading text-2xl font-bold text-aiu-ink-900 mt-2">Thanks @if ($voterName), {{ $voterName }}@endif!</h2>
            <p class="mt-3 text-aiu-ink-600">Your vote went to <span class="font-bold text-aiu-ink-900">{{ $myVoteTeamName }}</span>.</p>
            <p class="mt-2 text-xs text-aiu-ink-400">Results revealed at the awards ceremony.</p>
            <a href="{{ route('home') }}" class="btn-soft inline-flex mt-6 px-5 py-2.5 rounded-lg font-semibold text-sm">
                ← Back to leaderboard
            </a>
        </div>

    @elseif (!auth()->check() && $askForIdentity)
        {{-- Identity step for guests --}}
        <div class="card-3d rounded-2xl p-8 max-w-lg mx-auto">
            <p class="text-aiu-red uppercase tracking-[0.22em] text-[10px] font-bold mb-2">Step 1 of 2</p>
            <h2 class="font-heading text-xl font-bold text-aiu-ink-900">Tell us who you are</h2>
            <p class="mt-1 text-sm text-aiu-ink-600">Just so we count one vote per person.</p>

            <form wire:submit="startVoting" class="mt-5 space-y-4">
                {{-- Honeypot — invisible to humans, naive bots fill it. --}}
                <div aria-hidden="true" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">
                    <label>Website (leave blank)</label>
                    <input type="text" wire:model="hp_website" tabindex="-1" autocomplete="off">
                </div>
                <div>
                    <label class="block text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">Your name</label>
                    <input type="text" wire:model="voterName" required class="input-3d w-full px-4 py-3 rounded-lg" placeholder="e.g. Mona Khaled">
                    @error('voterName') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">Your email</label>
                    <input type="email" wire:model="voterEmail" required class="input-3d w-full px-4 py-3 rounded-lg" placeholder="you@example.com">
                    @error('voterEmail') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                    <p class="mt-1 text-[11px] text-aiu-ink-400">We use this only to prevent duplicate votes. No marketing.</p>
                </div>
                <button type="submit" class="btn-aiu w-full py-3 rounded-lg font-bold uppercase tracking-wider text-sm">
                    Continue → Browse teams
                </button>
            </form>
        </div>

    @else
        {{-- Voting grid: team cards with full work preview --}}
        @if (!auth()->check())
            <p class="mb-6 text-center text-xs text-aiu-ink-400">
                Voting as <span class="text-aiu-ink-700 font-semibold">{{ $voterName }}</span>
                ({{ $voterEmail }})
            </p>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($teams as $team)
                @php
                    $sub = $team->submission;
                    $teamDrafts = $drafts->get($team->id, collect());
                    $problem  = optional($teamDrafts->get('problem'))->body;
                    $solution = optional($teamDrafts->get('solution'))->body;
                @endphp

                <div class="card-3d rounded-2xl overflow-hidden flex flex-col">
                    {{-- Team header --}}
                    <div class="p-5 border-b border-aiu-line bg-gradient-to-b from-white to-aiu-ink-50/40">
                        <p class="text-[10px] uppercase tracking-[0.22em] text-aiu-red font-bold">
                            {{ $team->theme?->name ?? 'No theme' }}
                        </p>
                        <div class="flex items-start justify-between gap-3 mt-1">
                            <h3 class="font-heading font-bold text-xl text-aiu-ink-900">{{ $team->name }}</h3>
                            <a href="{{ route('teams.show', $team->slug) }}"
                               title="View full submission"
                               class="shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] uppercase tracking-wider font-bold text-aiu-ink-600 hover:text-aiu-red hover:bg-aiu-red-50 transition">
                                View
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 3h7v7M21 3l-9 9"/></svg>
                            </a>
                        </div>
                        @if ($team->is_finalist)
                            <span class="inline-block mt-2 text-[9px] uppercase tracking-[0.18em] px-1.5 py-0.5 rounded-md
                                         bg-aiu-red-50 text-aiu-red font-bold ring-1 ring-aiu-red/20">
                                Finalist
                            </span>
                        @endif
                    </div>

                    {{-- Problem & Solution preview --}}
                    <div class="p-5 flex-1 space-y-4">
                        @if ($problem)
                            <div>
                                <p class="text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-1">Problem</p>
                                <p class="text-sm text-aiu-ink-700 line-clamp-3 leading-relaxed">{{ $problem }}</p>
                            </div>
                        @endif

                        @if ($solution)
                            <div>
                                <p class="text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-1">Solution</p>
                                <p class="text-sm text-aiu-ink-700 line-clamp-4 leading-relaxed">{{ $solution }}</p>
                            </div>
                        @endif

                        @if (!$problem && !$solution)
                            <p class="text-xs text-aiu-ink-400 italic">This team hasn't written a public summary yet.</p>
                        @endif
                    </div>

                    {{-- Submission links --}}
                    @if ($sub && ($sub->video_url || $sub->slides_url || $sub->repo_url))
                        <div class="px-5 pb-4 flex flex-wrap gap-2">
                            @if ($sub->video_url)
                                <a href="{{ $sub->video_url }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg
                                          bg-aiu-red-50 text-aiu-red ring-1 ring-aiu-red/20
                                          text-[11px] font-semibold hover:bg-aiu-red hover:text-white transition">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
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

                    {{-- Vote button --}}
                    <div class="p-5 pt-3 border-t border-aiu-line bg-aiu-ink-50/30">
                        <button wire:click="vote({{ $team->id }})"
                                wire:confirm="Vote for {{ $team->name }}? This is your only vote and cannot be changed."
                                class="btn-aiu w-full py-3 rounded-lg font-bold uppercase tracking-wider text-xs">
                            Vote for {{ $team->name }}
                        </button>
                    </div>
                </div>
            @empty
                <p class="col-span-full text-center text-aiu-ink-400 py-12">No teams available to vote for.</p>
            @endforelse
        </div>
    @endif

    <p class="mt-10 text-center text-[11px] text-aiu-ink-400">
        Want to share this with attendees?
        <a href="{{ route('vote.qr') }}" class="text-aiu-red font-semibold hover:underline">show full-screen QR ↗</a>
    </p>
</div>
