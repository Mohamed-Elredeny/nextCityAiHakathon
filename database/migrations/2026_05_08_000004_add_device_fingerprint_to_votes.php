<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Browser-side device fingerprint (canvas + screen + timezone + hardware →
 * SHA-256). Stable across IP changes and cookie clearing — the strongest
 * cheap deterrent to vote manipulation that doesn't rely on the network.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peoples_choice_votes', function (Blueprint $table) {
            $table->string('device_fingerprint', 64)->nullable()->after('voter_token');
            $table->index('device_fingerprint');
        });
    }

    public function down(): void
    {
        Schema::table('peoples_choice_votes', function (Blueprint $table) {
            $table->dropIndex(['device_fingerprint']);
            $table->dropColumn('device_fingerprint');
        });
    }
};
