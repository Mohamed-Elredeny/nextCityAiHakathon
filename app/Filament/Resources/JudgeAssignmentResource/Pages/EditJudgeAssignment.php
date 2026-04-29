<?php

namespace App\Filament\Resources\JudgeAssignmentResource\Pages;

use App\Filament\Resources\JudgeAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJudgeAssignment extends EditRecord
{
    protected static string $resource = JudgeAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
