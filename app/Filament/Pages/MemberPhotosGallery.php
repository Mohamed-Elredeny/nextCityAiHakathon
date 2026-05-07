<?php

namespace App\Filament\Pages;

use App\Models\Team;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class MemberPhotosGallery extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Galleries';

    protected static ?string $navigationLabel = 'Member Photos';

    protected static ?string $title = 'Member Photos Gallery';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.member-photos-gallery';

    public function getHeading(): string|Htmlable
    {
        return 'Member Photos';
    }

    public function getSubheading(): string|Htmlable|null
    {
        $totalUsers = User::count();
        $withAvatar = User::whereNotNull('avatar_path')->count();
        return "{$withAvatar} of {$totalUsers} users have uploaded a profile photo.";
    }

    protected function getViewData(): array
    {
        $teams = Team::with(['members' => function ($q) {
            $q->orderBy('users.name');
        }, 'leader'])
            ->orderBy('name')
            ->get();

        $unassigned = User::doesntHave('teams')
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['team_leader', 'team_member']);
            })
            ->orderBy('name')
            ->get();

        return [
            'teams' => $teams,
            'unassigned' => $unassigned,
        ];
    }
}
