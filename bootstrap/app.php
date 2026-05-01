<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Single sign-in for everyone: unauthenticated requests (Filament admin
        // included) bounce to the participant /login page, which redirects each
        // role to its own dashboard after auth.
        $middleware->redirectGuestsTo(fn () => route('login'));

        // Trust X-Forwarded-* headers from the Apache reverse proxy so Laravel
        // generates HTTPS URLs even when PHP sees the request as plain http.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
