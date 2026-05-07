<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SystemTools extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'System Tools';

    protected static ?string $title = 'System Tools';

    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.pages.system-tools';

    public ?string $lastResult = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearAll')
                ->label('Clear ALL Caches')
                ->icon('heroicon-o-fire')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('Runs config:clear, route:clear, view:clear, cache:clear, filament:optimize-clear and resets OPcache.')
                ->action(fn () => $this->runClearAll()),

            Action::make('clearFilament')
                ->label('Clear Filament Cache')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->action(fn () => $this->runArtisan('filament:optimize-clear', 'Filament cache cleared')),

            Action::make('clearViews')
                ->label('Clear Views')
                ->icon('heroicon-o-eye-slash')
                ->color('gray')
                ->action(fn () => $this->runArtisan('view:clear', 'Compiled views cleared')),

            Action::make('clearConfig')
                ->label('Clear Config')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->action(fn () => $this->runArtisan('config:clear', 'Config cache cleared')),

            Action::make('clearRoutes')
                ->label('Clear Routes')
                ->icon('heroicon-o-map')
                ->color('gray')
                ->action(fn () => $this->runArtisan('route:clear', 'Route cache cleared')),

            Action::make('resetOpcache')
                ->label('Reset OPcache')
                ->icon('heroicon-o-bolt')
                ->color('info')
                ->action(fn () => $this->resetOpcache()),

            Action::make('migrate')
                ->label('Run Migrations')
                ->icon('heroicon-o-circle-stack')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('Runs `php artisan migrate --force`. Use this only after uploading new migration files.')
                ->action(fn () => $this->runArtisan('migrate', 'Migrations applied', ['--force' => true])),
        ];
    }

    public function runClearAll(): void
    {
        $log = [];

        foreach (['config:clear', 'route:clear', 'view:clear', 'cache:clear', 'filament:optimize-clear'] as $cmd) {
            try {
                Artisan::call($cmd);
                $log[] = "✓ {$cmd}";
            } catch (\Throwable $e) {
                $log[] = "✗ {$cmd} — " . $e->getMessage();
            }
        }

        // Belt-and-suspenders: physically wipe bootstrap/cache files in case
        // an Artisan command can't write (perms, opcache lock, etc.).
        try {
            $bootstrapCache = base_path('bootstrap/cache');
            if (is_dir($bootstrapCache)) {
                foreach (File::files($bootstrapCache) as $f) {
                    if (in_array($f->getFilename(), ['.gitignore'])) continue;
                    @unlink($f->getPathname());
                }
                $log[] = '✓ bootstrap/cache wiped';
            }
        } catch (\Throwable $e) {
            $log[] = '✗ bootstrap/cache wipe — ' . $e->getMessage();
        }

        // OPcache (the usual culprit on cPanel)
        if (function_exists('opcache_reset')) {
            $log[] = @opcache_reset() ? '✓ opcache_reset()' : '✗ opcache_reset() returned false';
        } else {
            $log[] = '— opcache extension not loaded';
        }

        $this->lastResult = implode("\n", $log);

        Notification::make()
            ->title('All caches cleared')
            ->body('Hard-refresh the admin (Ctrl+Shift+R) to see new menu items.')
            ->success()
            ->send();
    }

    public function runArtisan(string $command, string $successMessage, array $params = []): void
    {
        try {
            Artisan::call($command, $params);
            $this->lastResult = "✓ {$command}\n" . trim(Artisan::output());

            Notification::make()
                ->title($successMessage)
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->lastResult = "✗ {$command}\n" . $e->getMessage();

            Notification::make()
                ->title("Failed: {$command}")
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function resetOpcache(): void
    {
        if (! function_exists('opcache_reset')) {
            $this->lastResult = '— opcache extension not loaded on this server';
            Notification::make()
                ->title('OPcache extension not available')
                ->body('Touch your .env file or restart PHP-FPM from cPanel instead.')
                ->warning()
                ->send();
            return;
        }

        $ok = @opcache_reset();
        $this->lastResult = $ok
            ? '✓ OPcache reset'
            : '✗ opcache_reset() returned false (may be disabled by host)';

        Notification::make()
            ->title($ok ? 'OPcache reset' : 'OPcache reset failed')
            ->body($ok ? 'Bytecode cache flushed.' : 'Host may have disabled it. Try touching .env instead.')
            ->color($ok ? 'success' : 'danger')
            ->send();
    }

    protected function getViewData(): array
    {
        return [
            'opcacheAvailable' => function_exists('opcache_reset'),
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
        ];
    }
}
