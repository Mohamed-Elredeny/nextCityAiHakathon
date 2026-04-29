<?php

namespace App\Services;

use App\Events\PhaseChanged;
use App\Models\AuditLog;
use App\Models\Edition;
use App\Models\Phase;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PhaseEngine
{
    public function tick(?CarbonInterface $now = null): array
    {
        $now ??= Carbon::now();
        $edition = Edition::active();
        if (!$edition) {
            return ['changed' => 0, 'reason' => 'no_active_edition'];
        }

        $changed = [];

        DB::transaction(function () use ($edition, $now, &$changed) {
            $phases = Phase::where('edition_id', $edition->id)
                ->orderBy('sort_order')
                ->lockForUpdate()
                ->get();

            foreach ($phases as $phase) {
                if (!$phase->auto_transition) {
                    continue;
                }
                if ($phase->state === Phase::STATE_ACTIVE && $now->greaterThanOrEqualTo($phase->ends_at)) {
                    $phase->update(['state' => Phase::STATE_CLOSED]);
                    $changed[] = [$phase->key, Phase::STATE_ACTIVE, Phase::STATE_CLOSED];
                }
                if ($phase->state === Phase::STATE_PENDING
                    && $now->greaterThanOrEqualTo($phase->starts_at)
                    && $now->lessThan($phase->ends_at)) {
                    Phase::where('edition_id', $edition->id)
                        ->where('state', Phase::STATE_ACTIVE)
                        ->update(['state' => Phase::STATE_CLOSED]);
                    $phase->update(['state' => Phase::STATE_ACTIVE]);
                    $changed[] = [$phase->key, Phase::STATE_PENDING, Phase::STATE_ACTIVE];
                }
            }
        });

        foreach ($changed as [$key, $from, $to]) {
            AuditLog::create([
                'user_id' => null,
                'action' => 'phase.transition',
                'subject_type' => Phase::class,
                'subject_id' => null,
                'payload' => ['key' => $key, 'from' => $from, 'to' => $to, 'at' => $now->toIso8601String()],
                'created_at' => $now,
            ]);
            event(new PhaseChanged($edition->id, $key, $from, $to));
        }

        return ['changed' => count($changed), 'transitions' => $changed];
    }

    public function activate(Phase $phase): void
    {
        DB::transaction(function () use ($phase) {
            Phase::where('edition_id', $phase->edition_id)
                ->where('state', Phase::STATE_ACTIVE)
                ->where('id', '!=', $phase->id)
                ->update(['state' => Phase::STATE_CLOSED]);
            $phase->update(['state' => Phase::STATE_ACTIVE]);
        });
        event(new PhaseChanged($phase->edition_id, $phase->key, 'manual', Phase::STATE_ACTIVE));
        AuditLog::record('phase.manual_activate', $phase, ['key' => $phase->key]);
    }

    public function close(Phase $phase): void
    {
        $phase->update(['state' => Phase::STATE_CLOSED]);
        event(new PhaseChanged($phase->edition_id, $phase->key, 'manual', Phase::STATE_CLOSED));
        AuditLog::record('phase.manual_close', $phase, ['key' => $phase->key]);
    }

    public function reset(Phase $phase): void
    {
        $phase->update(['state' => Phase::STATE_PENDING]);
        event(new PhaseChanged($phase->edition_id, $phase->key, 'manual', Phase::STATE_PENDING));
        AuditLog::record('phase.manual_reset', $phase, ['key' => $phase->key]);
    }

    public function active(int $editionId): ?Phase
    {
        return Phase::where('edition_id', $editionId)
            ->where('state', Phase::STATE_ACTIVE)
            ->orderBy('sort_order')
            ->first();
    }
}
