<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ForgotPassword extends Component
{
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';

    // 'verify' (email + phone form) | 'reset' (set new password) | 'unverified' (contact admin)
    public string $stage = 'verify';

    public ?int $userId = null;
    public bool $resetDone = false;

    public const SUPPORT_PHONE_DISPLAY = '01514118958';
    public const SUPPORT_PHONE_WHATSAPP = '201514118958';

    public function verify()
    {
        $this->validate([
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'min:6', 'max:50'],
        ]);

        $email = strtolower(trim($this->email));
        $phoneDigits = preg_replace('/\D+/', '', $this->phone);

        $user = User::where('email', $email)->first();

        if (! $user || ! $user->phone || preg_replace('/\D+/', '', $user->phone) !== $phoneDigits) {
            $this->stage = 'unverified';
            return;
        }

        if ($user->registration_status === 'pending') {
            $this->stage = 'unverified';
            return;
        }

        $this->userId = $user->id;
        $this->stage = 'reset';
    }

    public function updatePassword()
    {
        $this->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::find($this->userId);
        if (! $user) {
            $this->stage = 'unverified';
            return;
        }

        $user->password = Hash::make($this->password);
        $user->save();

        $this->clearState();
        $this->resetDone = true;
    }

    public function startOver(): void
    {
        $this->clearState();
    }

    private function clearState(): void
    {
        $this->email = '';
        $this->phone = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->userId = null;
        $this->stage = 'verify';
        $this->resetDone = false;
        $this->resetErrorBag();
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        return view('livewire.forgot-password');
    }
}
