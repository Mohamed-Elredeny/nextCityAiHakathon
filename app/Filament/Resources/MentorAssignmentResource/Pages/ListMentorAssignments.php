<?php

namespace App\Filament\Resources\MentorAssignmentResource\Pages;

use App\Filament\Resources\MentorAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMentorAssignments extends ListRecords
{
    protected static string $resource = MentorAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
