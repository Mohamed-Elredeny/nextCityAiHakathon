<div class="relative"
     x-data="{
        open: @entangle('open'),
        muted: localStorage.getItem('notif-muted') === '1',
        toggleMute() {
            this.muted = !this.muted;
            localStorage.setItem('notif-muted', this.muted ? '1' : '0');
        },
        playBeep() {
            if (this.muted) return;
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const t = ctx.currentTime;
                const make = (freq, start, dur) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.value = freq;
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    gain.gain.setValueAtTime(0.0001, t + start);
                    gain.gain.exponentialRampToValueAtTime(0.18, t + start + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, t + start + dur);
                    osc.start(t + start);
                    osc.stop(t + start + dur + 0.02);
                };
                // Two-tone chirp: 880Hz then 1320Hz (a fifth up)
                make(880, 0, 0.10);
                make(1320, 0.10, 0.18);
            } catch (e) {}
        },
     }"
     @click.outside="open = false; $wire.close()"
     @keydown.escape.window="open = false; $wire.close()"
     @notification-arrived="playBeep()"
     @pusher-notification-arrived.window="playBeep(); $wire.$refresh()">
    <button type="button" wire:click="toggle"
            class="relative p-2 rounded-lg text-aiu-ink-700 hover:text-aiu-red hover:bg-aiu-red-50 transition"
            wire:poll.15s
            aria-label="Notifications">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if ($unreadCount > 0)
            <span class="absolute top-0.5 right-0.5 inline-flex items-center justify-center min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-aiu-red text-white text-[10px] font-bold tabular-nums ring-2 ring-white">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute right-0 mt-2 w-[20rem] sm:w-[22rem] card-3d rounded-2xl overflow-hidden z-50 max-h-[80vh] flex flex-col">
        <div class="flex items-center justify-between px-4 py-3 border-b border-aiu-line">
            <div class="flex items-center gap-2">
                <p class="font-heading font-bold text-sm text-aiu-ink-900">Notifications</p>
                @if ($unreadCount > 0)
                    <span class="text-[10px] tabular-nums px-1.5 py-0.5 rounded-full bg-aiu-red text-white">{{ $unreadCount }}</span>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="toggleMute()"
                        :title="muted ? 'Unmute notification sound' : 'Mute notification sound'"
                        class="text-aiu-ink-400 hover:text-aiu-red transition">
                    <template x-if="!muted">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072M18.364 5.636a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                        </svg>
                    </template>
                    <template x-if="muted">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                        </svg>
                    </template>
                </button>
                @if ($unreadCount > 0)
                    <button type="button" wire:click="markAllAsRead" class="text-[11px] text-aiu-red font-semibold hover:underline">
                        Mark all read
                    </button>
                @endif
            </div>
        </div>

        <div class="flex-1 overflow-y-auto">
            @if ($recent->isEmpty())
                <div class="px-4 py-10 text-center">
                    <svg class="w-8 h-8 mx-auto text-aiu-ink-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="text-xs text-aiu-ink-400">You're all caught up.</p>
                </div>
            @else
                @foreach ($recent as $notification)
                    @include('partials.notification-item', ['notification' => $notification, 'compact' => false])
                @endforeach
            @endif
        </div>

        <div class="border-t border-aiu-line bg-aiu-ink-50/30 px-4 py-2.5 text-center">
            <a href="{{ route('notifications') }}" class="text-xs font-semibold text-aiu-red hover:underline">
                View all notifications
            </a>
        </div>
    </div>
</div>
