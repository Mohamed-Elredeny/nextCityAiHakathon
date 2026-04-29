<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
    }
}
