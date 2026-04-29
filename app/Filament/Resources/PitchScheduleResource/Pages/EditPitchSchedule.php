<?php

namespace App\Filament\Resources\PitchScheduleResource\Pages;

use App\Filament\Resources\PitchScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPitchSchedule extends EditRecord
{
    protected static string $resource = PitchScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
