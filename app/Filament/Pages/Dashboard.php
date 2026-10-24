<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminHeroWidget;
use App\Filament\Widgets\MarketplaceStatsWidget;
use App\Filament\Widgets\OpsPulseWidget;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RoadmapWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{
    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Home';

    protected static ?string $title = 'Ops dashboard';

    public function getColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 2,
        ];
    }

    /**
     * @return array<class-string<\Filament\Widgets\Widget> | \Filament\Widgets\WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            AdminHeroWidget::class,
            MarketplaceStatsWidget::class,
            QuickActionsWidget::class,
            OpsPulseWidget::class,
            RoadmapWidget::class,
            AccountWidget::class,
        ];
    }
}
