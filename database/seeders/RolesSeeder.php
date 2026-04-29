<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'super_admin',
            'judge',
            'mentor',
            'team_leader',
            'team_member',
            'voter',
        ];

        foreach ($roles as $name) {
            Role::findOrCreate($name, 'web');
        }
    }
}
