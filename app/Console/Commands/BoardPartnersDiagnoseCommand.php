<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BoardPartnersDiagnoseCommand extends Command
{
    protected $signature = 'hackathon:diagnose-accounts
                            {--reset : Generate fresh passwords for every board member + partner and print them}';

    protected $description = 'Diagnose login problems for board members and partners. Shows status, roles, and approval state. Use --reset to generate new passwords.';

    public function handle(): int
    {
        $users = User::query()
            ->whereIn('user_category', [User::CATEGORY_BOARD, User::CATEGORY_PARTNER])
            ->with('roles')
            ->orderBy('user_category')
            ->orderBy('name')
            ->get();

        if ($users->isEmpty()) {
            $this->warn('No board members or partners found in the database.');
            $this->line('Run: php artisan db:seed --class=BoardAndPartnersSeeder');
            return self::SUCCESS;
        }

        $reset = (bool) $this->option('reset');
        $rows = [];
        foreach ($users as $user) {
            $passwordCol = '— unchanged —';
            if ($reset) {
                $newPassword = Str::random(12);
                $user->forceFill([
                    'password' => Hash::make($newPassword),
                    'registration_status' => 'approved',
                    'approved_at' => $user->approved_at ?? now(),
                ])->save();
                // ensure roles
                $user->syncRoles(['judge', 'mentor']);
                $passwordCol = $newPassword;
            }

            $issues = [];
            if ($user->registration_status !== 'approved') {
                $issues[] = 'status=' . $user->registration_status;
            }
            $roles = $user->roles->pluck('name')->all();
            if (! in_array('judge', $roles, true) && ! in_array('mentor', $roles, true)) {
                $issues[] = 'no judge/mentor role';
            }
            if (! $user->password) {
                $issues[] = 'NO PASSWORD SET';
            }

            $rows[] = [
                User::USER_CATEGORIES[$user->user_category] ?? $user->user_category,
                $user->name,
                $user->email,
                $user->registration_status ?: '—',
                empty($roles) ? '—' : implode(', ', $roles),
                empty($issues) ? '✓ ok' : '✗ ' . implode(' / ', $issues),
                $reset ? $passwordCol : '',
            ];
        }

        $this->table(
            ['Category', 'Name', 'Email', 'Status', 'Roles', 'Diagnostic', $reset ? 'New password' : ''],
            $rows,
        );

        if (! $reset) {
            $this->newLine();
            $this->info('To force-reset passwords for everyone above (idempotent), run:');
            $this->line('  php artisan hackathon:diagnose-accounts --reset');
            $this->newLine();
            $this->warn('Capture the password column — it will not be shown again.');
        } else {
            $this->newLine();
            $this->warn('Passwords above were just regenerated. Copy them now — they are NOT stored anywhere.');
        }

        return self::SUCCESS;
    }
}
