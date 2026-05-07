<?php

namespace App\Filament\Resources\AssignmentResource\RelationManagers;

use App\Models\AssignmentSubmission;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    protected static ?string $title = 'Team Submissions';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Team')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('files_count')
                    ->label('Files')
                    ->counts('files')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('scores_count')
                    ->label('Graded by')
                    ->counts('scores')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('avg_score')
                    ->label('Avg score')
                    ->state(fn (AssignmentSubmission $r) => $r->loadMissing('scores')->averageScore())
                    ->placeholder('—')
                    ->alignCenter()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('first_submitted_at')
                    ->label('First submitted')
                    ->dateTime('M d H:i')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_activity_at')
                    ->label('Last activity')
                    ->dateTime('M d H:i')
                    ->since()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lastActivityBy.name')
                    ->label('By')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(60)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('last_activity_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalContent(fn (AssignmentSubmission $record) => view(
                        'filament.resources.assignment-resource.partials.submission-files',
                        ['submission' => $record->load('files.uploader', 'team')]
                    ))
                    ->modalHeading(fn (AssignmentSubmission $record) => $record->team->name . ' — files')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ]);
    }
}
