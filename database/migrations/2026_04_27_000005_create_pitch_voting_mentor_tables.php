<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pitch_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->enum('round', ['round1', 'finals']);
            $table->unsignedSmallInteger('slot_index');
            $table->dateTime('scheduled_start')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();
            $table->unique(['team_id', 'round']);
            $table->unique(['round', 'slot_index']);
        });

        Schema::create('peoples_choice_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->dateTime('voted_at');
            $table->timestamps();
            $table->unique('user_id');
            $table->index('team_id');
        });

        Schema::create('mentor_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['mentor_id', 'team_id']);
        });

        Schema::create('mentor_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->index(['team_id', 'created_at']);
        });

        Schema::create('mentor_rotation_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->dateTime('slot_start');
            $table->dateTime('slot_end');
            $table->timestamps();
            $table->index(['mentor_id', 'slot_start']);
            $table->unique(['mentor_id', 'team_id', 'slot_start']);
        });

        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['subject_type', 'subject_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
        Schema::dropIfExists('mentor_rotation_slots');
        Schema::dropIfExists('mentor_notes');
        Schema::dropIfExists('mentor_assignments');
        Schema::dropIfExists('peoples_choice_votes');
        Schema::dropIfExists('pitch_schedule');
    }
};
