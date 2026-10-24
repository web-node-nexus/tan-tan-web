<?php

namespace App\Filament\Widgets;

use App\Filament\Support\Metrics;
use App\Models\Listing;
use Filament\Widgets\ChartWidget;

class CategoryShareChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = ['md' => 6, 'xl' => 4];

    protected ?string $heading = 'Live ads by category';

    protected ?string $description = 'Where the inventory is';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $rows = Listing::query()
            ->where('listings.status', 'active')
            ->join('categories', 'categories.id', '=', 'listings.category_id')
            ->selectRaw('categories.name_en as name, count(*) as c')
            ->groupBy('categories.name_en')
            ->orderByDesc('c')
            ->pluck('c', 'name');

        $top = $rows->take(6);
        $other = $rows->slice(6)->sum();
        if ($other > 0) {
            $top['Other'] = $other;
        }

        return [
            'datasets' => [[
                'data' => $top->values()->all(),
                'backgroundColor' => array_slice(Metrics::PALETTE, 0, $top->count()),
                'borderWidth' => 0,
                'hoverOffset' => 6,
            ]],
            'labels' => $top->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'cutout' => '68%',
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
