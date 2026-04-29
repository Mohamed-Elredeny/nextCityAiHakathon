<?php

use App\Jobs\CheckRepoPublic;
use App\Models\Submission;
use Illuminate\Support\Facades\Schedule;

Schedule::command('hackathon:phase-tick')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->runInBackground();

Schedule::call(function () {
    Submission::whereIn('status', [Submission::STATUS_SUBMITTED, Submission::STATUS_VALIDATED])
        ->whereNotNull('repo_url')
        ->limit(20)
        ->get()
        ->each(fn ($s) => CheckRepoPublic::dispatch($s->id));
})->dailyAt('22:00')->name('check-repos-public');
