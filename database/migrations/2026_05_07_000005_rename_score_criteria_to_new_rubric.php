<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Switch the judging rubric from the old 6-criterion model to the
 * new 5-criterion model used at Day 2 pitching:
 *
 *   innovation     → business_viability       (20%)
 *   technical      → technical_feasibility    (20%)
 *   impact         → impact_relevance         (20%)
 *   ux             → team_skills              (20%)
 *   pitch          → presentation_skills      (20%)
 *   business       → DROPPED
 *
 * Existing scores keep their values under the new column names.
 * weighted_total is recomputed by the application after the rename
 * (the weights changed) — judges are expected to re-lock if results
 * are needed before that.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->renameColumn('innovation', 'business_viability');
        });
        Schema::table('scores', function (Blueprint $table) {
            $table->renameColumn('technical', 'technical_feasibility');
        });
        Schema::table('scores', function (Blueprint $table) {
            $table->renameColumn('impact', 'impact_relevance');
        });
        Schema::table('scores', function (Blueprint $table) {
            $table->renameColumn('ux', 'team_skills');
        });
        Schema::table('scores', function (Blueprint $table) {
            $table->renameColumn('pitch', 'presentation_skills');
        });
        Schema::table('scores', function (Blueprint $table) {
            $table->dropColumn('business');
        });
    }

    public function down(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->decimal('business', 4, 2)->default(0);
        });
        Schema::table('scores', function (Blueprint $table) {
            $table->renameColumn('presentation_skills', 'pitch');
        });
        Schema::table('scores', function (Blueprint $table) {
            $table->renameColumn('team_skills', 'ux');
        });
        Schema::table('scores', function (Blueprint $table) {
            $table->renameColumn('impact_relevance', 'impact');
        });
        Schema::table('scores', function (Blueprint $table) {
            $table->renameColumn('technical_feasibility', 'technical');
        });
        Schema::table('scores', function (Blueprint $table) {
            $table->renameColumn('business_viability', 'innovation');
        });
    }
};
