<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PartnerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'People';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Partners';

    protected static ?string $modelLabel = 'Partner';

    protected static ?string $pluralModelLabel = 'Partners';

    /**
     * Scope this resource to partners only.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_category', User::CATEGORY_PARTNER);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Person')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Representative name')
                        ->required()
                        ->placeholder('Eng. Mohamed Ramadan')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\TextInput::make('phone')
                        ->tel()
                        ->maxLength(50),
                    Forms\Components\FileUpload::make('avatar_path')
                        ->label('Personal photo')
                        ->image()
                        ->avatar()
                        ->disk('public')
                        ->directory('avatars')
                        ->maxSize(2048),
                ]),

            Forms\Components\Section::make('Organization')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('organization')
                        ->label('Company / Organization')
                        ->required()
                        ->placeholder('Hassan Allam Group')
                        ->maxLength(191),
                    Forms\Components\TextInput::make('org_url')
                        ->label('Website')
                        ->url()
                        ->prefix('https://')
                        ->placeholder('hassanallam.com')
                        ->maxLength(255),
                    Forms\Components\FileUpload::make('org_logo_path')
                        ->label('Organization logo')
                        ->image()
                        ->disk('public')
                        ->directory('partner-logos')
                        ->imageResizeMode('contain')
                        ->maxSize(1024)
                        ->columnSpanFull()
                        ->helperText('Shown on the public landing page in the "Our Partners" section.'),
                    Forms\Components\Textarea::make('headline')
                        ->label('Headline')
                        ->rows(2)
                        ->maxLength(120)
                        ->columnSpanFull()
                        ->placeholder('e.g. Industry Partner — Hassan Allam Group'),
                ]),

            Forms\Components\Section::make('Login')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $operation) => $operation === 'create')
                        ->helperText(fn (string $operation) => $operation === 'edit'
                            ? 'Leave blank to keep current password'
                            : 'Share this password with the partner securely.')
                        ->maxLength(255),
                    Forms\Components\Placeholder::make('privileges_info')
                        ->label('Privileges')
                        ->content('Auto-granted: Judge + Mentor roles, plus assignment to every team in the active edition.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('org_logo_path')
                    ->label('Logo')
                    ->disk('public')
                    ->square()
                    ->size(48)
                    ->defaultImageUrl(fn () => null),
                Tables\Columns\TextColumn::make('organization')
                    ->label('Organization')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Representative')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('org_url')
                    ->label('Website')
                    ->url(fn ($state) => $state, true)
                    ->limit(28)
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('reset_password')
                    ->label('Reset password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $newPassword = Str::random(12);
                        $record->forceFill(['password' => Hash::make($newPassword)])->save();
                        Notification::make()
                            ->title('Password reset for ' . $record->name)
                            ->body('New password: ' . $newPassword . ' — copy NOW.')
                            ->success()
                            ->persistent()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('organization');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'edit' => Pages\EditPartner::route('/{record}/edit'),
        ];
    }
}
