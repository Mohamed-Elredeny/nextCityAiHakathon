<?php

namespace App\Filament\Pages;

use App\Models\Team;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class TeamLogosGallery extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Galleries';

    protected static ?string $navigationLabel = 'Team Logos';

    protected static ?string $title = 'Team Logos Gallery';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.team-logos-gallery';

    public function getHeading(): string|Htmlable
    {
        return 'Team Logos';
    }

    public function getSubheading(): string|Htmlable|null
    {
        $total = Team::count();
        $withLogo = Team::whereNotNull('logo_path')->count();
        return "{$withLogo} of {$total} teams have uploaded a logo.";
    }

    protected function getViewData(): array
    {
        $teams = Team::with('edition')
            ->orderBy('name')
            ->get();

        return [
            'teams' => $teams,
        ];
    }
}
