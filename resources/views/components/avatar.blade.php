@props([
    'user' => null,
    'name' => null,
    'src' => null,
    'size' => 'md',
    'leader' => false,
])

@php
    $sizes = [
        'xs' => 'w-5 h-5 text-[9px]',
        'sm' => 'w-7 h-7 text-[10px]',
        'md' => 'w-9 h-9 text-xs',
        'lg' => 'w-12 h-12 text-sm',
        'xl' => 'w-20 h-20 text-xl',
        '2xl' => 'w-28 h-28 text-2xl',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];

    $resolvedName = $name ?? ($user?->name ?? '?');
    $resolvedSrc = $src;
    if (!$resolvedSrc && $user) {
        $resolvedSrc = $user->avatar_path ? asset('storage/' . $user->avatar_path) : null;
    }
    $initials = collect(explode(' ', trim($resolvedName)))
        ->take(2)
        ->map(fn ($p) => mb_substr($p, 0, 1))
        ->implode('') ?: '?';
@endphp

<span {{ $attributes->merge([
    'class' => "shrink-0 inline-flex items-center justify-center rounded-full overflow-hidden font-bold uppercase {$sizeClass} " .
        ($leader ? 'bg-aiu-red text-white ring-2 ring-aiu-gold' : 'bg-aiu-red text-white')
]) }} title="{{ $resolvedName }}{{ $leader ? ' (leader)' : '' }}">
    @if ($resolvedSrc)
        <img src="{{ $resolvedSrc }}" alt="{{ $resolvedName }}" class="w-full h-full object-cover">
    @else
        <span>{{ $initials }}</span>
    @endif
</span>
