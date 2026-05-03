<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('primary_role', 32)->nullable()->after('headline');
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->string('role_category', 32)->nullable()->after('role_in_team');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->json('needed_roles')->nullable()->after('looking_for_skills');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('primary_role');
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn('role_category');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('needed_roles');
        });
    }
};
