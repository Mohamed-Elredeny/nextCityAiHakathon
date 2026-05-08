<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Round picker --}}
        <div class="flex items-center gap-3">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Round:</span>
            <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden">
                <button type="button" wire:click="setRound('round1')"
                        class="px-4 py-2 text-sm font-semibold {{ $round === 'round1' ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200' }}">
                    Round 1
                </button>
                <button type="button" wire:click="setRound('finals')"
                        class="px-4 py-2 text-sm font-semibold border-l border-gray-300 dark:border-gray-600 {{ $round === 'finals' ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200' }}">
                    Finals
                </button>
            </div>
            <span class="text-xs text-gray-500">
                Edition: <strong>{{ $edition?->name ?? 'none' }}</strong>
            </span>
        </div>

        {{-- 6 Winners --}}
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Final winners (no team repeats)</h3>
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
                Priority: 1st → 2nd → 3rd (leaderboard) → People's Choice → Best AI Innovation → Most Impactful Solution.
                A team that wins one slot is removed from contention for the rest.
            </p>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @php
                    $slots = [
                        'first_place'              => ['1st Place', 'bg-yellow-100 text-yellow-900 ring-yellow-300 dark:bg-yellow-900/40 dark:text-yellow-100'],
                        'second_place'             => ['2nd Place', 'bg-gray-100 text-gray-900 ring-gray-300 dark:bg-gray-700 dark:text-gray-100'],
                        'third_place'              => ['3rd Place', 'bg-orange-100 text-orange-900 ring-orange-300 dark:bg-orange-900/40 dark:text-orange-100'],
                        'peoples_choice'           => ["People's Choice", 'bg-pink-100 text-pink-900 ring-pink-300 dark:bg-pink-900/40 dark:text-pink-100'],
                        'best_ai_innovation'       => ['Best AI Innovation', 'bg-indigo-100 text-indigo-900 ring-indigo-300 dark:bg-indigo-900/40 dark:text-indigo-100'],
                        'most_impactful_solution'  => ['Most Impactful', 'bg-emerald-100 text-emerald-900 ring-emerald-300 dark:bg-emerald-900/40 dark:text-emerald-100'],
                    ];
                @endphp

                @foreach ($slots as $slot => [$label, $cls])
                    <div class="p-4 rounded-lg ring-1 {{ $cls }}">
                        <p class="text-[10px] uppercase tracking-wider font-bold opacity-70">{{ $label }}</p>
                        <p class="mt-1 font-bold text-lg">
                            {{ $winners[$slot]['team']?->name ?? '—' }}
                        </p>
                        <p class="text-xs opacity-80 mt-0.5">
                            {{ $winners[$slot]['metric_label'] }}:
                            <span class="tabular-nums font-semibold">
                                {{ $winners[$slot]['metric'] !== null ? (is_float($winners[$slot]['metric']) ? number_format($winners[$slot]['metric'], 2) : $winners[$slot]['metric']) : '—' }}
                            </span>
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Detailed counts --}}
        <div class="grid lg:grid-cols-3 gap-4">

            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2 text-sm">People's Choice — public votes</h4>
                @if ($publicCounts->isEmpty())
                    <p class="text-xs text-gray-500 italic">No votes yet.</p>
                @else
                    <ol class="text-xs space-y-1">
                        @foreach ($publicCounts as $row)
                            <li class="flex justify-between gap-3">
                                <span class="truncate">{{ $row->team?->name ?? "Team #{$row->team_id}" }}</span>
                                <span class="tabular-nums font-semibold">{{ $row->c }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>

            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2 text-sm">Best AI Innovation — restricted votes</h4>
                @if ($bestAiCounts->isEmpty())
                    <p class="text-xs text-gray-500 italic">No votes yet.</p>
                @else
                    <ol class="text-xs space-y-1">
                        @foreach ($bestAiCounts as $row)
                            <li class="flex justify-between gap-3">
                                <span class="truncate">{{ $row->team?->name ?? "Team #{$row->team_id}" }}</span>
                                <span class="tabular-nums font-semibold">{{ $row->c }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>

            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2 text-sm">Most Impactful — restricted votes</h4>
                @if ($impactCounts->isEmpty())
                    <p class="text-xs text-gray-500 italic">No votes yet.</p>
                @else
                    <ol class="text-xs space-y-1">
                        @foreach ($impactCounts as $row)
                            <li class="flex justify-between gap-3">
                                <span class="truncate">{{ $row->team?->name ?? "Team #{$row->team_id}" }}</span>
                                <span class="tabular-nums font-semibold">{{ $row->c }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>

        </div>
    </div>
</x-filament-panels::page>
