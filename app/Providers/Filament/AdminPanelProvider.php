<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        // Re-route Filament's "you must log in" redirect to the unified /login.
        Authenticate::redirectUsing(fn () => route('login'));
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // Filament's built-in /admin/login is intentionally disabled.
            // The single sign-in for ALL roles is the participant /login page,
            // which redirects super_admin users straight to /admin.
            ->colors([
                'primary' => Color::hex('#C8102E'),
                'danger' => Color::hex('#C8102E'),
                'warning' => Color::hex('#cf9040'),
                'success' => Color::Emerald,
                'info' => Color::hex('#0F4778'),
            ])
            ->brandLogo(asset('img/aec-logo.png'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('img/aec-logo.png'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\EventStatusWidget::class,
                \App\Filament\Widgets\HackathonStatsOverview::class,
                \App\Filament\Widgets\SubmissionStatusChart::class,
                \App\Filament\Widgets\TopTeamsLeaderboard::class,
                \App\Filament\Widgets\RecentAuditLog::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
