<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('tagline')->nullable()->after('name');
            $table->string('logo_path')->nullable()->after('tagline');
            $table->string('banner_path')->nullable()->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'logo_path', 'banner_path']);
        });
    }
};
