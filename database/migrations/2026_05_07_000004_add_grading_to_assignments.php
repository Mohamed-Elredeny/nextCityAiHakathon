<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->decimal('max_score', 6, 2)->default(100)->after('accepted_extensions');
            $table->boolean('release_grades')->default(true)->after('max_score');
        });

        Schema::create('assignment_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('judge_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('score', 6, 2);
            $table->text('feedback')->nullable();
            $table->timestamp('graded_at');
            $table->timestamps();

            $table->unique(['assignment_submission_id', 'judge_id']);
            $table->index('judge_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_scores');
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['max_score', 'release_grades']);
        });
    }
};
