<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignmentResource\Pages;
use App\Filament\Resources\AssignmentResource\RelationManagers;
use App\Models\Assignment;
use App\Models\Edition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Hackathon';

    protected static ?int $navigationSort = 25;

    protected static ?string $modelLabel = 'Assignment';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Assignment')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(191),
                    Forms\Components\TextInput::make('slug')
                        ->maxLength(191)
                        ->helperText('Auto-generated if blank.'),
                    Forms\Components\Select::make('edition_id')
                        ->label('Edition')
                        ->relationship('edition', 'name')
                        ->default(fn () => Edition::active()?->id)
                        ->searchable()
                        ->preload(),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active (visible to teams)')
                        ->default(true),
                    Forms\Components\Textarea::make('description')
                        ->rows(4)
                        ->columnSpanFull()
                        ->helperText('Markdown not supported — plain text only.'),
                ]),

            Forms\Components\Section::make('Window & limits')
                ->columns(2)
                ->schema([
                    Forms\Components\DateTimePicker::make('opens_at')
                        ->label('Opens at')
                        ->seconds(false)
                        ->helperText('Optional. Leave blank to open immediately when active.'),
                    Forms\Components\DateTimePicker::make('deadline_at')
                        ->label('Deadline')
                        ->seconds(false)
                        ->helperText('After this time, teams cannot upload more files.'),
                    Forms\Components\TextInput::make('max_files')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(50)
                        ->default(10)
                        ->required(),
                    Forms\Components\TextInput::make('max_file_size_kb')
                        ->label('Max file size (KB)')
                        ->numeric()
                        ->default(20480)
                        ->required()
                        ->helperText('Server upload_max_filesize ultimately wins. 20480 KB = 20 MB.'),
                    Forms\Components\TagsInput::make('accepted_extensions')
                        ->label('Accepted extensions')
                        ->placeholder('e.g. pdf, png, zip')
                        ->helperText('Leave empty to accept any extension.')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->helperText('Lower appears first.'),
                ]),

            Forms\Components\Section::make('Grading')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('max_score')
                        ->label('Max score')
                        ->numeric()
                        ->minValue(1)
                        ->default(100)
                        ->required()
                        ->helperText('Judges grade out of this number (e.g. 100, 20).'),
                    Forms\Components\Toggle::make('release_grades')
                        ->label('Release grades to teams')
                        ->default(true)
                        ->helperText('Off = grades are entered but hidden from teams until you switch on.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('opens_at')
                    ->label('Opens')
                    ->dateTime('M d H:i')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('deadline_at')
                    ->label('Deadline')
                    ->dateTime('M d H:i')
                    ->color(fn (Assignment $r) => $r->isPastDeadline() ? 'danger' : null)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('submissions_count')
                    ->label('Teams submitted')
                    ->counts('submissions')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
        ];
    }
}
