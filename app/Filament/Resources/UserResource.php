<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'People';

    protected static ?int $navigationSort = 10;

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('registration_status', 'pending')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identity')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\TextInput::make('phone')
                        ->tel()
                        ->maxLength(50),
                    Forms\Components\TextInput::make('institution')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('national_id')
                        ->maxLength(50)
                        ->label('National ID / Passport'),
                    Forms\Components\TextInput::make('headline')
                        ->maxLength(120)
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('avatar_path')
                        ->label('Avatar')
                        ->image()
                        ->avatar()
                        ->disk('public')
                        ->directory('avatars')
                        ->maxSize(2048),
                    Forms\Components\Textarea::make('bio')
                        ->rows(4)
                        ->maxLength(2000)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Access')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('registration_status')
                        ->label('Account status')
                        ->options([
                            'approved' => 'Approved',
                            'pending'  => 'Pending review',
                            'rejected' => 'Rejected',
                        ])
                        ->default('approved')
                        ->required(),
                    Forms\Components\TextInput::make('requested_role')
                        ->label('Self-requested role')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Select::make('roles')
                        ->label('Roles')
                        ->multiple()
                        ->relationship('roles', 'name')
                        ->preload()
                        ->columnSpanFull()
                        ->helperText('Mentor / judge applicants only get the role assigned once you approve them.'),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $operation) => $operation === 'create')
                        ->helperText(fn (string $operation) => $operation === 'edit'
                            ? 'Leave blank to keep current password'
                            : null)
                        ->maxLength(255),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('registration_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),
                Tables\Columns\TextColumn::make('requested_role')
                    ->label('Requested')
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'judge' => 'info',
                        'mentor' => 'success',
                        'team_leader' => 'primary',
                        'team_member' => 'gray',
                        'voter' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('institution')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('registration_status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Pending review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record) => $record->registration_status === 'pending')
                    ->requiresConfirmation()
                    ->modalDescription(fn (User $record) => 'Approve ' . $record->name . ' as ' . ($record->requested_role ?? 'participant') . '?')
                    ->action(function (User $record) {
                        static::approveUser($record);
                        Notification::make()
                            ->title('Approved')
                            ->body($record->name . ' can now sign in as ' . ($record->requested_role ?? 'participant') . '.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (User $record) => $record->registration_status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->update([
                            'registration_status' => 'rejected',
                            'approved_at' => null,
                        ]);
                        Notification::make()
                            ->title('Rejected')
                            ->body($record->name . ' will not be able to sign in.')
                            ->warning()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->registration_status === 'pending') {
                                    static::approveUser($record);
                                    $count++;
                                }
                            }
                            Notification::make()
                                ->title($count . ' account(s) approved')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Mark the user approved and grant the role they originally requested.
     */
    protected static function approveUser(User $user): void
    {
        $role = match ($user->requested_role) {
            'mentor' => 'mentor',
            'judge'  => 'judge',
            default  => 'team_member',
        };

        $user->update([
            'registration_status' => 'approved',
            'approved_at' => now(),
        ]);

        if (! $user->hasRole($role)) {
            $user->syncRoles([$role]);
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
