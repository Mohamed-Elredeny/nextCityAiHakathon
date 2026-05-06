<?php

namespace App\Filament\Resources\TeamResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    protected static ?string $title = 'Members';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('role_category')
                ->label('Role category')
                ->options(User::ROLE_CATEGORIES)
                ->placeholder('Pick one')
                ->helperText('Used to track which core roles a team has filled.')
                ->native(false),
            Forms\Components\TextInput::make('role_in_team')
                ->label('Role title (free text)')
                ->placeholder('e.g. ML lead, frontend, designer')
                ->maxLength(120),
            Forms\Components\Toggle::make('is_leader')
                ->label('Mark as team leader')
                ->helperText('There can only be one leader; selecting this will move leadership to this member.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->weight('semibold'),
                Tables\Columns\TextColumn::make('email')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('pivot.role_category')
                    ->label('Category')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? (User::ROLE_CATEGORIES[$state] ?? $state) : null)
                    ->color(fn (?string $state) => match ($state) {
                        'designer' => 'info',
                        'developer' => 'success',
                        'business' => 'warning',
                        default => 'gray',
                    })
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('pivot.role_in_team')
                    ->label('Role')
                    ->placeholder('—'),
                Tables\Columns\IconColumn::make('pivot.is_leader')
                    ->label('Leader')
                    ->boolean(),
                Tables\Columns\TextColumn::make('institution')->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Attach existing user')
                    ->modalHeading('Attach a user to this team')
                    ->recordSelectSearchColumns(['name', 'email', 'phone'])
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->helperText('Search by name, email, or phone. Users already on another team are shown — selecting one will move them here.')
                            ->getSearchResultsUsing(function (string $search) {
                                $ownerId = $this->getOwnerRecord()->getKey();
                                return User::query()
                                    ->with(['teams' => fn ($q) => $q->select('teams.id', 'teams.name')])
                                    ->where(fn ($q) => $q
                                        ->where('name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%")
                                        ->orWhere('phone', 'like', "%{$search}%"))
                                    // Hide users already on THIS team — they're listed in the table.
                                    ->whereDoesntHave('teams', fn ($q) => $q->where('teams.id', $ownerId))
                                    ->limit(30)
                                    ->get()
                                    ->mapWithKeys(function (User $u) {
                                        $current = $u->teams->first();
                                        $suffix = $current
                                            ? " · already on \"{$current->name}\" — will be moved"
                                            : '';
                                        $label = "{$u->name} · {$u->email}"
                                            . ($u->phone ? " · {$u->phone}" : '')
                                            . $suffix;
                                        return [$u->id => $label];
                                    });
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $u = User::with('teams')->find($value);
                                if (! $u) return null;
                                $current = $u->teams->first();
                                return "{$u->name} · {$u->email}"
                                    . ($u->phone ? " · {$u->phone}" : '')
                                    . ($current ? " · on \"{$current->name}\"" : '');
                            }),
                        Forms\Components\Select::make('role_category')
                            ->label('Role category')
                            ->options(User::ROLE_CATEGORIES)
                            ->native(false),
                        Forms\Components\TextInput::make('role_in_team')->label('Role title')->maxLength(120),
                        Forms\Components\Toggle::make('is_leader')->label('Set as leader'),
                    ])
                    ->before(function (array $data, $livewire) {
                        // If the picked user is currently on another team, detach them
                        // first so the team_members unique constraint / business rule
                        // (one team per user) holds. Clear the old team's leader_id
                        // if they were leading it.
                        $userId = $data['recordId'] ?? null;
                        if (! $userId) return;

                        $newTeam = $livewire->getOwnerRecord();
                        $oldTeams = \App\Models\Team::whereHas('members', fn ($q) => $q->where('users.id', $userId))
                            ->where('id', '!=', $newTeam->id)
                            ->get();

                        foreach ($oldTeams as $oldTeam) {
                            $oldTeam->members()->detach($userId);
                            if ((int) $oldTeam->leader_id === (int) $userId) {
                                $oldTeam->update(['leader_id' => null]);
                            }
                            \App\Models\AuditLog::record('team.member_moved_by_admin', $oldTeam, [
                                'user_id' => $userId,
                                'from_team_id' => $oldTeam->id,
                                'to_team_id' => $newTeam->id,
                                'admin_id' => \Illuminate\Support\Facades\Auth::id(),
                            ]);
                        }
                    })
                    ->after(function (array $data, $record, $livewire) {
                        if (!empty($data['is_leader'])) {
                            $livewire->getOwnerRecord()->update(['leader_id' => $record->id]);
                            $livewire->getOwnerRecord()->members()->updateExistingPivot(
                                $livewire->getOwnerRecord()->members->pluck('id')->all(),
                                ['is_leader' => false],
                            );
                            $livewire->getOwnerRecord()->members()->updateExistingPivot($record->id, ['is_leader' => true]);
                        }
                    }),
                Tables\Actions\Action::make('createUser')
                    ->label('Add new member')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('email')->email()->required()->unique('users', 'email'),
                        Forms\Components\TextInput::make('phone')->tel(),
                        Forms\Components\TextInput::make('institution'),
                        Forms\Components\Select::make('role_category')
                            ->label('Role category')
                            ->options(User::ROLE_CATEGORIES)
                            ->native(false),
                        Forms\Components\TextInput::make('role_in_team')->label('Role title'),
                        Forms\Components\Toggle::make('is_leader')->label('Set as leader'),
                    ])
                    ->action(function (array $data, $livewire) {
                        $user = User::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'phone' => $data['phone'] ?? null,
                            'institution' => $data['institution'] ?? null,
                            'password' => Hash::make(Str::random(12)),
                        ]);
                        $user->assignRole('team_member');
                        $livewire->getOwnerRecord()->members()->attach($user->id, [
                            'role_in_team' => $data['role_in_team'] ?? null,
                            'role_category' => $data['role_category'] ?? null,
                            'is_leader' => (bool) ($data['is_leader'] ?? false),
                        ]);
                        if (!empty($data['is_leader'])) {
                            $livewire->getOwnerRecord()->update(['leader_id' => $user->id]);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
