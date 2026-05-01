<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationCenter extends Component
{
    use WithPagination;

    public string $filter = 'all';

    public function setFilter(string $filter): void
    {
        $this->filter = in_array($filter, ['all', 'unread'], true) ? $filter : 'all';
        $this->resetPage();
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

    #[Layout('components.layouts.public')]
    public function render()
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $query = $user->notifications();
        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(20);
        $unreadCount = $user->unreadNotifications()->count();

        return view('livewire.notification-center', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}
