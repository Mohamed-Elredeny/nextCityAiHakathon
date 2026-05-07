<a
    href="{{ route('users.show', $user->id) }}"
    target="_blank"
    class="flex flex-col items-center gap-1.5 group"
    title="{{ $user->name }}"
>
    <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700 ring-2 ring-transparent group-hover:ring-primary-500 transition flex items-center justify-center">
        @if ($user->avatar_path)
            <img
                src="{{ $user->avatar_url }}"
                alt="{{ $user->name }}"
                class="w-full h-full object-cover"
                loading="lazy"
            />
        @else
            <span class="text-sm font-semibold text-gray-500 dark:text-gray-300">
                {{ $user->initials }}
            </span>
        @endif
    </div>
    <div class="text-xs text-center text-gray-700 dark:text-gray-200 line-clamp-2 w-full">
        {{ $user->name }}
    </div>
</a>
