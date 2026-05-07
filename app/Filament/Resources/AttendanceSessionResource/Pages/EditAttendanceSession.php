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
            Actions\Action::make('qr')
                ->label('Show QR & Roster')
                ->icon('heroicon-o-qr-code')
                ->color('primary')
                ->url(fn () => ShowAttendanceQr::getUrl(['record' => $this->record])),
            Actions\DeleteAction::make(),
        ];
    }
}
