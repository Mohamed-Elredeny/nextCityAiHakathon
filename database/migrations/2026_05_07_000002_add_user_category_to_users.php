<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // board | partner | null (regular participant)
            $table->string('user_category', 24)->nullable()->after('primary_role');
            $table->string('organization', 191)->nullable()->after('institution');
            $table->string('org_logo_path')->nullable()->after('avatar_path');
            $table->string('org_url', 255)->nullable()->after('org_logo_path');
            $table->index('user_category');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['user_category']);
            $table->dropColumn(['user_category', 'organization', 'org_logo_path', 'org_url']);
        });
    }
};
