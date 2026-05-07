<?php

namespace App\Filament\Resources\AttendanceSessionResource\Pages;

use App\Filament\Resources\AttendanceSessionResource;
use App\Models\AttendanceSession;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ShowAttendanceQr extends Page
{
    protected static string $resource = AttendanceSessionResource::class;

    protected static string $view = 'filament.resources.attendance-session-resource.pages.show-attendance-qr';

    /**
     * Hide from sidebar — only reachable via the row action / direct URL.
     */
    protected static bool $shouldRegisterNavigation = false;

    /**
     * Filament's resource pages use standard Laravel route params (string).
     * We avoid model binding so that any 404 is raised explicitly inside mount(),
     * not silently by Laravel's route resolver.
     */
    public ?Model $record = null;

    public function mount($record = null): void
    {
        if ($record === null) {
            abort(404, 'Missing attendance session id.');
        }

        $session = AttendanceSession::with(['attendances.user'])->find($record);

        if (! $session) {
            throw (new ModelNotFoundException())->setModel(AttendanceSession::class, [$record]);
        }

        $this->record = $session;
    }

    public function getRecord(): Model
    {
        return $this->record;
    }

    public function getTitle(): string
    {
        return ($this->record?->name ?? 'Attendance Session') . ' — QR & Roster';
    }

    public function getCheckInUrl(): string
    {
        return $this->record?->check_in_url ?? '';
    }

    public function getRoster(): array
    {
        if (! $this->record) {
            return [];
        }
        return $this->record->attendances()
            ->with('user')
            ->orderByDesc('checked_in_at')
            ->get()
            ->all();
    }
}
