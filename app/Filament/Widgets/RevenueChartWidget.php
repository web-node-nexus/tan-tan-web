<?php

namespace App\Filament\Widgets;

use App\Filament\Support\Metrics;
use App\Models\ListingPayment;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class RevenueChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = ['md' => 6, 'xl' => 8];

    protected ?string $heading = 'Revenue';

    protected ?string $maxHeight = '280px';

    public function getDescription(): ?string
    {
        $days = Metrics::days($this->pageFilters);
        $total = Metrics::compare(ListingPayment::query()->whereIn('status', ['paid', 'consumed']), $days, 'created_at', 'amount')['current'];

        return Metrics::rupees($total).' collected · extra ad slots vs boosts';
    }

    protected function getData(): array
    {
        $days = Metrics::days($this->pageFilters);
        $paid = ListingPayment::query()->whereIn('status', ['paid', 'consumed']);

        $slots = Metrics::series((clone $paid)->where('purpose', 'extra_ad'), $days, 'created_at', 'amount');
        $boosts = Metrics::series((clone $paid)->where('purpose', 'boost'), $days, 'created_at', 'amount');

        return [
            'datasets' => [
                [
                    'label' => 'Extra ad slots',
                    'data' => $slots['values'],
                    'backgroundColor' => '#FF5A1F',
                    'borderRadius' => 6,
                    'maxBarThickness' => 22,
                ],
                [
                    'label' => 'Boosts',
                    'data' => $boosts['values'],
                    'backgroundColor' => '#F2B01E',
                    'borderRadius' => 6,
                    'maxBarThickness' => 22,
                ],
            ],
            'labels' => $slots['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'bottom', 'labels' => ['usePointStyle' => true, 'boxWidth' => 8, 'padding' => 16]],
            ],
            'scales' => [
                'x' => ['stacked' => true, 'grid' => ['display' => false], 'ticks' => ['maxTicksLimit' => 10]],
                'y' => ['stacked' => true, 'beginAtZero' => true, 'border' => ['display' => false]],
            ],
        ];
    }
}
