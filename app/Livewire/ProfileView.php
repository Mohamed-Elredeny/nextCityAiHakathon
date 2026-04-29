<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ProfileView extends Component
{
    public int $userId;

    public function mount(int $id): void
    {
        $this->userId = $id;
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        $user = User::with(['roles', 'team.theme', 'ledTeams.theme'])->find($this->userId);
        abort_if(!$user, 404);

        $teams = $user->team()->with('theme')->get();
        $primaryRole = $user->roles->first()?->name;

        return view('livewire.profile-view', [
            'user' => $user,
            'teams' => $teams,
            'primaryRole' => $primaryRole,
        ]);
    }
}
