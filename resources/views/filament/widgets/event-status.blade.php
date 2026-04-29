@php
    $countdownTarget = $activeEnds ?? $nextStarts;
    $countdownLabel = $activeEnds ? 'Active phase ends in' : ($nextStarts ? 'Next phase starts in' : null);
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Current Edition
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900 dark:text-white">
                    {{ $edition?->name ?? 'No active edition' }}
                </h2>
                @if ($edition)
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        {{ $edition->starts_at?->format('M j, Y') }}
                        &mdash;
                        {{ $edition->ends_at?->format('M j, Y') }}
                    </p>
                @endif
            </div>

            <div class="flex flex-col gap-1 text-right">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Active Phase
                </p>
                @if ($activePhase)
                    <div class="inline-flex items-center justify-end gap-2">
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">
                            <span class="mr-1 h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                            Live
                        </span>
                        <span class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ $activePhase->label }}
                        </span>
                    </div>
                @elseif ($nextPhase)
                    <div class="inline-flex items-center justify-end gap-2">
                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-semibold text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                            Pending
                        </span>
                        <span class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ $nextPhase->label }}
                        </span>
                    </div>
                @else
                    <span class="text-sm text-gray-500 dark:text-gray-400">No active phase</span>
                @endif

                @if ($countdownTarget && $countdownLabel)
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $countdownLabel }}
                        <span class="font-semibold text-gray-700 dark:text-gray-200">
                            {{ $countdownTarget->diffForHumans(null, ['parts' => 2, 'short' => true]) }}
                        </span>
                    </p>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
