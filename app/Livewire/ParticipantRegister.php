<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ParticipantRegister extends Component
{
    public string $role = 'student';

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $institution = '';
    public string $headline = '';
    public string $bio = '';
    public string $password = '';
    public string $password_confirmation = '';

    public ?string $submittedStatus = null; // 'approved' | 'pending'

    public const ROLES = ['student', 'mentor', 'judge'];
    public const PENDING_ROLES = ['mentor', 'judge'];

    protected function rules(): array
    {
        return [
            'role'        => ['required', Rule::in(self::ROLES)],
            'name'        => ['required', 'string', 'min:2', 'max:255'],
            'email'       => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'institution' => ['nullable', 'string', 'max:255'],
            'headline'    => ['nullable', 'string', 'max:120'],
            'bio'         => ['nullable', 'string', 'max:2000'],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function register()
    {
        $data = $this->validate();

        $needsApproval = in_array($data['role'], self::PENDING_ROLES, true);

        $user = User::create([
            'name'                => trim($data['name']),
            'email'               => strtolower(trim($data['email'])),
            'password'            => Hash::make($data['password']),
            'phone'               => $data['phone'] ?: null,
            'institution'         => $data['institution'] ?: null,
            'headline'            => $data['headline'] ?: null,
            'bio'                 => $data['bio'] ?: null,
            'requested_role'      => $data['role'],
            'registration_status' => $needsApproval ? 'pending' : 'approved',
            'approved_at'         => $needsApproval ? null : now(),
        ]);

        // Students get their role straight away. Mentors / judges only get the
        // role assigned when an admin approves them — until then they have no
        // role and cannot sign in.
        if (! $needsApproval) {
            $user->syncRoles(['team_member']);
            Auth::login($user);
            request()->session()->regenerate();
            return redirect('/workspace');
        }

        $this->submittedStatus = 'pending';
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        return view('livewire.participant-register');
    }
}
