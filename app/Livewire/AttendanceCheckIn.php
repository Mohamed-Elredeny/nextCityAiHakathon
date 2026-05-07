<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

class AttendanceCheckIn extends Component
{
    public string $token = '';
    public ?AttendanceSession $session = null;

    public ?Attendance $existing = null;
    public ?string $error = null;
    public ?string $success = null;
    public array $missingFields = [];

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->session = AttendanceSession::where('token', $token)->first();

        if (! $this->session) {
            $this->error = 'This check-in link is invalid or has been removed.';
            return;
        }

        if (! Auth::check()) {
            // Send them to login, then bring them back here. ParticipantLogin
            // pulls 'attendance_redirect' from the session after a successful
            // sign-in and redirects to it (bypassing the role-based default).
            session(['attendance_redirect' => route('attendance.check-in', $this->token)]);
            $this->redirect(route('login'), navigate: false);
            return;
        }

        $user = Auth::user();
        $this->missingFields = $user->missingProfileFields();

        $this->existing = Attendance::where('attendance_session_id', $this->session->id)
            ->where('user_id', $user->id)
            ->first();
    }

    public function checkIn(): void
    {
        if (! $this->session) {
            $this->error = 'Session not found.';
            return;
        }

        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: false);
            return;
        }

        $user = Auth::user();

        // Re-evaluate profile completeness in case it changed mid-session.
        $missing = $user->missingProfileFields();
        if (! empty($missing)) {
            $this->missingFields = $missing;
            $this->error = 'Complete your profile (including a photo) before checking in.';
            return;
        }

        if (! $this->session->isOpenForCheckIn()) {
            $this->error = 'This check-in window is currently closed.';
            return;
        }

        if ($this->existing) {
            $this->success = 'You\'re already checked in.';
            return;
        }

        $fingerprint = $this->deviceFingerprint();

        // One check-in per device per session.
        $deviceUsed = Attendance::where('attendance_session_id', $this->session->id)
            ->where('device_fingerprint', $fingerprint)
            ->where('user_id', '!=', $user->id)
            ->exists();
        if ($deviceUsed) {
            $this->error = 'This device has already been used to check in another participant for this session. Please use your own device.';
            return;
        }

        $attendance = Attendance::create([
            'attendance_session_id' => $this->session->id,
            'user_id' => $user->id,
            'checked_in_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 500, ''),
            'device_fingerprint' => $fingerprint,
            'source' => Attendance::SOURCE_SELF,
        ]);

        $this->existing = $attendance;
        $this->success = 'Welcome! Your attendance has been recorded.';
    }

    /**
     * Browsers cannot read MAC addresses for security reasons. We approximate
     * "one check-in per device" using a persistent cookie + IP + UA hash.
     */
    private function deviceFingerprint(): string
    {
        $cookieName = 'aiu_device_id';
        $deviceId = request()->cookie($cookieName);
        if (! $deviceId) {
            $deviceId = (string) Str::uuid();
            // Persist for ~1 year on this device.
            cookie()->queue(cookie($cookieName, $deviceId, 60 * 24 * 365, null, null, false, true));
        }

        return hash('sha256', $deviceId . '|' . request()->ip() . '|' . substr((string) request()->userAgent(), 0, 200));
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        return view('livewire.attendance-check-in');
    }
}
