<?php
/**
 * One-shot wipe of all non-admin data, run via:
 *   php artisan tinker database/data/wipe_demo.php
 *
 * Keeps only the admin@acie.local account; clears every team/submission/
 * score/comment/vote/post/notification/application and all judges, mentors,
 * voters, team_leaders, team_members.
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

DB::statement('SET FOREIGN_KEY_CHECKS=0');

$admin = User::where('email', 'admin@acie.local')->first();
if (! $admin) {
    echo "ERROR: admin@acie.local not found — refusing to wipe.\n";
    return;
}
$adminId = (int) $admin->id;
echo "Admin id: {$adminId}\n";

$truncateIfExists = [
    // Judging / scoring
    'audit_log',
    'peoples_choice_votes',
    'mentor_notes',
    'mentor_rotation_slots',
    'mentor_assignments',
    'special_award_nominations',
    'scores',
    'judge_assignments',
    'submission_validations',
    'submissions',
    // Team workspace & roster
    'team_workspace_drafts',
    'team_comments',
    'pitch_schedule',
    'team_members',
    'team_applications',
    'teams',
    // Community
    'community_post_attachments',
    'community_post_likes',
    'community_post_comments',
    'community_posts',
    // Notifications
    'notifications',
];

foreach ($truncateIfExists as $t) {
    if (Schema::hasTable($t)) {
        $n = DB::table($t)->count();
        DB::table($t)->truncate();
        echo "Truncated {$t} ({$n} rows)\n";
    }
}

// Drop role assignments for everyone but admin
$rolesDeleted = DB::table('model_has_roles')->where('model_id', '!=', $adminId)->delete();
echo "Removed role assignments: {$rolesDeleted}\n";

$permissionsDeleted = DB::table('model_has_permissions')->where('model_id', '!=', $adminId)->delete();
echo "Removed direct permission assignments: {$permissionsDeleted}\n";

// Delete every user except admin
$usersDeleted = DB::table('users')->where('id', '!=', $adminId)->delete();
echo "Deleted users: {$usersDeleted}\n";

DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "\n--- Final state ---\n";
echo 'Users: ' . DB::table('users')->count() . "\n";
echo 'Teams: ' . DB::table('teams')->count() . "\n";
echo 'Submissions: ' . DB::table('submissions')->count() . "\n";
echo "Wipe complete.\n";
