<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceSessionResource\Pages;
use App\Models\AttendanceSession;
use App\Models\Edition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AttendanceSessionResource extends Resource
{
    protected static ?string $model = AttendanceSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationGroup = 'Hackathon';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Attendance';

    protected static ?string $modelLabel = 'Attendance Session';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Session')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(120),
                    Forms\Components\Select::make('type')
                        ->options(AttendanceSession::TYPES)
                        ->default('day1')
                        ->required(),
                    Forms\Components\Select::make('edition_id')
                        ->label('Edition')
                        ->relationship('edition', 'name')
                        ->default(fn () => Edition::active()?->id)
                        ->searchable()
                        ->preload(),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active (open for check-in)')
                        ->default(true),
                    Forms\Components\DateTimePicker::make('starts_at')
                        ->label('Opens at')
                        ->seconds(false)
                        ->helperText('Optional — restrict check-in window start.'),
                    Forms\Components\DateTimePicker::make('ends_at')
                        ->label('Closes at')
                        ->seconds(false)
                        ->helperText('Optional — restrict check-in window end.'),
                    Forms\Components\Textarea::make('notes')
                        ->rows(2)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('token')
                        ->label('Token (auto-generated)')
                        ->disabled()
                        ->dehydrated(false)
                        ->visible(fn ($record) => filled($record))
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => AttendanceSession::TYPES[$state] ?? Str::title($state)),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Opens')
                    ->dateTime('M d H:i')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Closes')
                    ->dateTime('M d H:i')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('attendances_count')
                    ->label('Checked-in')
                    ->counts('attendances')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(AttendanceSession::TYPES),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\Action::make('qr')
                    ->label('QR & Roster')
                    ->icon('heroicon-o-qr-code')
                    ->color('primary')
                    ->modalHeading(fn (AttendanceSession $record) => $record->name . ' — QR & Roster')
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn (AttendanceSession $record) => view(
                        'filament.resources.attendance-session-resource.partials.qr-and-roster',
                        [
                            'record' => $record->load('attendances.user'),
                            'checkInUrl' => $record->check_in_url,
                            'isOpen' => $record->isOpenForCheckIn(),
                            'roster' => $record->attendances()
                                ->with('user')
                                ->orderByDesc('checked_in_at')
                                ->get(),
                        ],
                    )),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('starts_at');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendanceSessions::route('/'),
            'create' => Pages\CreateAttendanceSession::route('/create'),
            'edit' => Pages\EditAttendanceSession::route('/{record}/edit'),
        ];
    }
}
