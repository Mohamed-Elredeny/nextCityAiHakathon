<?php

namespace App\Filament\Resources\PhaseResource\Pages;

use App\Filament\Resources\PhaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPhase extends EditRecord
{
    protected static string $resource = PhaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
