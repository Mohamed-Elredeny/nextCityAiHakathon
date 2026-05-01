<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->json('mentioned_user_ids')->nullable()->after('comments_count');
            $table->json('mentioned_team_ids')->nullable()->after('mentioned_user_ids');
        });

        Schema::table('community_comments', function (Blueprint $table) {
            $table->json('mentioned_user_ids')->nullable()->after('body');
            $table->json('mentioned_team_ids')->nullable()->after('mentioned_user_ids');
        });
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->dropColumn(['mentioned_user_ids', 'mentioned_team_ids']);
        });

        Schema::table('community_comments', function (Blueprint $table) {
            $table->dropColumn(['mentioned_user_ids', 'mentioned_team_ids']);
        });
    }
};
