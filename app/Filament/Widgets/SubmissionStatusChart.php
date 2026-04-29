<?php

namespace App\Filament\Widgets;

use App\Models\Edition;
use App\Models\Submission;
use App\Models\Team;
use Filament\Widgets\ChartWidget;

class SubmissionStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Round 1 submission status';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $edition = Edition::active();
        $teamIds = $edition
            ? Team::where('edition_id', $edition->id)->pluck('id')
            : Team::query()->pluck('id');

        $statuses = [
            Submission::STATUS_DRAFT => 'Draft',
            Submission::STATUS_SUBMITTED => 'Submitted',
            Submission::STATUS_VALIDATED => 'Validated',
            Submission::STATUS_FLAGGED => 'Flagged',
            Submission::STATUS_ACCEPTED => 'Accepted',
            Submission::STATUS_REJECTED => 'Rejected',
        ];

        $counts = [];
        foreach ($statuses as $key => $label) {
            $counts[$label] = Submission::whereIn('team_id', $teamIds)
                ->where('round', Submission::ROUND_ONE)
                ->where('status', $key)
                ->count();
        }

        $missing = max(0, $teamIds->count() - Submission::whereIn('team_id', $teamIds)
            ->where('round', Submission::ROUND_ONE)
            ->count());
        if ($missing > 0) {
            $counts['Not started'] = $missing;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Teams',
                    'data' => array_values($counts),
                    'backgroundColor' => [
                        '#9ca3af', // draft
                        '#0F4778', // submitted
                        '#10b981', // validated
                        '#f59e0b', // flagged
                        '#059669', // accepted
                        '#C8102E', // rejected
                        '#cbd5e1', // not started
                    ],
                ],
            ],
            'labels' => array_keys($counts),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
