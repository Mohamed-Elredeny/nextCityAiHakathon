<div class="max-w-3xl mx-auto px-6 lg:px-8 py-10">
    <div class="flex items-center justify-between mb-6 gap-3">
        <div>
            <p class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full chip-3d
                      text-aiu-red uppercase tracking-[0.22em] text-[11px] font-bold mb-2">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-aiu-red animate-pulse"></span>
                Notifications
            </p>
            <h1 class="font-heading font-bold text-3xl text-aiu-ink-900">Inbox</h1>
            @if ($unreadCount > 0)
                <p class="text-sm text-aiu-ink-600 mt-1">{{ $unreadCount }} unread</p>
            @endif
        </div>
        @if ($unreadCount > 0)
            <button type="button" wire:click="markAllAsRead" class="btn-soft px-4 py-2 rounded-lg text-sm font-semibold">
                Mark all as read
            </button>
        @endif
    </div>

    <div class="flex items-center gap-2 mb-4">
        <button wire:click="setFilter('all')" class="px-3 py-1.5 rounded-full text-xs font-semibold transition
            {{ $filter === 'all' ? 'btn-aiu' : 'chip-3d text-aiu-ink-700 hover:text-aiu-red' }}">All</button>
        <button wire:click="setFilter('unread')" class="px-3 py-1.5 rounded-full text-xs font-semibold transition
            {{ $filter === 'unread' ? 'btn-aiu' : 'chip-3d text-aiu-ink-700 hover:text-aiu-red' }}">
            Unread
            @if ($unreadCount > 0)
                <span class="ml-1 text-[10px] tabular-nums px-1.5 py-0.5 rounded-full
                    {{ $filter === 'unread' ? 'bg-white/25 text-white' : 'bg-aiu-red text-white' }}">
                    {{ $unreadCount }}
                </span>
            @endif
        </button>
    </div>

    <div class="card-3d rounded-2xl overflow-hidden">
        @if ($notifications->isEmpty())
            <div class="px-4 py-16 text-center">
                <svg class="w-10 h-10 mx-auto text-aiu-ink-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="text-sm text-aiu-ink-500">
                    {{ $filter === 'unread' ? 'No unread notifications.' : 'No notifications yet.' }}
                </p>
            </div>
        @else
            @foreach ($notifications as $notification)
                @include('partials.notification-item', ['notification' => $notification, 'compact' => false])
            @endforeach
        @endif
    </div>

    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
</div>
