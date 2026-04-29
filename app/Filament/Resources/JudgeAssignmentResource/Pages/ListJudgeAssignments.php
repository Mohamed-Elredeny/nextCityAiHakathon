<?php

namespace App\Filament\Resources\JudgeAssignmentResource\Pages;

use App\Filament\Resources\JudgeAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJudgeAssignments extends ListRecords
{
    protected static string $resource = JudgeAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
