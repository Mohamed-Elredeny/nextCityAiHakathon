<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ParticipantLogin extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', 'Invalid credentials. Please check your email and password.');
            return;
        }

        request()->session()->regenerate();

        // Pick the canonical landing page for this user's role. We don't use
        // ->intended() here because if the user typed /admin first (got bounced
        // to /login as a guest), Laravel would send a judge back to /admin —
        // a panel they have no permission for.
        $user = Auth::user();
        $target = match (true) {
            $user->hasRole('super_admin') => '/admin',
            $user->hasRole('judge')       => '/judge',
            $user->hasRole('mentor')      => '/mentor',
            default                       => '/workspace',
        };

        // Forget any stale "intended" URL from the redirect cycle.
        request()->session()->forget('url.intended');

        return redirect($target);
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        return view('livewire.participant-login');
    }
}
