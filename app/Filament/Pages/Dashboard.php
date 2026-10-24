<?php

namespace App\Filament\Pages;

use App\Filament\Support\Metrics;
use App\Filament\Widgets\ActivityChartWidget;
use App\Filament\Widgets\CategoryShareChartWidget;
use App\Filament\Widgets\KpiStatsWidget;
use App\Filament\Widgets\LatestReportsWidget;
use App\Filament\Widgets\ListingStatusChartWidget;
use App\Filament\Widgets\PendingListingsWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\SystemHealthWidget;
use App\Filament\Widgets\TopSellersWidget;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|\BackedEnum|null $activeNavigationIcon = Heroicon::Squares2x2;

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    public function getSubheading(): ?string
    {
        $hour = (int) now()->timezone('Asia/Kolkata')->format('G');
        $greeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        return $greeting.', '.str(auth()->user()?->name ?? 'there')->before(' ').' — here’s how Tan Tan is doing.';
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->columns(['md' => 3, 'xl' => 4])
            ->components([
                Select::make('period')
                    ->hiddenLabel()
                    ->options(Metrics::PERIODS)
                    ->default('30')
                    ->selectablePlaceholder(false)
                    ->native(false)
                    ->prefixIcon(Heroicon::OutlinedCalendarDays),
            ]);
    }

    public function getColumns(): int|array
    {
        return ['default' => 1, 'md' => 6, 'xl' => 12];
    }

    public function getWidgets(): array
    {
        return [
            KpiStatsWidget::class,
            ActivityChartWidget::class,
            CategoryShareChartWidget::class,
            PendingListingsWidget::class,
            LatestReportsWidget::class,
            RevenueChartWidget::class,
            ListingStatusChartWidget::class,
            TopSellersWidget::class,
            SystemHealthWidget::class,
        ];
    }
}
