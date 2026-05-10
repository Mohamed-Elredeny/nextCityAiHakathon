{{--
    Winners hero — shows the announced 6 winners for the current edition.
    Renders nothing when the table is empty (so it disappears automatically
    until the seeder runs / the admin sets winners).

    Required vars:
      $winners  — Collection<string, ?AwardWinner>  keyed by slot (see AwardWinner::SLOTS)
      $size     — 'lg' (full hero) | 'md' (compact). Default 'lg'.
--}}
@php
    $size = $size ?? 'lg';
    $hasAny = $winners->filter()->isNotEmpty();
    $first  = $winners[\App\Models\AwardWinner::SLOT_FIRST]  ?? null;
    $second = $winners[\App\Models\AwardWinner::SLOT_SECOND] ?? null;
    $third  = $winners[\App\Models\AwardWinner::SLOT_THIRD]  ?? null;
    $bestAi = $winners[\App\Models\AwardWinner::SLOT_BEST_AI] ?? null;
    $impact = $winners[\App\Models\AwardWinner::SLOT_MOST_IMPACTFUL] ?? null;
    $choice = $winners[\App\Models\AwardWinner::SLOT_PEOPLES_CHOICE] ?? null;
@endphp

@if ($hasAny)
<section class="winners-hero relative overflow-hidden rounded-3xl mb-8 bg-gradient-to-br from-aiu-gold-50 via-white to-aiu-red-50/60 ring-1 ring-aiu-gold/20 shadow-lifted">
    {{-- Decorative confetti dots --}}
    <div class="absolute inset-0 pointer-events-none opacity-50"
         style="background-image:
            radial-gradient(circle at 12% 22%, #cf9040 1.5px, transparent 2px),
            radial-gradient(circle at 88% 18%, #C8102E 1.5px, transparent 2px),
            radial-gradient(circle at 30% 80%, #C8102E 1.5px, transparent 2px),
            radial-gradient(circle at 70% 75%, #cf9040 1.5px, transparent 2px);
            background-size: 220px 220px;"></div>

    <div class="relative px-6 lg:px-10 pt-8 pb-10">
        <div class="text-center mb-7">
            <p class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white shadow-soft ring-1 ring-aiu-gold/30
                      text-aiu-gold-600 uppercase tracking-[0.28em] text-[11px] font-bold">
                <span class="text-sm">🏆</span>
                Official Results · {{ $winners->first()?->edition?->name ?? 'Hackathon' }}
            </p>
            <h2 class="mt-4 font-heading font-bold {{ $size === 'lg' ? 'text-3xl lg:text-5xl' : 'text-2xl lg:text-3xl' }} text-aiu-ink-900 leading-tight">
                Meet the Winners
            </h2>
            <p class="mt-2 text-sm lg:text-base text-aiu-ink-600 max-w-2xl mx-auto">
                Congratulations to every team that pitched — and the six standing on the podium below.
            </p>
        </div>

        {{-- Podium row: 2nd · 1st · 3rd (Olympic style) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6 items-end mb-6">
            {{-- 2nd --}}
            @include('partials.winner-card', ['winner' => $second, 'tone' => 'silver',  'tall' => false])
            {{-- 1st --}}
            @include('partials.winner-card', ['winner' => $first,  'tone' => 'gold',    'tall' => true])
            {{-- 3rd --}}
            @include('partials.winner-card', ['winner' => $third,  'tone' => 'bronze',  'tall' => false])
        </div>

        {{-- Special awards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @include('partials.winner-card', ['winner' => $bestAi, 'tone' => 'indigo',  'tall' => false, 'compact' => true])
            @include('partials.winner-card', ['winner' => $impact, 'tone' => 'emerald', 'tall' => false, 'compact' => true])
            @include('partials.winner-card', ['winner' => $choice, 'tone' => 'pink',    'tall' => false, 'compact' => true])
        </div>
    </div>
</section>
@endif
