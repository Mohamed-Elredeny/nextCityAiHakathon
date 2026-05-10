<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Locks in the official award winners (one row per slot per edition).
 *
 * The live PublicLeaderboard is computed from raw scores+votes and keeps
 * fluctuating; this table holds the announced final picks so the public
 * site can show a stable Winners hero even after the event ends.
 *
 * Slots: first_place, second_place, third_place,
 *        peoples_choice, best_ai_innovation, most_impactful_solution.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('award_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('slot', 32);
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();

            $table->unique(['edition_id', 'slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('award_winners');
    }
};
