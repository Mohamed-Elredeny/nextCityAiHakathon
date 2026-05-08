<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two kill-switches for People's Choice voting:
 * - voting_paused: stops all new votes immediately, shows maintenance message.
 * - vote_requires_login: forces guests to sign in before voting (defeats
 *   IP-rotation attacks because every vote needs a verified user account).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->boolean('voting_paused')->default(false)->after('is_active');
            $table->boolean('vote_requires_login')->default(false)->after('voting_paused');
        });
    }

    public function down(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->dropColumn(['voting_paused', 'vote_requires_login']);
        });
    }
};
