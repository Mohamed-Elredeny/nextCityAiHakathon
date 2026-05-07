<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BoardMemberResource\Pages;
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

class BoardMemberResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'People';

    protected static ?int $navigationSort = 25;

    protected static ?string $navigationLabel = 'Board Members';

    protected static ?string $modelLabel = 'Board Member';

    protected static ?string $pluralModelLabel = 'Board Members';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_category', User::CATEGORY_BOARD);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identity')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Full name')
                        ->required()
                        ->placeholder('Dr. Hesham Gaber')
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
                        ->default('AIU')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('headline')
                        ->placeholder('e.g. Board Member, Faculty of Engineering')
                        ->maxLength(120)
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('avatar_path')
                        ->label('Photo')
                        ->image()
                        ->avatar()
                        ->disk('public')
                        ->directory('avatars')
                        ->maxSize(2048)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('bio')
                        ->rows(3)
                        ->maxLength(2000)
                        ->columnSpanFull(),
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
                            : 'Share with the board member securely.')
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
                Tables\Columns\ImageColumn::make('avatar_path')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('email')
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('institution')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBoardMembers::route('/'),
            'create' => Pages\CreateBoardMember::route('/create'),
            'edit' => Pages\EditBoardMember::route('/{record}/edit'),
        ];
    }
}
