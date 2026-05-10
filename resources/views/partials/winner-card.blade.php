@php
    /** @var ?\App\Models\AwardWinner $winner */
    $tone    = $tone ?? 'gold';
    $tall    = $tall ?? false;
    $compact = $compact ?? false;
    $team    = $winner?->team;
    $slotMeta = $winner ? (\App\Models\AwardWinner::SLOTS[$winner->slot] ?? null) : null;

    $tones = [
        'gold'    => ['ring' => 'ring-aiu-gold/40', 'bg' => 'from-aiu-gold-50 to-white',  'medal' => 'bg-gradient-to-br from-aiu-gold-300 to-aiu-gold-600 text-white shadow-gold', 'accent' => 'text-aiu-gold-600'],
        'silver'  => ['ring' => 'ring-aiu-ink-200', 'bg' => 'from-aiu-ink-50 to-white',   'medal' => 'bg-gradient-to-br from-aiu-ink-200 to-aiu-ink-600 text-white shadow-card',  'accent' => 'text-aiu-ink-700'],
        'bronze'  => ['ring' => 'ring-orange-200',  'bg' => 'from-orange-50 to-white',    'medal' => 'bg-gradient-to-br from-orange-300 to-orange-700 text-white shadow-card',     'accent' => 'text-orange-700'],
        'indigo'  => ['ring' => 'ring-indigo-200',  'bg' => 'from-indigo-50/60 to-white', 'medal' => 'bg-gradient-to-br from-indigo-400 to-indigo-700 text-white shadow-card',     'accent' => 'text-indigo-700'],
        'emerald' => ['ring' => 'ring-emerald-200', 'bg' => 'from-emerald-50/60 to-white','medal' => 'bg-gradient-to-br from-emerald-400 to-emerald-700 text-white shadow-card',   'accent' => 'text-emerald-700'],
        'pink'    => ['ring' => 'ring-pink-200',    'bg' => 'from-pink-50/60 to-white',   'medal' => 'bg-gradient-to-br from-pink-400 to-pink-700 text-white shadow-card',         'accent' => 'text-pink-700'],
    ];
    $t = $tones[$tone] ?? $tones['gold'];
@endphp

<div class="relative rounded-2xl bg-gradient-to-br {{ $t['bg'] }} ring-1 {{ $t['ring'] }} shadow-card overflow-hidden
            {{ $tall ? 'md:scale-[1.06] md:py-9' : 'md:py-7' }} px-5 py-7
            {{ $tall ? 'md:order-2 order-1' : '' }}
            transition hover:-translate-y-0.5 hover:shadow-lifted">
    @if ($team)
        <div class="flex flex-col items-center text-center gap-3">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full text-2xl font-bold {{ $t['medal'] }}">
                {{ $slotMeta['medal'] ?? '🏅' }}
            </div>
            <p class="text-[10px] uppercase tracking-[0.28em] font-bold {{ $t['accent'] }}">
                {{ $slotMeta['label'] ?? '' }}
            </p>

            {{-- Logo --}}
            <div class="w-{{ $tall ? '24' : '20' }} h-{{ $tall ? '24' : '20' }} rounded-2xl bg-white ring-1 ring-aiu-line shadow-soft p-2 flex items-center justify-center">
                @if ($team->logo_path)
                    <img src="{{ $team->logo_url }}" alt="{{ $team->name }}" class="max-w-full max-h-full object-contain">
                @else
                    <span class="font-heading font-bold {{ $tall ? 'text-3xl' : 'text-2xl' }} {{ $t['accent'] }} tracking-wide">
                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($team->name, 0, 2)) }}
                    </span>
                @endif
            </div>

            <a href="{{ route('teams.show', $team->slug) }}"
               class="font-heading font-bold {{ $tall ? 'text-2xl lg:text-3xl' : 'text-xl' }} text-aiu-ink-900 hover:text-aiu-red transition leading-tight">
                {{ $team->name }}
            </a>

            @if (!$compact && $team->tagline)
                <p class="text-xs text-aiu-ink-600 italic line-clamp-2 max-w-[18rem]">{{ $team->tagline }}</p>
            @endif

            @if ($team->relationLoaded('members') && $team->members->isNotEmpty())
                <div class="flex -space-x-2 mt-1">
                    @foreach ($team->members->take(5) as $m)
                        <div class="w-7 h-7 rounded-full ring-2 ring-white bg-aiu-ink-100 overflow-hidden flex items-center justify-center text-[10px] font-bold text-aiu-ink-600"
                             title="{{ $m->name }}">
                            @if ($m->avatar_path)
                                <img src="{{ $m->avatar_url }}" alt="{{ $m->name }}" class="w-full h-full object-cover">
                            @else
                                {{ $m->initials ?? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($m->name, 0, 1)) }}
                            @endif
                        </div>
                    @endforeach
                    @if ($team->members->count() > 5)
                        <span class="w-7 h-7 rounded-full ring-2 ring-white bg-aiu-ink-100 text-[10px] font-bold text-aiu-ink-600 flex items-center justify-center">
                            +{{ $team->members->count() - 5 }}
                        </span>
                    @endif
                </div>
            @endif
        </div>
    @else
        <div class="flex flex-col items-center text-center gap-3 opacity-60 py-3">
            <div class="w-14 h-14 rounded-full bg-aiu-ink-100 ring-1 ring-aiu-line"></div>
            <p class="text-[10px] uppercase tracking-[0.28em] font-bold text-aiu-ink-400">{{ $slotMeta['label'] ?? '—' }}</p>
            <p class="text-aiu-ink-400 text-sm italic">To be announced</p>
        </div>
    @endif
</div>
