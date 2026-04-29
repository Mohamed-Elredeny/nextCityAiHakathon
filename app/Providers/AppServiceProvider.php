<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // In production force every generated URL to use APP_URL exactly
        // (so https://aiu.edu.eg/hackathon stays prefixed on every link).
        if ($this->app->environment('production')) {
            URL::forceScheme('https');

            $appUrl = config('app.url');
            if (filled($appUrl)) {
                URL::forceRootUrl($appUrl);
            }
        }

        // Sub-URI deployment: Livewire registers /livewire/* by default, but
        // we live at /hackathon/livewire/*. Re-register both routes under the
        // path component of APP_URL so the script tag and the update endpoint
        // both resolve correctly behind the alias.
        if (! $this->app->runningInConsole()) {
            $prefix = trim(parse_url((string) config('app.url'), PHP_URL_PATH) ?? '', '/');
            if ($prefix !== '') {
                Livewire::setUpdateRoute(function ($handle) use ($prefix) {
                    return Route::post("/{$prefix}/livewire/update", $handle);
                });
                Livewire::setScriptRoute(function ($handle) use ($prefix) {
                    return Route::get("/{$prefix}/livewire/livewire.js", $handle);
                });
            }
        }
    }
}
