<div>
    <section class="max-w-7xl mx-auto px-6 lg:px-8 py-10 lg:py-16">

        <div class="text-center mb-8">
            <p class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full chip-3d
                      text-aiu-red uppercase tracking-[0.22em] text-[11px] font-bold">
                <span class="text-sm">🏆</span>
                {{ $edition?->name ?? 'Hackathon' }} · Final Results
            </p>
            <h1 class="mt-5 font-heading font-bold text-4xl lg:text-6xl leading-[1.05] tracking-tight text-aiu-ink-900">
                The Winners
            </h1>
            <p class="mt-4 text-base lg:text-lg text-aiu-ink-600 max-w-2xl mx-auto">
                Celebrating the six teams who stood out across the rubric, the public vote, and the special-award juries.
            </p>
        </div>

        @include('partials.winners-hero', ['winners' => $winners])

        <div class="mt-10 text-center">
            <a href="{{ route('leaderboard') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl btn-soft text-sm font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M9 6l-6 6 6 6"/>
                </svg>
                Back to live scoreboard
            </a>
        </div>
    </section>
</div>
