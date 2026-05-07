<?php

namespace App\Filament\Resources\PartnerResource\Pages;

use App\Filament\Resources\PartnerResource;
use App\Models\Edition;
use App\Models\JudgeAssignment;
use App\Models\MentorAssignment;
use App\Models\Team;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePartner extends CreateRecord
{
    protected static string $resource = PartnerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_category'] = User::CATEGORY_PARTNER;
        $data['registration_status'] = 'approved';
        $data['approved_at'] = now();
        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var User $user */
        $user = $this->record;

        // Roles
        $user->syncRoles(['judge', 'mentor']);

        // Assign to every active team in the active edition
        $edition = Edition::active();
        if ($edition) {
            $teams = Team::where('edition_id', $edition->id)->where('status', 'active')->get();
            foreach ($teams as $team) {
                JudgeAssignment::firstOrCreate(
                    ['judge_id' => $user->id, 'team_id' => $team->id, 'round' => JudgeAssignment::ROUND_ONE],
                    ['recused' => false],
                );
                JudgeAssignment::firstOrCreate(
                    ['judge_id' => $user->id, 'team_id' => $team->id, 'round' => JudgeAssignment::ROUND_FINALS],
                    ['recused' => false],
                );
                MentorAssignment::firstOrCreate(
                    ['mentor_id' => $user->id, 'team_id' => $team->id],
                );
            }
        }

        Notification::make()
            ->title('Partner created')
            ->body($user->name . ' is now assigned as judge + mentor to every active team.')
            ->success()
            ->send();
    }
}
