<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MarketplaceStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -2;

    protected ?string $heading = 'Marketplace snapshot';

    protected ?string $description = 'Live counts from the current database — zeros are fine until Phase 2 data models fill in.';

    protected function getStats(): array
    {
        $users = $this->safeCount(User::class);
        $listings = $this->safeTableCount('listings');
        $reports = $this->safeTableCount('reports');
        $payments = $this->safeTableCount('payments');

        return [
            Stat::make('Users', number_format($users))
                ->description('Accounts in the system')
                ->descriptionIcon(Heroicon::OutlinedUsers)
                ->chart($this->spark([$users, max(1, $users), max(2, (int) ($users * 1.1)), max(2, $users + 1), max(3, $users + 2)]))
                ->color('primary'),
            Stat::make('Listings', number_format($listings))
                ->description($listings === 0 ? 'Awaiting Post Ad API' : 'Published inventory')
                ->descriptionIcon(Heroicon::OutlinedRectangleStack)
                ->chart($this->spark([2, 3, 4, 6, 5, 8, 7]))
                ->color('warning'),
            Stat::make('Reports queue', number_format($reports))
                ->description($reports === 0 ? 'Moderation queue empty' : 'Needs review')
                ->descriptionIcon(Heroicon::OutlinedShieldExclamation)
                ->chart($this->spark([1, 1, 2, 1, 3, 2, 2]))
                ->color('danger'),
            Stat::make('Package pays', number_format($payments))
                ->description($payments === 0 ? 'Razorpay hooks later' : 'Boost checkouts')
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->chart($this->spark([1, 2, 2, 3, 4, 3, 5]))
                ->color('success'),
        ];
    }

    /**
     * @param  class-string  $model
     */
    private function safeCount(string $model): int
    {
        try {
            return (int) $model::query()->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function safeTableCount(string $table): int
    {
        try {
            if (! Schema::hasTable($table)) {
                return 0;
            }

            return (int) DB::table($table)->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * @param  list<int|float>  $points
     * @return list<float>
     */
    private function spark(array $points): array
    {
        return array_map(static fn ($n): float => (float) $n, $points);
    }
}
