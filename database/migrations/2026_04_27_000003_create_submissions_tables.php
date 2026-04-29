<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('report_pdf_path')->nullable();
            $table->string('slides_url')->nullable();
            $table->string('repo_url')->nullable();
            $table->string('video_url')->nullable();
            $table->text('ai_disclosure_text')->nullable();
            $table->enum('status', ['draft', 'submitted', 'validated', 'flagged', 'accepted', 'rejected'])->default('draft');
            $table->dateTime('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reject_reason')->nullable();
            $table->timestamps();
            $table->unique('team_id');
        });

        Schema::create('submission_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->string('check_key');
            $table->enum('status', ['pending', 'pass', 'fail'])->default('pending');
            $table->text('message')->nullable();
            $table->dateTime('checked_at')->nullable();
            $table->timestamps();
            $table->index(['submission_id', 'check_key']);
        });

        Schema::create('team_workspace_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('section_key');
            $table->longText('body')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['team_id', 'section_key']);
        });

        Schema::create('team_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->index(['team_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_comments');
        Schema::dropIfExists('team_workspace_drafts');
        Schema::dropIfExists('submission_validations');
        Schema::dropIfExists('submissions');
    }
};
