<?php

namespace App\Filament\Widgets;

use App\Filament\Support\ListingModeration;
use App\Models\Listing;
use Filament\Widgets\ChartWidget;

class ListingStatusChartWidget extends ChartWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = ['md' => 6, 'xl' => 4];

    protected ?string $heading = 'Listing lifecycle';

    protected ?string $description = 'All ads by current status';

    protected ?string $maxHeight = '280px';

    private const COLORS = [
        'active' => '#0F9D8A',
        'pending' => '#F5A524',
        'sold' => '#6366F1',
        'expired' => '#A1A1AA',
        'rejected' => '#E5484D',
        'blocked' => '#991B1B',
        'draft' => '#D4D4D8',
    ];

    protected function getData(): array
    {
        $counts = Listing::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');
        $statuses = array_values(array_filter(array_keys(self::COLORS), fn (string $s): bool => ($counts[$s] ?? 0) > 0));

        return [
            'datasets' => [[
                'data' => array_map(fn (string $s): int => (int) $counts[$s], $statuses),
                'backgroundColor' => array_map(fn (string $s): string => self::COLORS[$s], $statuses),
                'borderWidth' => 0,
                'hoverOffset' => 6,
            ]],
            'labels' => array_map(fn (string $s): string => ListingModeration::STATUS_LABELS[$s], $statuses),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'bottom', 'labels' => ['usePointStyle' => true, 'boxWidth' => 8, 'padding' => 12]],
            ],
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
        ];
    }
}
