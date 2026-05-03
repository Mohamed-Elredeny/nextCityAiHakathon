<?php

namespace App\Filament\Resources\MentorAssignmentResource\Pages;

use App\Filament\Resources\MentorAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMentorAssignment extends EditRecord
{
    protected static string $resource = MentorAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
