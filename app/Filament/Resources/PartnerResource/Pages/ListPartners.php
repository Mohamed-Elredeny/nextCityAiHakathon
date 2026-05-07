<?php

namespace App\Filament\Resources\PartnerResource\Pages;

use App\Filament\Resources\PartnerResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPartners extends ListRecords
{
    protected static string $resource = PartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('sync_assignments')
                ->label('Sync team assignments')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('Make sure every partner is assigned as judge + mentor on every team in the active edition.')
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
