<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 191);
            $table->string('slug', 191)->unique();
            $table->text('description')->nullable();
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->unsignedInteger('max_files')->default(10);
            $table->unsignedBigInteger('max_file_size_kb')->default(20480); // 20MB per file
            $table->json('accepted_extensions')->nullable(); // null = any
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['edition_id', 'is_active']);
            $table->index('sort_order');
        });

        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('first_submitted_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->foreignId('last_activity_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['assignment_id', 'team_id']);
        });

        Schema::create('assignment_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('mime_type', 191)->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index('assignment_submission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_files');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
    }
};
