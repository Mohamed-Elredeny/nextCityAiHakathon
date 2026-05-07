<?php

namespace App\Filament\Resources\AttendanceSessionResource\Pages;

use App\Filament\Resources\AttendanceSessionResource;
use App\Models\AttendanceSession;
use Filament\Resources\Pages\Page;

class ShowAttendanceQr extends Page
{
    protected static string $resource = AttendanceSessionResource::class;

    protected static string $view = 'filament.resources.attendance-session-resource.pages.show-attendance-qr';

    public AttendanceSession $record;

    public function mount(int|string $record): void
    {
        $this->record = AttendanceSession::with([
            'attendances.user',
        ])->findOrFail($record);
    }

    public function getTitle(): string
    {
        return $this->record->name . ' — QR & Roster';
    }

    public function getCheckInUrl(): string
    {
        return $this->record->check_in_url;
    }

    public function getRoster(): array
    {
        return $this->record->attendances()
            ->with('user')
            ->orderByDesc('checked_in_at')
            ->get()
            ->all();
    }
}
