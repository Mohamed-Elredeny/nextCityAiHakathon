<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('is_recruiting')->default(false)->after('all_first_timers');
            $table->text('recruitment_message')->nullable()->after('is_recruiting');
            $table->string('looking_for_skills')->nullable()->after('recruitment_message');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['is_recruiting', 'recruitment_message', 'looking_for_skills']);
        });
    }
};
