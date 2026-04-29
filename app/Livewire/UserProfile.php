<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class UserProfile extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $institution = '';
    public string $headline = '';
    public string $bio = '';
    public array $socialLinks = ['linkedin' => '', 'github' => '', 'twitter' => '', 'website' => ''];
    public $avatar = null;
    public bool $removeAvatar = false;
    public ?string $savedMessage = null;

    public function mount(): void
    {
        $u = Auth::user();
        if (!$u) return;
        $this->name = (string) $u->name;
        $this->email = (string) $u->email;
        $this->phone = (string) $u->phone;
        $this->institution = (string) $u->institution;
        $this->headline = (string) $u->headline;
        $this->bio = (string) $u->bio;
        $existing = $u->social_links ?? [];
        $this->socialLinks = array_merge(['linkedin' => '', 'github' => '', 'twitter' => '', 'website' => ''], is_array($existing) ? $existing : []);
    }

    public function save(): void
    {
        $u = Auth::user();
        if (!$u) return;

        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'institution' => 'nullable|string|max:255',
            'headline' => 'nullable|string|max:120',
            'bio' => 'nullable|string|max:2000',
            'socialLinks.linkedin' => 'nullable|url|max:255',
            'socialLinks.github' => 'nullable|url|max:255',
            'socialLinks.twitter' => 'nullable|url|max:255',
            'socialLinks.website' => 'nullable|url|max:255',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($this->removeAvatar && $u->avatar_path) {
            Storage::disk('public')->delete($u->avatar_path);
            $u->avatar_path = null;
            $this->removeAvatar = false;
        }

        if ($this->avatar) {
            if ($u->avatar_path) {
                Storage::disk('public')->delete($u->avatar_path);
            }
            $u->avatar_path = $this->avatar->store('avatars', 'public');
            $this->avatar = null;
        }

        $u->fill([
            'name' => $this->name,
            'phone' => $this->phone ?: null,
            'institution' => $this->institution ?: null,
            'headline' => $this->headline ?: null,
            'bio' => $this->bio ?: null,
            'social_links' => array_filter($this->socialLinks, fn ($v) => filled($v)),
        ])->save();

        $this->savedMessage = 'Profile updated';
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        return view('livewire.user-profile', [
            'user' => Auth::user(),
        ]);
    }
}
