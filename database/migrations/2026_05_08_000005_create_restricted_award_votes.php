<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Restricted-award voting (Best AI Innovation, Most Impactful Solution).
 *
 * Unlike People's Choice (open public vote), these awards are voted by
 * registered users only — and only those who are part of a team, or are
 * judges / mentors / admins. Random students with no team affiliation
 * are excluded.
 *
 * One vote per user per award per edition. Users can change their vote
 * while the voting window is open.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restricted_award_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->string('award_key', 64);
            $table->string('voter_role', 32)->nullable(); // audit: team_member|judge|mentor|super_admin
            $table->timestamps();

            $table->unique(['user_id', 'award_key', 'edition_id'], 'raw_user_award_edition_uniq');
            $table->index(['edition_id', 'award_key'], 'raw_edition_award_idx');
        });

        // Insert the new phase row for the active edition so admins can
        // open/close the restricted voting window from Filament.
        $editionId = DB::table('editions')->where('is_active', 1)->value('id');
        if ($editionId) {
            $exists = DB::table('phases')
                ->where('edition_id', $editionId)
                ->where('key', 'restricted_award_voting')
                ->exists();
            if (!$exists) {
                // Slot it between finalist_pitching (sort 12, ends 14:30) and
                // awards (sort 13, starts 15:00). Push awards to 14 to keep
                // ordering monotonic.
                DB::table('phases')
                    ->where('edition_id', $editionId)
                    ->where('key', 'awards')
                    ->update(['sort_order' => 14]);

                DB::table('phases')->insert([
                    'edition_id'      => $editionId,
                    'key'             => 'restricted_award_voting',
                    'label'           => 'Restricted Award Voting',
                    'state'           => 'pending',
                    'starts_at'       => '2026-05-08 14:30:00',
                    'ends_at'         => '2026-05-08 14:55:00',
                    'auto_transition' => 0,
                    'sort_order'      => 13,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('restricted_award_votes');

        DB::table('phases')->where('key', 'restricted_award_voting')->delete();
    }
};
