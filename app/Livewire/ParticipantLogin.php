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

        // Block sign-in for accounts that are awaiting admin approval, before
        // we ever consume a session, so they can't slip past the role gates.
        $pending = \App\Models\User::where('email', $this->email)
            ->where('registration_status', 'pending')
            ->exists();
        if ($pending) {
            $this->addError('email', 'Your account is awaiting admin approval. You\'ll be notified once it\'s reviewed.');
            return;
        }

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

        // Honor an explicit attendance check-in redirect (QR landing flow).
        $attendanceRedirect = request()->session()->pull('attendance_redirect');
        if ($attendanceRedirect) {
            request()->session()->forget('url.intended');
            return redirect($attendanceRedirect);
        }

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
