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

        $user = Auth::user();
        if ($user->hasRole('super_admin')) {
            return redirect()->intended('/admin');
        }
        if ($user->hasRole('judge')) {
            return redirect()->intended('/judge');
        }
        if ($user->hasRole('mentor')) {
            return redirect()->intended('/mentor');
        }
        return redirect()->intended('/workspace');
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        return view('livewire.participant-login');
    }
}
