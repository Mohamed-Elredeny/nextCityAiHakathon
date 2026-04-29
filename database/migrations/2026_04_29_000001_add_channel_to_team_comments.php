<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_comments', function (Blueprint $table) {
            $table->enum('channel', ['team', 'mentor', 'judge'])
                ->default('team')
                ->after('user_id');
            $table->index(['team_id', 'channel', 'created_at'], 'team_comments_channel_idx');
        });
    }

    public function down(): void
    {
        Schema::table('team_comments', function (Blueprint $table) {
            $table->dropIndex('team_comments_channel_idx');
            $table->dropColumn('channel');
        });
    }
};
