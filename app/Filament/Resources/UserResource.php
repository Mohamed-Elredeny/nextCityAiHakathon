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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

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

            Forms\Components\Section::make('Board / Partner')
                ->description('Optional — only set if this user is a board member or industry partner.')
                ->columns(2)
                ->collapsible()
                ->schema([
                    Forms\Components\Select::make('user_category')
                        ->label('Category')
                        ->options(User::USER_CATEGORIES)
                        ->placeholder('— Regular participant —')
                        ->live(),
                    Forms\Components\TextInput::make('organization')
                        ->label('Organization / Company')
                        ->helperText('e.g. "Hassan Allam Group", "Onyx Systems".')
                        ->maxLength(191),
                    Forms\Components\TextInput::make('org_url')
                        ->label('Organization website')
                        ->url()
                        ->prefix('https://')
                        ->maxLength(255),
                    Forms\Components\FileUpload::make('org_logo_path')
                        ->label('Organization logo')
                        ->image()
                        ->disk('public')
                        ->directory('partner-logos')
                        ->imageResizeMode('contain')
                        ->maxSize(1024)
                        ->helperText('Shown in the public partners ribbon and landing footer.'),
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
                    // Use plain options (not ->relationship) to avoid Filament
                    // eagerly invoking the Spatie morphToMany on a null parent
                    // during table re-renders, which crashes with
                    // "Call to a member function newQueryWithoutRelationships() on null".
                    Forms\Components\Select::make('roles')
                        ->label('Roles')
                        ->multiple()
                        ->options(fn () => Role::query()->orderBy('name')->pluck('name', 'name')->all())
                        ->afterStateHydrated(function (Forms\Components\Select $component, ?User $record) {
                            $component->state($record ? $record->roles->pluck('name')->all() : []);
                        })
                        ->dehydrated(false)
                        ->saveRelationshipsUsing(function (User $record, $state) {
                            $record->syncRoles($state ?? []);
                        })
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
                Tables\Columns\TextColumn::make('user_category')
                    ->label('Category')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? (User::USER_CATEGORIES[$state] ?? $state) : '—')
                    ->color(fn (?string $state): string => match ($state) {
                        User::CATEGORY_BOARD => 'info',
                        User::CATEGORY_PARTNER => 'warning',
                        default => 'gray',
                    })
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('organization')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('registration_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),
                Tables\Columns\TextColumn::make('requested_role')
                    ->label('Requested')
                    ->badge()
                    ->toggleable(),
                // Render roles directly off the loaded model rather than via a
                // relationship dot-path column (which forces Filament to do
                // additional joins that have been observed to fail in this
                // app's deployment).
                Tables\Columns\TextColumn::make('roles_list')
                    ->label('Roles')
                    ->badge()
                    ->state(fn (User $record) => $record->roles->pluck('name')->all())
                    ->color(fn (?string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'judge'       => 'info',
                        'mentor'      => 'success',
                        'team_leader' => 'primary',
                        'team_member' => 'gray',
                        'voter'       => 'gray',
                        default       => 'gray',
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
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options(fn () => Role::query()->orderBy('name')->pluck('name', 'name')->all())
                    ->query(function ($query, array $data) {
                        if (! filled($data['value'] ?? null)) {
                            return $query;
                        }
                        return $query->whereHas('roles', fn ($q) => $q->where('name', $data['value']));
                    }),
                Tables\Filters\SelectFilter::make('user_category')
                    ->label('Category')
                    ->options(User::USER_CATEGORIES),
            ])
            ->actions([
                Tables\Actions\Action::make('reset_password')
                    ->label('Reset password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Generate a new temporary password')
                    ->modalDescription(fn (User $record) => 'A new random password will replace ' . $record->name . "'s current one. The new password will be shown ONCE — copy it before closing the notification.")
                    ->modalSubmitActionLabel('Generate')
                    ->action(function (User $record) {
                        $newPassword = \Illuminate\Support\Str::random(12);
                        $record->forceFill(['password' => Hash::make($newPassword)])->save();

                        Notification::make()
                            ->title('Password reset for ' . $record->name)
                            ->body('New password: ' . $newPassword . ' — copy NOW, it will not be shown again.')
                            ->success()
                            ->persistent()
                            ->send();
                    }),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (?User $record) => $record && $record->registration_status === 'pending')
                    ->requiresConfirmation()
                    ->modalDescription(fn (?User $record) => $record
                        ? 'Approve ' . $record->name . ' as ' . ($record->requested_role ?? 'participant') . '?'
                        : null)
                    ->action(function (?User $record) {
                        if (! $record) {
                            Notification::make()->title('User not found')->danger()->send();
                            return;
                        }
                        try {
                            static::approveUser($record);
                            Notification::make()
                                ->title('Approved')
                                ->body($record->name . ' can now sign in as ' . ($record->requested_role ?? 'participant') . '.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            \Log::error('Approve user failed', ['user_id' => $record->id, 'err' => $e->getMessage()]);
                            Notification::make()
                                ->title('Approval failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (?User $record) => $record && $record->registration_status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (?User $record) {
                        if (! $record) {
                            return;
                        }
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
        $fresh = User::find($user->getKey());
        if (! $fresh) {
            return;
        }

        $roleName = match ($fresh->requested_role) {
            'mentor' => 'mentor',
            'judge'  => 'judge',
            default  => 'team_member',
        };

        Role::findOrCreate($roleName, 'web');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $fresh->update([
            'registration_status' => 'approved',
            'approved_at' => now(),
        ]);

        $fresh->syncRoles([$roleName]);
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
