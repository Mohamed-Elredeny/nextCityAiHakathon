<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('institution');
            $table->string('avatar_path')->nullable()->after('bio');
            $table->string('headline')->nullable()->after('avatar_path');
            $table->json('social_links')->nullable()->after('headline');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bio', 'avatar_path', 'headline', 'social_links']);
        });
    }
};
