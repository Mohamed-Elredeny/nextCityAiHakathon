<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoterIpBlockResource\Pages;
use App\Models\VoterIpBlock;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VoterIpBlockResource extends Resource
{
    protected static ?string $model = VoterIpBlock::class;

    protected static ?string $navigationIcon = 'heroicon-o-no-symbol';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 90;

    protected static ?string $navigationLabel = 'Vote IP Blocks';

    protected static ?string $modelLabel = 'IP Block';

    protected static ?string $pluralModelLabel = 'IP Blocks';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),
                Tables\Columns\TextColumn::make('attempt_count')
                    ->label('Attempts')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state >= VoterIpBlock::ATTEMPT_LIMIT ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('blocked_until')
                    ->label('Blocked until')
                    ->dateTime('M d, H:i')
                    ->placeholder('— not blocked —')
                    ->color(fn ($state, VoterIpBlock $r) => $r->isBlocked() ? 'danger' : 'gray'),
                Tables\Columns\TextColumn::make('first_attempt_at')
                    ->label('First seen')
                    ->dateTime('M d, H:i')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('last_attempt_at')
                    ->label('Last seen')
                    ->dateTime('M d, H:i')
                    ->since(),
                Tables\Columns\TextColumn::make('reason')
                    ->limit(50)
                    ->wrap()
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_user_agent')
                    ->label('User-Agent')
                    ->limit(40)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('last_attempt_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('blocked_until')
                    ->label('Currently blocked')
                    ->placeholder('All')
                    ->trueLabel('Blocked')
                    ->falseLabel('Not blocked')
                    ->queries(
                        true: fn ($q) => $q->where('blocked_until', '>', now()),
                        false: fn ($q) => $q->where(fn ($q) => $q->whereNull('blocked_until')->orWhere('blocked_until', '<=', now())),
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('unblock')
                    ->label('Unblock')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->visible(fn (VoterIpBlock $r) => $r->isBlocked())
                    ->requiresConfirmation()
                    ->modalDescription(fn (VoterIpBlock $r) => 'Allow ' . $r->ip_address . ' to vote again. The attempt counter will also be reset.')
                    ->action(function (VoterIpBlock $record) {
                        $record->forceFill([
                            'blocked_until' => null,
                            'attempt_count' => 0,
                            'reason' => null,
                            'first_attempt_at' => null,
                        ])->save();
                        Notification::make()
                            ->title('IP unblocked')
                            ->body($record->ip_address . ' can vote again.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('extend')
                    ->label('Extend block')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->visible(fn (VoterIpBlock $r) => ! $r->isBlocked())
                    ->requiresConfirmation()
                    ->modalDescription('Block this IP for the next 24 hours.')
                    ->action(function (VoterIpBlock $record) {
                        $record->forceFill([
                            'blocked_until' => now()->addHours(VoterIpBlock::BLOCK_DURATION_HOURS),
                            'reason' => 'Manually blocked by admin',
                        ])->save();
                        Notification::make()
                            ->title('IP blocked')
                            ->body($record->ip_address . ' cannot vote for 24h.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('unblock_all')
                        ->label('Unblock selected')
                        ->icon('heroicon-o-shield-check')
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function (VoterIpBlock $r) {
                                $r->forceFill([
                                    'blocked_until' => null,
                                    'attempt_count' => 0,
                                    'reason' => null,
                                    'first_attempt_at' => null,
                                ])->save();
                            });
                            Notification::make()->title('Unblocked ' . $records->count() . ' IPs')->success()->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No vote attempts tracked yet')
            ->emptyStateDescription('IPs only get added here once they try to vote.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVoterIpBlocks::route('/'),
        ];
    }
}
