<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Round 1 runs in 3 parallel rooms (A, B, C). The original schema only
 * allowed one team per (round, slot_index) globally, which would force
 * the 3 rooms to share slots. Add a `room` column and re-key uniqueness
 * by (round, room, slot_index) so each room gets its own ordered list.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pitch_schedule', function (Blueprint $table) {
            $table->string('room', 8)->nullable()->after('round');
            // Drop the (round, slot_index) unique that prevented per-room slot reuse
            $table->dropUnique(['round', 'slot_index']);
        });

        Schema::table('pitch_schedule', function (Blueprint $table) {
            // New uniqueness: per round AND room AND slot
            $table->unique(['round', 'room', 'slot_index'], 'pitch_schedule_round_room_slot_unique');
            $table->index('room');
        });
    }

    public function down(): void
    {
        Schema::table('pitch_schedule', function (Blueprint $table) {
            $table->dropUnique('pitch_schedule_round_room_slot_unique');
            $table->dropIndex(['room']);
            $table->dropColumn('room');
        });
        Schema::table('pitch_schedule', function (Blueprint $table) {
            $table->unique(['round', 'slot_index']);
        });
    }
};
