@php
    $compact = $compact ?? false;
@endphp
<div class="relative" wire:key="mention-picker-{{ $compact ? 'comment' : 'post' }}">
    <div class="flex items-center justify-between mb-1.5">
        <label class="block text-xs font-semibold text-aiu-ink-700">
            Mention people or teams <span class="text-aiu-ink-400 font-normal">(optional)</span>
        </label>
        @if (count($mentionedUsers) + count($mentionedTeams) > 0)
            <span class="text-[11px] text-aiu-ink-500">{{ count($mentionedUsers) + count($mentionedTeams) }} selected</span>
        @endif
    </div>

    {{-- Selected chips --}}
    @if (count($mentionedUsers) || count($mentionedTeams))
        <div class="flex flex-wrap gap-1.5 mb-2">
            @foreach ($mentionedUsers as $u)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full chip-3d text-xs text-aiu-ink-700">
                    <svg class="w-3 h-3 text-aiu-red" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    @<span class="font-semibold">{{ $u['name'] }}</span>
                    <button type="button" wire:click="removeUserMention({{ $u['id'] }})" class="ml-0.5 text-aiu-ink-400 hover:text-aiu-red">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </span>
            @endforeach
            @foreach ($mentionedTeams as $t)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full chip-3d text-xs text-aiu-ink-700">
                    <svg class="w-3 h-3 text-aiu-gold" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    @<span class="font-semibold">{{ $t['name'] }}</span>
                    <span class="text-aiu-ink-400 text-[10px] uppercase tracking-wider ml-0.5">team</span>
                    <button type="button" wire:click="removeTeamMention({{ $t['id'] }})" class="ml-0.5 text-aiu-ink-400 hover:text-aiu-red">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </span>
            @endforeach
        </div>
    @endif

    {{-- Search input --}}
    <input type="text" wire:model.live.debounce.250ms="mentionSearch"
           placeholder="Search for a person or team to @mention…"
           class="input-3d w-full px-3 py-2 rounded-lg text-xs">

    {{-- Dropdown results --}}
    @if (trim($mentionSearch) !== '' && (count($userResults) || count($teamResults)))
        <div class="absolute z-30 left-0 right-0 mt-1 card-3d rounded-xl overflow-hidden max-h-64 overflow-y-auto">
            @if (count($userResults))
                <p class="text-[10px] uppercase tracking-wider px-3 pt-2 pb-1 text-aiu-ink-400 font-bold">People</p>
                @foreach ($userResults as $u)
                    <button type="button" wire:click="addUserMention({{ $u->id }})"
                            class="w-full text-left px-3 py-2 hover:bg-aiu-red-50 flex items-center gap-2 transition">
                        <x-avatar :user="$u" size="sm" />
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-aiu-ink-900 truncate">{{ $u->name }}</p>
                            @if ($u->institution)
                                <p class="text-[10px] text-aiu-ink-500 truncate">{{ $u->institution }}</p>
                            @endif
                        </div>
                    </button>
                @endforeach
            @endif
            @if (count($teamResults))
                <p class="text-[10px] uppercase tracking-wider px-3 pt-2 pb-1 text-aiu-ink-400 font-bold border-t border-aiu-line">Teams</p>
                @foreach ($teamResults as $t)
                    <button type="button" wire:click="addTeamMention({{ $t->id }})"
                            class="w-full text-left px-3 py-2 hover:bg-aiu-red-50 flex items-center gap-2 transition">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg chip-3d text-[10px] font-bold text-aiu-ink-700">
                            {{ strtoupper(mb_substr($t->name, 0, 2)) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-aiu-ink-900 truncate">{{ $t->name }}</p>
                            @if ($t->tagline)
                                <p class="text-[10px] text-aiu-ink-500 truncate">{{ $t->tagline }}</p>
                            @endif
                        </div>
                        <span class="text-[10px] text-aiu-gold-600 uppercase tracking-wider font-bold">Team</span>
                    </button>
                @endforeach
            @endif
        </div>
    @elseif (trim($mentionSearch) !== '')
        <div class="absolute z-30 left-0 right-0 mt-1 card-3d rounded-xl px-3 py-3 text-xs text-aiu-ink-500">
            No matches.
        </div>
    @endif
</div>
