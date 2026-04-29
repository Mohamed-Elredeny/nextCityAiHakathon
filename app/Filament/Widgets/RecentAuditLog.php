<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentAuditLog extends TableWidget
{
    protected static ?string $heading = 'Recent activity';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AuditLog::query()
                    ->with('user')
                    ->orderByDesc('created_at')
                    ->limit(15)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at?->format('Y-m-d H:i:s'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Actor')
                    ->placeholder('System')
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'reject'), str_contains($state, 'delete') => 'danger',
                        str_contains($state, 'flag'), str_contains($state, 'recus') => 'warning',
                        str_contains($state, 'lock'), str_contains($state, 'submit'), str_contains($state, 'accept') => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(fn (?string $state, $record) => $state
                        ? class_basename($state) . ' #' . $record->subject_id
                        : '—'),
            ])
            ->paginated(false)
            ->emptyStateHeading('No activity yet')
            ->emptyStateDescription('Audit log entries will appear here as users act.');
    }
}
