<x-filament-panels::page>
    <div class="space-y-8">
        @forelse ($teams as $team)
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    @if ($team->logo_path)
                        <img
                            src="{{ $team->logo_url }}"
                            alt="{{ $team->name }}"
                            class="w-10 h-10 rounded object-contain bg-white"
                        />
                    @else
                        <div class="w-10 h-10 rounded bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-xs font-semibold text-gray-500">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($team->name, 0, 2)) }}
                        </div>
                    @endif
                    <div class="flex-1">
                        <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $team->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $team->members->count() }} {{ \Illuminate\Support\Str::plural('member', $team->members->count()) }}
                            @if ($team->theme)
                                · {{ $team->theme->name ?? '' }}
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    @if ($team->members->isEmpty())
                        <div class="text-sm italic text-gray-500 dark:text-gray-400">No members yet.</div>
                    @else
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-4">
                            @foreach ($team->members as $member)
                                @include('filament.pages.partials.member-avatar-card', ['user' => $member])
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-sm text-gray-500 dark:text-gray-400 italic">No teams yet.</div>
        @endforelse

        @if ($unassigned->isNotEmpty())
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-amber-50 dark:bg-amber-900/20">
                    <div class="font-semibold text-amber-900 dark:text-amber-200">
                        Unassigned Participants ({{ $unassigned->count() }})
                    </div>
                    <div class="text-xs text-amber-700 dark:text-amber-300">
                        Approved participants who are not yet on a team.
                    </div>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-4">
                        @foreach ($unassigned as $member)
                            @include('filament.pages.partials.member-avatar-card', ['user' => $member])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
