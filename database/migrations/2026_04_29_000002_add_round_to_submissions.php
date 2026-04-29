<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The unique index on team_id is also serving as the FK index, so MySQL
        // refuses to drop it directly. Add a plain index first, then drop the unique.
        Schema::table('submissions', function (Blueprint $table) {
            $table->index('team_id', 'submissions_team_id_index');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropUnique('submissions_team_id_unique');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->enum('round', ['round1', 'finals'])
                ->default('round1')
                ->after('team_id');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->unique(['team_id', 'round'], 'submissions_team_round_unique');
            $table->index(['round', 'status'], 'submissions_round_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex('submissions_round_status_idx');
            $table->dropUnique('submissions_team_round_unique');
            $table->dropColumn('round');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->unique('team_id', 'submissions_team_id_unique');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex('submissions_team_id_index');
        });
    }
};
