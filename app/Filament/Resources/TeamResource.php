<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamResource\Pages;
use App\Filament\Resources\TeamResource\RelationManagers;
use App\Models\Edition;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Hackathon';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Team')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('slug')
                        ->maxLength(255)
                        ->helperText('Auto-generated if left blank.'),
                    Forms\Components\TextInput::make('tagline')
                        ->maxLength(160)
                        ->columnSpanFull()
                        ->helperText('One-line pitch shown on the leaderboard.'),
                    Forms\Components\FileUpload::make('logo_path')
                        ->label('Logo')
                        ->image()
                        ->disk('public')
                        ->directory('team-logos')
                        ->imageResizeMode('cover')
                        ->maxSize(1024),
                    Forms\Components\FileUpload::make('banner_path')
                        ->label('Banner')
                        ->image()
                        ->disk('public')
                        ->directory('team-banners')
                        ->maxSize(3072),
                    Forms\Components\Select::make('edition_id')
                        ->label('Edition')
                        ->relationship('edition', 'name')
                        ->required()
                        ->default(fn () => Edition::active()?->id)
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('theme_id')
                        ->label('Theme')
                        ->relationship('theme', 'name', fn ($query, $get) => $query->where('edition_id', $get('edition_id')))
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('leader_id')
                        ->label('Team Leader')
                        ->relationship(
                            'leader',
                            'name',
                            fn ($query) => $query->whereHas('roles', fn ($q) => $q->whereIn('name', ['team_leader', 'team_member']))
                        )
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\TextInput::make('email')->email()->required()->unique('users', 'email'),
                            Forms\Components\TextInput::make('phone'),
                            Forms\Components\TextInput::make('institution'),
                        ])
                        ->createOptionUsing(function (array $data) {
                            $user = \App\Models\User::create([
                                ...$data,
                                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(12)),
                            ]);
                            $user->assignRole('team_leader');
                            return $user->id;
                        }),
                ]),

            Forms\Components\Section::make('Recruiting')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('is_recruiting')
                        ->label('Open to applications')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('recruitment_message')
                        ->label("What we're looking for")
                        ->rows(3)
                        ->maxLength(1000)
                        ->visible(fn ($get) => (bool) $get('is_recruiting'))
                        ->columnSpanFull(),
                    Forms\Components\CheckboxList::make('needed_roles')
                        ->label('Roles needed')
                        ->options(\App\Models\User::ROLE_CATEGORIES)
                        ->columns(3)
                        ->visible(fn ($get) => (bool) $get('is_recruiting'))
                        ->helperText('Surfaced as chips on the public recruiting board.')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('looking_for_skills')
                        ->label('Specific skills (free text)')
                        ->maxLength(255)
                        ->visible(fn ($get) => (bool) $get('is_recruiting'))
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Status')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'withdrawn' => 'Withdrawn',
                            'disqualified' => 'Disqualified',
                        ])
                        ->default('active')
                        ->required(),
                    Forms\Components\Toggle::make('is_finalist')
                        ->label('Finalist')
                        ->helperText('Top 5 from Round 1.'),
                    Forms\Components\Toggle::make('all_first_timers')
                        ->label('All first-timers')
                        ->helperText('Eligible for Best Newcomer Award.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('theme.name')
                    ->label('Theme')
                    ->placeholder('—')
                    ->wrap(),
                Tables\Columns\TextColumn::make('leader.name')
                    ->label('Leader')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('members_count')
                    ->label('Members')
                    ->counts('members')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'withdrawn' => 'gray',
                        'disqualified' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_finalist')
                    ->label('Finalist')
                    ->boolean(),
                Tables\Columns\IconColumn::make('all_first_timers')
                    ->label('Newcomers')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('theme')
                    ->relationship('theme', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'withdrawn' => 'Withdrawn',
                        'disqualified' => 'Disqualified',
                    ]),
                Tables\Filters\TernaryFilter::make('is_finalist')->label('Finalist'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'view' => Pages\ViewTeam::route('/{record}'),
            'edit' => Pages\EditTeam::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $sectionLabels = [
            'cover' => 'Cover Page',
            'problem' => 'Problem Statement',
            'solution' => 'Proposed Solution',
            'architecture' => 'Technical Architecture',
            'impact' => 'Impact & Scalability',
            'roles' => 'Team Roles',
            'references' => 'References & Resources',
            'ai_disclosure' => 'AI Tools Disclosure',
        ];

        $draftEntries = collect($sectionLabels)->map(fn ($label, $key) =>
            Infolists\Components\TextEntry::make("workspace_{$key}")
                ->label($label)
                ->state(fn (Team $record) => optional($record->workspaceDrafts->firstWhere('section_key', $key))->body)
                ->placeholder('— not filled in —')
                ->prose()
                ->columnSpanFull()
        )->values()->all();

        return $infolist->schema([
            Infolists\Components\Section::make('Team')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('name')->weight('bold'),
                    Infolists\Components\TextEntry::make('theme.name')->label('Theme')->placeholder('—'),
                    Infolists\Components\TextEntry::make('leader.name')->label('Team Leader')->placeholder('—'),
                    Infolists\Components\TextEntry::make('status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'active' => 'success',
                            'withdrawn' => 'gray',
                            'disqualified' => 'danger',
                            default => 'gray',
                        }),
                    Infolists\Components\IconEntry::make('is_finalist')->label('Finalist')->boolean(),
                    Infolists\Components\IconEntry::make('all_first_timers')->label('All first-timers')->boolean(),
                ]),

            Infolists\Components\Section::make('Workspace content')
                ->description('Everything the team filled in — same content judges and mentors see before evaluating.')
                ->collapsible()
                ->schema($draftEntries),

            Infolists\Components\Section::make('Submission materials')
                ->description('Final submission links and assets.')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('submission.video_url')
                        ->label('Demo video')
                        ->placeholder('— not provided —')
                        ->url(fn (?string $state) => $state, true)
                        ->copyable(),
                    Infolists\Components\TextEntry::make('submission.slides_url')
                        ->label('Slides')
                        ->placeholder('— not provided —')
                        ->url(fn (?string $state) => $state, true)
                        ->copyable(),
                    Infolists\Components\TextEntry::make('submission.repo_url')
                        ->label('Code repository')
                        ->placeholder('— not provided —')
                        ->url(fn (?string $state) => $state, true)
                        ->copyable(),
                    Infolists\Components\TextEntry::make('submission_report')
                        ->label('Solution report (PDF)')
                        ->state(fn (Team $record) => $record->submission?->report_pdf_path
                            ? asset('storage/' . $record->submission->report_pdf_path)
                            : null)
                        ->placeholder('— not uploaded —')
                        ->url(fn (?string $state) => $state, true),
                    Infolists\Components\TextEntry::make('submission.ai_disclosure_text')
                        ->label('AI tools disclosure (final)')
                        ->placeholder('— not provided —')
                        ->prose()
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('submission.status')
                        ->label('Submission status')
                        ->badge()
                        ->placeholder('— no submission yet —'),
                    Infolists\Components\TextEntry::make('submission.submitted_at')
                        ->label('Submitted at')
                        ->dateTime()
                        ->placeholder('—'),
                ]),
        ]);
    }
}
