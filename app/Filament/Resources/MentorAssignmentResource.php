<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MentorAssignmentResource\Pages;
use App\Models\MentorAssignment;
use App\Models\Team;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MentorAssignmentResource extends Resource
{
    protected static ?string $model = MentorAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Hackathon';

    protected static ?int $navigationSort = 26;

    protected static ?string $navigationLabel = 'Mentor Assignments';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('mentor_id')
                ->label('Mentor')
                ->options(fn () => User::query()
                    ->whereHas('roles', fn ($q) => $q->where('name', 'mentor'))
                    ->orderBy('name')
                    ->pluck('name', 'id'))
                ->required()
                ->searchable(),
            Forms\Components\Select::make('team_id')
                ->label('Team')
                ->relationship('team', 'name')
                ->required()
                ->searchable()
                ->preload(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mentor.name')->label('Mentor')->searchable()->sortable()->weight('semibold'),
                Tables\Columns\TextColumn::make('team.name')->label('Team')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Assigned')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('mentor')->relationship('mentor', 'name'),
                Tables\Filters\SelectFilter::make('team')->relationship('team', 'name'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('bulkAssign')
                    ->label('Bulk assign mentor to teams')
                    ->icon('heroicon-o-user-plus')
                    ->modalHeading('Assign one mentor to multiple teams')
                    ->modalSubmitActionLabel('Assign')
                    ->form([
                        Forms\Components\Select::make('mentor_id')
                            ->label('Mentor')
                            ->options(fn () => User::query()
                                ->whereHas('roles', fn ($q) => $q->where('name', 'mentor'))
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('team_ids')
                            ->label('Teams')
                            ->multiple()
                            ->options(fn () => Team::query()->orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->minItems(1)
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (array $data) {
                        $teamIds = array_filter((array) ($data['team_ids'] ?? []));
                        if (empty($teamIds)) {
                            Notification::make()->title('No teams selected')->warning()->send();
                            return;
                        }

                        $alreadyAssigned = MentorAssignment::where('mentor_id', $data['mentor_id'])->count();
                        $totalAfter = $alreadyAssigned + count($teamIds);
                        if ($totalAfter > 5) {
                            Notification::make()
                                ->title("Heads up: this mentor will be on {$totalAfter} teams")
                                ->body('More than 5 teams per mentor usually thins the quality of feedback.')
                                ->warning()
                                ->send();
                        }

                        $created = 0;
                        $existing = 0;
                        foreach ($teamIds as $teamId) {
                            $row = MentorAssignment::firstOrCreate([
                                'mentor_id' => $data['mentor_id'],
                                'team_id' => $teamId,
                            ]);
                            $row->wasRecentlyCreated ? $created++ : $existing++;
                        }

                        Notification::make()
                            ->title("Assigned {$created} new · {$existing} already existed")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMentorAssignments::route('/'),
            'create' => Pages\CreateMentorAssignment::route('/create'),
            'edit' => Pages\EditMentorAssignment::route('/{record}/edit'),
        ];
    }
}
