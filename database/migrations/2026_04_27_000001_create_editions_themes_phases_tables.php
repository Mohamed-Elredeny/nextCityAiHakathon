<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editions', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year')->unique();
            $table->string('name');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['edition_id', 'key']);
        });

        Schema::create('phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('label');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->enum('state', ['pending', 'active', 'closed'])->default('pending');
            $table->boolean('auto_transition')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['edition_id', 'key']);
            $table->index(['edition_id', 'state']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phases');
        Schema::dropIfExists('themes');
        Schema::dropIfExists('editions');
    }
};
