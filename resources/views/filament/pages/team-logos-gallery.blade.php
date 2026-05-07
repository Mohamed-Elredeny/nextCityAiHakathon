<x-filament-panels::page>
    @php
        $withLogo = $teams->filter(fn ($t) => filled($t->logo_path));
        $withoutLogo = $teams->filter(fn ($t) => blank($t->logo_path));
    @endphp

    @if ($withLogo->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach ($withLogo as $team)
                <a
                    href="{{ route('teams.show', $team->slug) }}"
                    target="_blank"
                    class="group flex flex-col items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:shadow-md hover:border-primary-500 transition"
                >
                    <div class="w-full aspect-square flex items-center justify-center bg-gray-50 dark:bg-gray-900 rounded overflow-hidden">
                        <img
                            src="{{ $team->logo_url }}"
                            alt="{{ $team->name }}"
                            class="max-w-full max-h-full object-contain"
                            loading="lazy"
                        />
                    </div>
                    <div class="text-sm font-medium text-center text-gray-900 dark:text-gray-100 line-clamp-2 group-hover:text-primary-600">
                        {{ $team->name }}
                    </div>
                    @if ($team->tagline)
                        <div class="text-xs text-gray-500 dark:text-gray-400 text-center line-clamp-2">
                            {{ $team->tagline }}
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    @else
        <div class="text-sm text-gray-500 dark:text-gray-400 italic">
            No team logos uploaded yet.
        </div>
    @endif

    @if ($withoutLogo->isNotEmpty())
        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                Teams without a logo ({{ $withoutLogo->count() }})
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach ($withoutLogo as $team)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                        {{ $team->name }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif
</x-filament-panels::page>
