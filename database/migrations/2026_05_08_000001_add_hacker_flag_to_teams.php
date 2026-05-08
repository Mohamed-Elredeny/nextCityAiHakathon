<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penalty mechanism for teams caught manipulating the People's Choice
 * voting (vote stuffing, ballot harvesting, etc.). When the flag is on:
 *   - All of their votes are zeroed for ranking purposes
 *   - Their judges' average is reduced by HACKER_JUDGE_PENALTY (Team model)
 *
 * The flag is a boolean — flip it on/off from the admin Teams resource.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('is_hacker')->default(false)->after('is_finalist');
            $table->text('hacker_reason')->nullable()->after('is_hacker');
            $table->timestamp('hacker_marked_at')->nullable()->after('hacker_reason');
            $table->foreignId('hacker_marked_by')->nullable()->after('hacker_marked_at')
                ->constrained('users')->nullOnDelete();
            $table->index('is_hacker');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['hacker_marked_by']);
            $table->dropIndex(['is_hacker']);
            $table->dropColumn(['is_hacker', 'hacker_reason', 'hacker_marked_at', 'hacker_marked_by']);
        });
    }
};
