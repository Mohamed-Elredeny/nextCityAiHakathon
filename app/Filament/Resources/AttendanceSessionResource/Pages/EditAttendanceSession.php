<?php

namespace App\Filament\Resources\AttendanceSessionResource\Pages;

use App\Filament\Resources\AttendanceSessionResource;
use App\Models\AttendanceSession;
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
                ->modalHeading(fn () => $this->record->name . ' — QR & Roster')
                ->modalWidth('5xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalContent(fn () => view(
                    'filament.resources.attendance-session-resource.partials.qr-and-roster',
                    [
                        'record' => $this->record->load('attendances.user'),
                        'checkInUrl' => $this->record->check_in_url,
                        'isOpen' => $this->record->isOpenForCheckIn(),
                        'roster' => $this->record->attendances()
                            ->with('user')
                            ->orderByDesc('checked_in_at')
                            ->get(),
                    ],
                )),
            Actions\DeleteAction::make(),
        ];
    }
}
