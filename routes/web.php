<?php

use App\Livewire\BigScreen;
use App\Livewire\JudgeDashboard;
use App\Livewire\MentorDashboard;
use App\Livewire\ParticipantLogin;
use App\Livewire\PeoplesChoiceVote;
use App\Livewire\ProfileView;
use App\Livewire\PublicLeaderboard;
use App\Livewire\TeamSubmissionPreview;
use App\Livewire\TeamWorkspace;
use App\Livewire\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicLeaderboard::class)->name('home');
Route::get('/leaderboard', PublicLeaderboard::class)->name('leaderboard');
Route::get('/screen', BigScreen::class)->name('screen');

// Public read-only team submission page (visible to voters/judges/mentors/anyone)
Route::get('/teams/{slug}', TeamSubmissionPreview::class)->name('teams.show');

// Public user profile (read-only)
Route::get('/u/{id}', ProfileView::class)->whereNumber('id')->name('users.show');

// Public People's Choice voting (no auth required — QR-friendly)
Route::get('/vote', PeoplesChoiceVote::class)->name('vote');
Route::get('/vote/qr', function () {
    return view('vote-qr', ['voteUrl' => url('/vote')]);
})->name('vote.qr');

Route::get('/login', ParticipantLogin::class)
    ->middleware('guest')
    ->name('login');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/workspace', TeamWorkspace::class)->name('workspace');
    Route::get('/judge', JudgeDashboard::class)->name('judge');
    Route::get('/mentor', MentorDashboard::class)->name('mentor');
    Route::get('/profile', UserProfile::class)->name('profile');
});
