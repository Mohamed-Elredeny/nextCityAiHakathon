<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@acie.local'],
            [
                'name' => 'ACIE Admin',
                'password' => Hash::make('password'),
                'institution' => 'Alamein Center for Innovation and Entrepreneurship',
            ]
        );

        $admin->syncRoles(['super_admin']);
    }
}
