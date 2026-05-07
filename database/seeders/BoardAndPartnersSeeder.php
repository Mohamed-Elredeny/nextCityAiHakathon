<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BoardAndPartnersSeeder extends Seeder
{
    /**
     * Seeds 6 board members + 2 industry partners with `judge` and `mentor`
     * roles. Emails are placeholders (admin can update from Filament).
     * Passwords are randomized and printed to the console once — re-running
     * the seeder will NOT regenerate passwords (it only updates profile data).
     */
    public function run(): void
    {
        $board = [
            ['name' => 'Dr. Hesham Gaber',          'email' => 'hesham.gaber@aiu.edu.eg',         'institution' => 'AIU'],
            ['name' => 'Dr. Maged Ghonima',         'email' => 'maged.ghonima@aiu.edu.eg',        'institution' => 'AIU'],
            ['name' => 'Dr. Fadel Daigham',         'email' => 'fadel.daigham@aiu.edu.eg',        'institution' => 'AIU'],
            ['name' => 'Dr. Mohamed Abdel Kareem',  'email' => 'mohamed.abdelkareem@aiu.edu.eg',  'institution' => 'AIU'],
            ['name' => 'Dr. Ahmed Elyazbi',         'email' => 'ahmed.elyazbi@aiu.edu.eg',        'institution' => 'AIU'],
            ['name' => 'Dr. Malak Sameh',           'email' => 'malak.sameh@aiu.edu.eg',          'institution' => 'AIU'],
        ];

        $partners = [
            [
                'name' => 'Eng. Mohamed Ramadan',
                'email' => 'mohamed.ramadan@hassanallam.com',
                'organization' => 'Hassan Allam Group',
                'org_url' => 'https://hassanallam.com',
                'headline' => 'Industry Partner — Hassan Allam Group',
            ],
            [
                'name' => 'Eng. Abdelrahman Ahmed',
                'email' => 'abdelrahman.ahmed@onyxsystems.com',
                'organization' => 'Onyx Systems',
                'org_url' => null,
                'headline' => 'Industry Partner — Onyx Systems',
            ],
        ];

        $credentials = [];

        foreach ($board as $person) {
            $credentials[] = $this->upsert($person + [
                'user_category' => User::CATEGORY_BOARD,
                'headline' => 'Board Member',
            ]);
        }

        foreach ($partners as $person) {
            $credentials[] = $this->upsert($person + [
                'user_category' => User::CATEGORY_PARTNER,
                'institution' => $person['organization'] ?? null,
            ]);
        }

        $this->command?->newLine();
        $this->command?->warn('Board & Partner accounts (share these credentials with the people only):');
        $this->command?->table(['Name', 'Email', 'Password', 'Category'], $credentials);
        $this->command?->newLine();
    }

    private function upsert(array $data): array
    {
        $isNew = ! User::where('email', $data['email'])->exists();
        $plainPassword = $isNew ? Str::random(12) : '— unchanged —';

        $payload = collect($data)
            ->only(['name', 'institution', 'organization', 'headline', 'org_url', 'user_category'])
            ->filter(fn ($v) => $v !== null)
            ->all();

        $payload['registration_status'] = 'approved';
        $payload['approved_at'] = now();

        if ($isNew) {
            $payload['password'] = Hash::make($plainPassword);
        }

        $user = User::updateOrCreate(['email' => $data['email']], $payload);

        // Roles: judge + mentor cover the requested privileges (view +
        // communicate are open to any logged-in user via /community).
        $user->syncRoles(['judge', 'mentor']);

        return [
            $user->name,
            $user->email,
            $plainPassword,
            User::USER_CATEGORIES[$user->user_category] ?? '—',
        ];
    }
}
