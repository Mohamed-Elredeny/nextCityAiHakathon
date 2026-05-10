<?php

use App\Http\Controllers\AvatarController;
use App\Http\Controllers\TeamAssetController;
use App\Livewire\AttendanceCheckIn;
use App\Livewire\BigScreen;
use App\Livewire\JudgeAssignments;
use App\Livewire\CommunityHub;
use App\Livewire\CommunityPostView;
use App\Livewire\JudgeDashboard;
use App\Livewire\MentorDashboard;
use App\Livewire\NotificationCenter;
use App\Livewire\ForgotPassword;
use App\Livewire\ParticipantLogin;
use App\Livewire\ParticipantRegister;
use App\Livewire\PeoplesChoiceVote;
use App\Livewire\ProfileView;
use App\Livewire\PublicLeaderboard;
use App\Livewire\RecruitingTeams;
use App\Livewire\Winners;
use App\Livewire\RestrictedAwardVote;
use App\Livewire\TeamSubmissionPreview;
use App\Livewire\TeamWorkspace;
use App\Livewire\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicLeaderboard::class)->name('home');
Route::get('/leaderboard', PublicLeaderboard::class)->name('leaderboard');
Route::get('/screen', BigScreen::class)->name('screen');
Route::get('/winners', Winners::class)->name('winners');

// Public read-only team submission page (visible to voters/judges/mentors/anyone)
Route::get('/teams/{slug}', TeamSubmissionPreview::class)->name('teams.show');

// Public Community Hub
Route::get('/community', CommunityHub::class)->name('community');
Route::get('/community/teams', RecruitingTeams::class)->name('community.teams');
Route::get('/community/{post}', CommunityPostView::class)->whereNumber('post')->name('community.show');

// Public user profile (read-only)
Route::get('/u/{id}', ProfileView::class)->whereNumber('id')->name('users.show');

// Public People's Choice voting (no auth required — QR-friendly)
Route::get('/vote', PeoplesChoiceVote::class)->name('vote');
Route::get('/vote/qr', function () {
    return view('vote-qr', ['voteUrl' => url('/vote')]);
})->name('vote.qr');

// Restricted special-award voting (Best AI / Most Impactful).
// Auth required. Eligibility (team_member / judge / mentor / super_admin)
// is enforced inside the component so the page can render an explanatory
// "not eligible" panel instead of bouncing the user.
Route::get('/awards/vote', RestrictedAwardVote::class)
    ->middleware('auth')
    ->name('awards.vote');

// Public attendance check-in (QR landing). Auth is enforced inside the
// Livewire component itself so unauthenticated users get redirected to /login
// with the intended URL preserved.
Route::get('/attendance/{token}', AttendanceCheckIn::class)
    ->name('attendance.check-in');

// Server-rendered QR image for the attendance check-in URL.
// Lives on the same origin so a strict CSP (default-src 'self') still allows it.
// Cached on disk (NOT in the cache table — MySQL utf8 columns can't hold a
// raw PNG binary, that throws "Incorrect string value: \x89PNG").
Route::get('/attendance-qr/{token}.png', function (string $token) {
    abort_unless(\App\Models\AttendanceSession::where('token', $token)->exists(), 404);

    $diskPath = 'qr-cache/' . $token . '.png';
    $disk = \Illuminate\Support\Facades\Storage::disk('local');

    // Serve from disk if cached and fresh (≤ 7 days)
    if ($disk->exists($diskPath) && (time() - $disk->lastModified($diskPath)) < 7 * 86400) {
        return response($disk->get($diskPath), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    $url = route('attendance.check-in', $token);
    $endpoint = 'https://api.qrserver.com/v1/create-qr-code/?size=512x512&margin=10&data=' . urlencode($url);

    $png = null;
    try {
        $ctx = stream_context_create([
            'http' => ['timeout' => 5, 'header' => "User-Agent: AIU-Hackathon-QR/1.0\r\n"],
            'https' => ['timeout' => 5, 'header' => "User-Agent: AIU-Hackathon-QR/1.0\r\n"],
        ]);
        $img = @file_get_contents($endpoint, false, $ctx);
        if ($img !== false && strlen($img) > 100) {
            $png = $img;
        }
    } catch (\Throwable $e) {
        $png = null;
    }

    abort_if($png === null, 503, 'QR generation upstream is temporarily unavailable.');

    // Best-effort store; if disk is unwritable, just serve without caching.
    try {
        $disk->put($diskPath, $png);
    } catch (\Throwable $e) {
        // ignore
    }

    return response($png, 200, [
        'Content-Type' => 'image/png',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->name('attendance.qr-image')->where('token', '[A-Za-z0-9]+');

Route::get('/login', ParticipantLogin::class)
    ->middleware('guest')
    ->name('login');

Route::get('/register', ParticipantRegister::class)
    ->middleware('guest')
    ->name('register');

Route::get('/forgot-password', ForgotPassword::class)
    ->middleware('guest')
    ->name('password.request');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/workspace', TeamWorkspace::class)->name('workspace');
    Route::get('/judge', JudgeDashboard::class)
        ->middleware('role:judge|super_admin')
        ->name('judge');
    Route::get('/judge/assignments', JudgeAssignments::class)
        ->middleware('role:judge|super_admin')
        ->name('judge.assignments');
    Route::get('/mentor', MentorDashboard::class)
        ->middleware('role:mentor|super_admin')
        ->name('mentor');
    Route::get('/profile', UserProfile::class)->name('profile');
    Route::get('/notifications', NotificationCenter::class)->name('notifications');

    // Plain form POST uploads — bypass Livewire so we don't trip on PHP
    // notices leaking into the JSON response.
    Route::post('/workspace/logo',   [TeamAssetController::class, 'uploadLogo'])->name('workspace.logo.upload');
    Route::delete('/workspace/logo', [TeamAssetController::class, 'deleteLogo'])->name('workspace.logo.delete');
    Route::post('/workspace/banner', [TeamAssetController::class, 'uploadBanner'])->name('workspace.banner.upload');
    Route::delete('/workspace/banner',[TeamAssetController::class, 'deleteBanner'])->name('workspace.banner.delete');
    Route::post('/profile/avatar',   [AvatarController::class, 'upload'])->name('profile.avatar.upload');
    Route::delete('/profile/avatar', [AvatarController::class, 'delete'])->name('profile.avatar.delete');
});
