<?php

namespace App\Filament\Widgets;

use App\Models\Edition;
use App\Models\Score;
use App\Models\Team;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopTeamsLeaderboard extends TableWidget
{
    protected static ?string $heading = 'Top teams (locked scores)';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        $edition = Edition::active();

        return $table
            ->query(
                Team::query()
                    ->when($edition, fn (Builder $q) => $q->where('edition_id', $edition->id))
                    ->withAvg([
                        'scores as avg_score' => fn (Builder $q) => $q->whereNotNull('locked_at'),
                    ], 'weighted_total')
                    ->withCount([
                        'scores as locked_scores_count' => fn (Builder $q) => $q->whereNotNull('locked_at'),
                    ])
                    ->having('locked_scores_count', '>', 0)
                    ->orderByDesc('avg_score')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Team')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('avg_score')
                    ->label('Avg score')
                    ->numeric(2)
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 8 => 'success',
                        $state >= 6 => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('locked_scores_count')
                    ->label('Locked')
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('is_finalist')
                    ->label('Finalist')
                    ->boolean(),
            ])
            ->paginated(false)
            ->emptyStateHeading('No locked scores yet')
            ->emptyStateDescription('Leaderboard appears once judges lock their scores.');
    }
}
