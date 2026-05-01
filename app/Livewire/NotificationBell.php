<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;
    public int $lastSeenCount = 0;
    public bool $initialized = false;

    public function mount(): void
    {
        $user = Auth::user();
        if ($user) {
            $this->lastSeenCount = $user->unreadNotifications()->count();
            $this->initialized = true;
        }
    }

    public function toggle(): void
    {
        if (!Auth::check()) return;
        $this->open = !$this->open;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function markAsRead(string $id): void
    {
        $user = Auth::user();
        if (!$user) return;
        $notification = $user->notifications()->where('id', $id)->first();
        if ($notification && !$notification->read_at) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        $user = Auth::user();
        if (!$user) return;
        $user->unreadNotifications->markAsRead();
    }

    public function render()
    {
        $user = Auth::user();
        if (!$user) {
            return view('livewire.notification-bell', [
                'unreadCount' => 0,
                'recent' => collect(),
            ]);
        }

        $recent = $user->notifications()->limit(8)->get();
        $unreadCount = $user->unreadNotifications()->count();

        $arrivedDelta = max(0, $unreadCount - $this->lastSeenCount);
        if ($arrivedDelta > 0 && $this->initialized) {
            $this->dispatch('notification-arrived', count: $arrivedDelta);
        }
        $this->lastSeenCount = $unreadCount;
        $this->initialized = true;

        return view('livewire.notification-bell', [
            'unreadCount' => $unreadCount,
            'recent' => $recent,
        ]);
    }
}
