<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks vote attempts per IP. After ATTEMPT_LIMIT attempts in a sliding
 * one-hour window, the IP is blocked from voting for BLOCK_DURATION_HOURS.
 * Admins can review and clear blocks from the Filament resource.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voter_ip_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->timestamp('first_attempt_at')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('blocked_until')->nullable();
            $table->string('reason')->nullable();
            $table->text('last_user_agent')->nullable();
            $table->timestamps();

            $table->index('blocked_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voter_ip_blocks');
    }
};
