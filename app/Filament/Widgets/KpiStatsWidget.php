<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Listings\ListingResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\Reports\ReportResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Support\Metrics;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\ListingPayment;
use App\Models\Report;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KpiStatsWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $days = Metrics::days($this->pageFilters);
        $window = Metrics::PERIODS[(string) $days];

        $paid = ListingPayment::query()->whereIn('status', ['paid', 'consumed']);
        $revenue = Metrics::compare($paid, $days, 'created_at', 'amount');
        $listings = Metrics::compare(Listing::query(), $days);
        $users = Metrics::compare(User::query(), $days);
        $chats = Metrics::compare(Conversation::query(), $days);

        $live = Listing::query()->where('status', 'active')->count();
        $featured = Listing::query()->where('status', 'active')->where('is_featured', true)->count();
        $pending = Listing::query()->where('status', 'pending')->count();
        $openReports = Report::query()->whereIn('status', ['open', 'reviewing'])->count();
        $suspended = User::query()->whereNotNull('banned_at')->count();

        return [
            $this->trendStat('Revenue', Metrics::rupees($revenue['current']), $revenue['change'], $window)
                ->icon(Heroicon::OutlinedBanknotes)
                ->chart(Metrics::series($paid, $days, 'created_at', 'amount')['values'])
                ->url(PaymentResource::getUrl()),
            $this->trendStat('New listings', number_format($listings['current']), $listings['change'], $window)
                ->icon(Heroicon::OutlinedRectangleStack)
                ->chart(Metrics::series(Listing::query(), $days)['values'])
                ->url(ListingResource::getUrl()),
            $this->trendStat('New users', number_format($users['current']), $users['change'], $window)
                ->icon(Heroicon::OutlinedUserPlus)
                ->chart(Metrics::series(User::query(), $days)['values'])
                ->url(UserResource::getUrl()),
            $this->trendStat('Chats started', number_format($chats['current']), $chats['change'], $window)
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->chart(Metrics::series(Conversation::query(), $days)['values']),

            Stat::make('Live listings', number_format($live))
                ->icon(Heroicon::OutlinedSignal)
                ->description($featured.' featured right now')
                ->descriptionIcon(Heroicon::Star)
                ->color('success')
                ->url(ListingResource::getUrl('index', ['activeTab' => 'active'])),
            Stat::make('Awaiting review', number_format($pending))
                ->icon(Heroicon::OutlinedClock)
                ->description($pending > 0 ? 'Open moderation queue' : 'Queue is clear')
                ->descriptionIcon($pending > 0 ? Heroicon::ArrowRight : Heroicon::CheckCircle)
                ->color($pending > 0 ? 'warning' : 'success')
                ->url(ListingResource::getUrl('index', ['activeTab' => 'pending'])),
            Stat::make('Open reports', number_format($openReports))
                ->icon(Heroicon::OutlinedFlag)
                ->description($openReports > 0 ? 'Needs a moderator' : 'Nothing flagged')
                ->descriptionIcon($openReports > 0 ? Heroicon::ExclamationTriangle : Heroicon::ShieldCheck)
                ->color($openReports > 0 ? 'danger' : 'success')
                ->url(ReportResource::getUrl()),
            Stat::make('Total users', number_format(User::query()->count()))
                ->icon(Heroicon::OutlinedUsers)
                ->description($suspended > 0 ? $suspended.' suspended' : 'No suspended accounts')
                ->descriptionIcon(Heroicon::NoSymbol)
                ->color('gray')
                ->url(UserResource::getUrl()),
        ];
    }

    private function trendStat(string $label, string $value, ?float $change, string $window): Stat
    {
        $up = ($change ?? 0) >= 0;

        return Stat::make($label, $value)
            ->description($change === null ? $window : ($up ? '+' : '').$change.'% vs previous period')
            ->descriptionIcon($change === null ? Heroicon::OutlinedCalendarDays : ($up ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown))
            ->color($change === null ? 'gray' : ($up ? 'success' : 'danger'));
    }
}
