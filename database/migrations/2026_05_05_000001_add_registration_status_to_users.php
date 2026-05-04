<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('registration_status', 20)->default('approved')->after('social_links');
            $table->string('requested_role', 32)->nullable()->after('registration_status');
            $table->timestamp('approved_at')->nullable()->after('requested_role');
            $table->index('registration_status');
        });

        DB::table('users')->update(['registration_status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['registration_status']);
            $table->dropColumn(['registration_status', 'requested_role', 'approved_at']);
        });
    }
};
