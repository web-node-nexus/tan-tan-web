<?php

namespace App\Filament\Widgets;

use App\Filament\Support\Metrics;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ActivityChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = ['md' => 6, 'xl' => 8];

    protected ?string $heading = 'Marketplace activity';

    protected ?string $maxHeight = '300px';

    public function getDescription(): ?string
    {
        return 'New listings, sign-ups and chats · '.Metrics::PERIODS[(string) Metrics::days($this->pageFilters)];
    }

    protected function getData(): array
    {
        $days = Metrics::days($this->pageFilters);
        $listings = Metrics::series(Listing::query(), $days);
        $users = Metrics::series(User::query(), $days);
        $chats = Metrics::series(Conversation::query(), $days);

        return [
            'datasets' => [
                $this->line('Listings', $listings['values'], '#FF5A1F', true),
                $this->line('Sign-ups', $users['values'], '#0F9D8A'),
                $this->line('Chats', $chats['values'], '#6366F1'),
            ],
            'labels' => $listings['labels'],
        ];
    }

    /**
     * @param  list<float>  $values
     * @return array<string, mixed>
     */
    private function line(string $label, array $values, string $color, bool $fill = false): array
    {
        return [
            'label' => $label,
            'data' => $values,
            'borderColor' => $color,
            'backgroundColor' => $color.'1A',
            'fill' => $fill,
            'tension' => 0.35,
            'borderWidth' => 2,
            'pointRadius' => 0,
            'pointHoverRadius' => 4,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'interaction' => ['mode' => 'index', 'intersect' => false],
            'plugins' => [
                'legend' => ['position' => 'bottom', 'labels' => ['usePointStyle' => true, 'boxWidth' => 8, 'padding' => 16]],
            ],
            'scales' => [
                'x' => ['grid' => ['display' => false], 'ticks' => ['maxTicksLimit' => 8]],
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0], 'border' => ['display' => false]],
            ],
        ];
    }
}
