<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubmissionResource\Pages;
use App\Models\Submission;
use App\Services\SubmissionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubmissionResource extends Resource
{
    protected static ?string $model = Submission::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static ?string $navigationGroup = 'Hackathon';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Submission')->columns(2)->schema([
                Forms\Components\Select::make('team_id')
                    ->relationship('team', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'validated' => 'Validated',
                        'flagged' => 'Flagged',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),
                Forms\Components\FileUpload::make('report_pdf_path')
                    ->label('Solution Report')
                    ->disk('public')
                    ->directory('submissions/reports')
                    ->acceptedFileTypes(['application/pdf'])
                    ->openable()
                    ->downloadable()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('slides_url')->url()->columnSpanFull(),
                Forms\Components\TextInput::make('repo_url')->url()->columnSpanFull(),
                Forms\Components\TextInput::make('video_url')->url()->columnSpanFull(),
                Forms\Components\Textarea::make('ai_disclosure_text')->columnSpanFull()->rows(3),
                Forms\Components\Textarea::make('reject_reason')->columnSpanFull()->rows(2),
                Forms\Components\DateTimePicker::make('submitted_at')->disabled(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('team.name')->label('Team')->searchable()->sortable()->weight('semibold'),
                Tables\Columns\TextColumn::make('round')
                    ->badge()
                    ->color(fn (string $state) => $state === 'finals' ? 'warning' : 'info')
                    ->formatStateUsing(fn (string $state) => $state === 'finals' ? 'Finals' : 'Round 1'),
                Tables\Columns\TextColumn::make('team.theme.name')->label('Theme')->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'info',
                        'validated' => 'primary',
                        'accepted' => 'success',
                        'flagged' => 'warning',
                        'rejected' => 'danger',
                        'draft' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('submitted_at')->dateTime('Y-m-d H:i')->sortable()->placeholder('—'),
                Tables\Columns\IconColumn::make('report_pdf_path')->label('PDF')->boolean()
                    ->getStateUsing(fn ($record) => filled($record->report_pdf_path)),
                Tables\Columns\IconColumn::make('slides_url')->label('Slides')->boolean()
                    ->getStateUsing(fn ($record) => filled($record->slides_url)),
                Tables\Columns\IconColumn::make('repo_url')->label('Repo')->boolean()
                    ->getStateUsing(fn ($record) => filled($record->repo_url)),
                Tables\Columns\IconColumn::make('video_url')->label('Video')->boolean()
                    ->getStateUsing(fn ($record) => filled($record->video_url)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('round')->options([
                    'round1' => 'Round 1', 'finals' => 'Finals',
                ]),
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'submitted' => 'Submitted', 'validated' => 'Validated',
                    'flagged' => 'Flagged', 'accepted' => 'Accepted', 'rejected' => 'Rejected',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('accept')
                    ->color('success')->icon('heroicon-o-check-badge')
                    ->visible(fn ($record) => in_array($record->status, ['submitted', 'flagged', 'validated']))
                    ->requiresConfirmation()
                    ->action(fn ($record) => app(SubmissionService::class)->accept($record, auth()->id())),
                Tables\Actions\Action::make('flag')
                    ->color('warning')->icon('heroicon-o-flag')
                    ->visible(fn ($record) => $record->status === 'submitted')
                    ->form([Forms\Components\Textarea::make('reason')->required()])
                    ->action(fn ($record, array $data) => app(SubmissionService::class)->flag($record, $data['reason'], auth()->id())),
                Tables\Actions\Action::make('reject')
                    ->color('danger')->icon('heroicon-o-x-mark')
                    ->visible(fn ($record) => $record->status !== 'rejected')
                    ->form([Forms\Components\Textarea::make('reason')->required()])
                    ->requiresConfirmation()
                    ->action(fn ($record, array $data) => app(SubmissionService::class)->reject($record, $data['reason'], auth()->id())),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('submitted_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubmissions::route('/'),
            'edit' => Pages\EditSubmission::route('/{record}/edit'),
        ];
    }
}
