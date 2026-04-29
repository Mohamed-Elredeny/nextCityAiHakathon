<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('judge_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('judge_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->enum('round', ['round1', 'finals']);
            $table->boolean('recused')->default(false);
            $table->text('recused_reason')->nullable();
            $table->timestamps();
            $table->unique(['judge_id', 'team_id', 'round']);
            $table->index(['team_id', 'round']);
        });

        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('judge_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->enum('round', ['round1', 'finals']);
            $table->decimal('innovation', 4, 2)->nullable();
            $table->decimal('technical', 4, 2)->nullable();
            $table->decimal('impact', 4, 2)->nullable();
            $table->decimal('ux', 4, 2)->nullable();
            $table->decimal('pitch', 4, 2)->nullable();
            $table->decimal('business', 4, 2)->nullable();
            $table->decimal('weighted_total', 5, 2)->nullable();
            $table->text('comment')->nullable();
            $table->dateTime('locked_at')->nullable();
            $table->timestamps();
            $table->unique(['judge_id', 'team_id', 'round']);
            $table->index(['team_id', 'round', 'locked_at']);
        });

        Schema::create('special_award_nominations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('judge_id')->constrained('users')->cascadeOnDelete();
            $table->string('award_key');
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->enum('round', ['round1', 'finals'])->default('finals');
            $table->text('justification')->nullable();
            $table->timestamps();
            $table->unique(['judge_id', 'award_key', 'round']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_award_nominations');
        Schema::dropIfExists('scores');
        Schema::dropIfExists('judge_assignments');
    }
};
