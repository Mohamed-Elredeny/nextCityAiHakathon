<?php

namespace App\Filament\Resources\AttendanceSessionResource\Pages;

use App\Filament\Resources\AttendanceSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceSession extends EditRecord
{
    protected static string $resource = AttendanceSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('show_qr')
                ->label('Show QR')
                ->icon('heroicon-o-qr-code')
                ->color('primary')
                ->url(fn () => route('attendance.qr-image', $this->record->token))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }
}
