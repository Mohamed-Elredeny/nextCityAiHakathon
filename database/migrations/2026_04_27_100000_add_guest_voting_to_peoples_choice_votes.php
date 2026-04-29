<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop FK first so we can change the column and indexes
        Schema::table('peoples_choice_votes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Drop the existing unique on user_id (forced by initial migration)
        DB::statement('ALTER TABLE peoples_choice_votes DROP INDEX peoples_choice_votes_user_id_unique');

        // Make user_id nullable, add guest fields
        Schema::table('peoples_choice_votes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('voter_name')->nullable()->after('user_id');
            $table->string('voter_email')->nullable()->after('voter_name');
            $table->string('voter_token', 64)->nullable()->after('voter_email');
            $table->string('ip_address', 45)->nullable()->after('voter_token');
        });

        // Re-add FK and add new unique constraints
        Schema::table('peoples_choice_votes', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique('user_id');         // logged-in users — at most one vote each (NULLs allowed by MySQL)
            $table->unique('voter_email');     // guest emails — same allowance
            $table->unique('voter_token');     // browser cookie token
        });
    }

    public function down(): void
    {
        Schema::table('peoples_choice_votes', function (Blueprint $table) {
            $table->dropUnique(['voter_token']);
            $table->dropUnique(['voter_email']);
            $table->dropColumn(['voter_name', 'voter_email', 'voter_token', 'ip_address']);
        });
    }
};
