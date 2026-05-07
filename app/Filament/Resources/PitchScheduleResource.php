<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PitchScheduleResource\Pages;
use App\Models\Edition;
use App\Models\PitchSchedule;
use App\Services\PitchScheduleService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PitchScheduleResource extends Resource
{
    protected static ?string $model = PitchSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static ?string $navigationGroup = 'Hackathon';

    protected static ?int $navigationSort = 35;

    protected static ?string $navigationLabel = 'Pitch Schedule';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('team_id')->relationship('team', 'name')->required()->searchable(),
            Forms\Components\Select::make('round')->options(['round1' => 'Round 1', 'finals' => 'Finals'])->required(),
            Forms\Components\Select::make('room')
                ->options(collect(\App\Models\PitchSchedule::ROOMS)->mapWithKeys(fn ($r) => [$r => 'Room ' . $r])->all())
                ->placeholder('—')
                ->helperText('Day-2 round 1 has 3 parallel rooms. Leave blank for finals (single room).'),
            Forms\Components\TextInput::make('slot_index')->numeric()->required(),
            Forms\Components\DateTimePicker::make('scheduled_start'),
            Forms\Components\DateTimePicker::make('started_at')->disabled(),
            Forms\Components\DateTimePicker::make('ended_at')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('room')
                    ->label('Room')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'A' => 'info', 'B' => 'warning', 'C' => 'success', default => 'gray',
                    })
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('slot_index')->label('#')->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('team.name')->label('Team')->searchable()->weight('semibold'),
                Tables\Columns\TextColumn::make('round')->badge()->color(fn (string $state) => $state === 'finals' ? 'warning' : 'info'),
                Tables\Columns\TextColumn::make('scheduled_start')->dateTime('H:i')->label('Scheduled'),
                Tables\Columns\TextColumn::make('started_at')->dateTime('H:i:s')->label('Started')->placeholder('—'),
                Tables\Columns\TextColumn::make('ended_at')->dateTime('H:i:s')->label('Ended')->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->ended_at) return 'completed';
                        if ($record->started_at) return 'live';
                        return 'pending';
                    })
                    ->color(fn (string $state) => match ($state) {
                        'live' => 'success', 'completed' => 'gray', 'pending' => 'warning', default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('round')->options(['round1' => 'Round 1', 'finals' => 'Finals']),
                Tables\Filters\SelectFilter::make('room')
                    ->options(collect(\App\Models\PitchSchedule::ROOMS)->mapWithKeys(fn ($r) => [$r => 'Room ' . $r])->all()),
            ])
            ->defaultSort('scheduled_start')
            ->headerActions([
                Tables\Actions\Action::make('generate')
                    ->label('Generate schedule')
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('round')->options(['round1' => 'Round 1', 'finals' => 'Finals'])->required(),
                        Forms\Components\Select::make('order')->options([
                            'random' => 'Random', 'name' => 'Alphabetical', 'score_desc' => 'By Round 1 score (desc)',
                        ])->default('random')->required(),
                        Forms\Components\DateTimePicker::make('start_at')->label('First slot starts at')->required(),
                    ])
                    ->action(function (array $data) {
                        $edition = Edition::active();
                        $count = app(PitchScheduleService::class)->generate(
                            $edition, $data['round'], $data['order'],
                            \Carbon\Carbon::parse($data['start_at']),
                        );
                        \Filament\Notifications\Notification::make()
                            ->title("Generated {$count} slots")->success()->send();
                    }),
                Tables\Actions\Action::make('promoteFinalists')
                    ->label('Promote top 5 to finals')
                    ->icon('heroicon-o-trophy')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function () {
                        $ids = app(PitchScheduleService::class)->promoteFinalists(Edition::active());
                        \Filament\Notifications\Notification::make()
                            ->title('Finalists promoted')
                            ->body(count($ids) . ' team(s) marked as finalists.')
                            ->success()->send();
                    }),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('start')
                    ->label('Start')->icon('heroicon-o-play')->color('success')
                    ->visible(fn ($record) => !$record->started_at)
                    ->requiresConfirmation()
                    ->action(fn ($record) => app(PitchScheduleService::class)->start($record)),
                Tables\Actions\Action::make('end')
                    ->label('End')->icon('heroicon-o-stop')->color('warning')
                    ->visible(fn ($record) => $record->started_at && !$record->ended_at)
                    ->requiresConfirmation()
                    ->action(fn ($record) => app(PitchScheduleService::class)->end($record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('slot_index');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPitchSchedules::route('/'),
            'create' => Pages\CreatePitchSchedule::route('/create'),
            'edit' => Pages\EditPitchSchedule::route('/{record}/edit'),
        ];
    }
}
