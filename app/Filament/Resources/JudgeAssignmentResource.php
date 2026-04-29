<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JudgeAssignmentResource\Pages;
use App\Models\JudgeAssignment;
use App\Models\Team;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JudgeAssignmentResource extends Resource
{
    protected static ?string $model = JudgeAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Hackathon';

    protected static ?int $navigationSort = 25;

    protected static ?string $navigationLabel = 'Judge Assignments';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('judge_id')
                ->label('Judge')
                ->options(fn () => User::query()
                    ->whereHas('roles', fn ($q) => $q->where('name', 'judge'))
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
            Forms\Components\Select::make('round')
                ->options(['round1' => 'Round 1', 'finals' => 'Finals'])
                ->required(),
            Forms\Components\Toggle::make('recused')
                ->label('Recused (conflict of interest)')
                ->live(),
            Forms\Components\Textarea::make('recused_reason')
                ->visible(fn ($get) => $get('recused'))
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judge.name')->label('Judge')->searchable()->sortable()->weight('semibold'),
                Tables\Columns\TextColumn::make('team.name')->label('Team')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('round')->badge()->color(fn (string $state) => $state === 'finals' ? 'warning' : 'info'),
                Tables\Columns\IconColumn::make('recused')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('round')->options(['round1' => 'Round 1', 'finals' => 'Finals']),
                Tables\Filters\SelectFilter::make('judge')
                    ->relationship('judge', 'name'),
                Tables\Filters\SelectFilter::make('team')
                    ->relationship('team', 'name'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('bulkAssign')
                    ->label('Bulk assign judge to teams')
                    ->icon('heroicon-o-user-plus')
                    ->modalHeading('Assign one judge to multiple teams')
                    ->modalSubmitActionLabel('Assign')
                    ->form([
                        Forms\Components\Select::make('judge_id')
                            ->label('Judge')
                            ->options(fn () => User::query()
                                ->whereHas('roles', fn ($q) => $q->where('name', 'judge'))
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
                        Forms\Components\Select::make('round')
                            ->options(['round1' => 'Round 1', 'finals' => 'Finals'])
                            ->default('round1')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $teamIds = array_filter((array) ($data['team_ids'] ?? []));
                        if (empty($teamIds)) {
                            Notification::make()
                                ->title('No teams selected')
                                ->warning()
                                ->send();
                            return;
                        }

                        $created = 0;
                        $existing = 0;
                        foreach ($teamIds as $teamId) {
                            $row = JudgeAssignment::firstOrCreate([
                                'judge_id' => $data['judge_id'],
                                'team_id' => $teamId,
                                'round' => $data['round'],
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
            ->defaultSort('round');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJudgeAssignments::route('/'),
            'create' => Pages\CreateJudgeAssignment::route('/create'),
            'edit' => Pages\EditJudgeAssignment::route('/{record}/edit'),
        ];
    }
}
