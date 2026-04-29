<?php

namespace App\Filament\Widgets;

use App\Models\Edition;
use App\Models\Phase;
use Filament\Widgets\Widget;

class EventStatusWidget extends Widget
{
    protected static string $view = 'filament.widgets.event-status';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -2;

    protected static bool $isLazy = false;

    public function getViewData(): array
    {
        $edition = Edition::active();

        $activePhase = $edition
            ? Phase::where('edition_id', $edition->id)
                ->where('state', Phase::STATE_ACTIVE)
                ->orderBy('sort_order')
                ->first()
            : null;

        $nextPhase = $edition
            ? Phase::where('edition_id', $edition->id)
                ->where('state', Phase::STATE_PENDING)
                ->orderBy('sort_order')
                ->first()
            : null;

        return [
            'edition' => $edition,
            'activePhase' => $activePhase,
            'nextPhase' => $nextPhase,
            'activeEnds' => $activePhase?->ends_at,
            'nextStarts' => $nextPhase?->starts_at,
        ];
    }
}
