@php
    $userIds = $userIds ?? [];
    $teamIds = $teamIds ?? [];
    $userMap = $userMap ?? collect();
    $teamMap = $teamMap ?? collect();
    $hasAny = (count($userIds) || count($teamIds));
@endphp
@if ($hasAny)
    <div class="flex flex-wrap gap-1 mt-2">
        @foreach ($userIds as $uid)
            @php $u = $userMap->get($uid); @endphp
            @if ($u)
                <a href="{{ route('users.show', $u->id) }}"
                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full chip-3d text-[11px] text-aiu-ink-700 hover:text-aiu-red transition">
                    <span class="text-aiu-red font-bold">@</span><span class="font-semibold">{{ $u->name }}</span>
                </a>
            @endif
        @endforeach
        @foreach ($teamIds as $tid)
            @php $t = $teamMap->get($tid); @endphp
            @if ($t)
                <a href="{{ route('teams.show', $t->slug) }}"
                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full chip-3d text-[11px] text-aiu-ink-700 hover:text-aiu-red transition">
                    <span class="text-aiu-gold font-bold">@</span><span class="font-semibold">{{ $t->name }}</span>
                    <span class="text-aiu-ink-400 text-[9px] uppercase tracking-wider">team</span>
                </a>
            @endif
        @endforeach
    </div>
@endif
