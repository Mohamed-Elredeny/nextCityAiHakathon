<?php

namespace App\Filament\Resources\BoardMemberResource\Pages;

use App\Filament\Resources\BoardMemberResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListBoardMembers extends ListRecords
{
    protected static string $resource = BoardMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('sync_assignments')
                ->label('Sync team assignments')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('Make sure every board member is assigned as judge + mentor on every team in the active edition.')
                ->action(function () {
                    $seeder = new \Database\Seeders\BoardAndPartnersSeeder();
                    $summary = $seeder->assignBoardAndPartnersToAllTeams();
                    Notification::make()
                        ->title('Assignments synced')
                        ->body('Judges added: ' . ($summary['judges_added'] ?? 0) . ' · Mentors added: ' . ($summary['mentors_added'] ?? 0))
                        ->success()
                        ->send();
                }),
        ];
    }
}
