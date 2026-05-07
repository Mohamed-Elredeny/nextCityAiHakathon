<?php

namespace App\Filament\Resources\AttendanceSessionResource\RelationManagers;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    protected static ?string $title = 'Roster';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('Participant')
                ->relationship('user', 'name')
                ->searchable(['name', 'email'])
                ->preload()
                ->required()
                ->helperText('Pick the participant. Manual check-ins bypass the profile-completeness gate.'),
            Forms\Components\Select::make('source')
                ->options([
                    Attendance::SOURCE_SELF => 'Self check-in',
                    Attendance::SOURCE_ADMIN => 'Admin manual',
                ])
                ->default(Attendance::SOURCE_ADMIN)
                ->required(),
            Forms\Components\DateTimePicker::make('checked_in_at')
                ->label('Checked in at')
                ->default(now())
                ->seconds(false)
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\ImageColumn::make('user.avatar_path')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn () => null)
                    ->disk('public')
                    ->size(36),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.institution')
                    ->label('Institution')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Phone')
                    ->placeholder('—')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('checked_in_at')
                    ->label('Checked in')
                    ->dateTime('M d, H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('source')
                    ->badge()
                    ->color(fn ($state) => $state === Attendance::SOURCE_ADMIN ? 'warning' : 'success'),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('checkedInBy.name')
                    ->label('Added by (admin)')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('checked_in_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        Attendance::SOURCE_SELF => 'Self check-in',
                        Attendance::SOURCE_ADMIN => 'Admin manual',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Manual check-in')
                    ->icon('heroicon-o-user-plus')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['source'] = $data['source'] ?? Attendance::SOURCE_ADMIN;
                        $data['checked_in_by'] = Auth::id();
                        return $data;
                    })
                    ->using(function (array $data) {
                        /** @var AttendanceSession $session */
                        $session = $this->getOwnerRecord();
                        // Honour the unique (session_id, user_id) constraint gracefully.
                        $existing = Attendance::where('attendance_session_id', $session->id)
                            ->where('user_id', $data['user_id'])
                            ->first();
                        if ($existing) {
                            Notification::make()
                                ->title('Already checked in')
                                ->body('This participant is already on the roster.')
                                ->warning()
                                ->send();
                            return $existing;
                        }
                        return $session->attendances()->create($data);
                    }),

                Tables\Actions\Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        return $this->streamRosterCsv();
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label('Remove'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No check-ins yet')
            ->emptyStateDescription('When participants scan the QR and check in, they will appear here.');
    }

    protected function streamRosterCsv(): StreamedResponse
    {
        /** @var AttendanceSession $session */
        $session = $this->getOwnerRecord();
        $filename = 'attendance-' . \Illuminate\Support\Str::slug($session->name) . '-' . now()->format('Ymd-His') . '.csv';

        $rows = Attendance::with(['user', 'checkedInBy'])
            ->where('attendance_session_id', $session->id)
            ->orderBy('checked_in_at')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // BOM so Excel reads UTF-8 correctly
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                '#', 'Name', 'Email', 'Institution', 'Phone', 'National ID',
                'Checked-in at', 'Source', 'IP', 'Added by',
            ]);
            $i = 1;
            foreach ($rows as $a) {
                fputcsv($out, [
                    $i++,
                    $a->user?->name ?? '',
                    $a->user?->email ?? '',
                    $a->user?->institution ?? '',
                    $a->user?->phone ?? '',
                    $a->user?->national_id ?? '',
                    optional($a->checked_in_at)?->format('Y-m-d H:i:s'),
                    $a->source,
                    $a->ip_address ?? '',
                    $a->checkedInBy?->name ?? '',
                ]);
            }
            fclose($out);
        }, $filename, $headers);
    }
}
