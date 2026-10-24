<?php

namespace App\Filament\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;

class Metrics
{
    public const PERIODS = [
        '7' => 'Last 7 days',
        '30' => 'Last 30 days',
        '90' => 'Last 90 days',
        '365' => 'Last 12 months',
    ];

    public const PALETTE = ['#FF5A1F', '#0F9D8A', '#F2B01E', '#6366F1', '#E5484D', '#0EA5E9', '#A1A1AA'];

    /**
     * @param  array<string, mixed>|null  $filters
     */
    public static function days(?array $filters): int
    {
        $days = (int) ($filters['period'] ?? 30);

        return array_key_exists((string) $days, self::PERIODS) ? $days : 30;
    }

    public static function start(int $days): CarbonImmutable
    {
        return CarbonImmutable::now()->subDays($days - 1)->startOfDay();
    }

    /**
     * Bucketed series for a query between start and now.
     * Daily buckets up to 90 days, monthly beyond that.
     *
     * @return array{labels: list<string>, values: list<float>}
     */
    public static function series(Builder $query, int $days, string $dateColumn = 'created_at', ?string $sumColumn = null): array
    {
        $start = self::start($days);
        $monthly = $days > 90;

        $rows = (clone $query)
            ->where($dateColumn, '>=', $start)
            ->selectRaw('DATE('.$dateColumn.') as d, '.($sumColumn ? 'SUM('.$sumColumn.')' : 'COUNT(*)').' as v')
            ->groupBy('d')
            ->pluck('v', 'd');

        $buckets = [];
        foreach (CarbonPeriod::create($start, $monthly ? '1 month' : '1 day', CarbonImmutable::now()) as $date) {
            $buckets[$monthly ? $date->format('Y-m') : $date->format('Y-m-d')] = 0.0;
        }

        foreach ($rows as $day => $value) {
            $key = $monthly ? substr((string) $day, 0, 7) : (string) $day;
            if (array_key_exists($key, $buckets)) {
                $buckets[$key] += (float) $value;
            }
        }

        $labels = array_map(
            fn (string $key): string => $monthly
                ? CarbonImmutable::createFromFormat('Y-m', $key)->format('M y')
                : CarbonImmutable::parse($key)->format('d M'),
            array_keys($buckets),
        );

        return ['labels' => $labels, 'values' => array_values($buckets)];
    }

    /**
     * Current vs previous equal-length window.
     *
     * @return array{current: float, previous: float, change: float|null}
     */
    public static function compare(Builder $query, int $days, string $dateColumn = 'created_at', ?string $sumColumn = null): array
    {
        $start = self::start($days);
        $prevStart = $start->subDays($days);

        $aggregate = fn (Builder $query): float => (float) ($sumColumn ? $query->sum($sumColumn) : $query->count());

        $current = $aggregate((clone $query)->where($dateColumn, '>=', $start));
        $previous = $aggregate((clone $query)->where($dateColumn, '>=', $prevStart)->where($dateColumn, '<', $start));

        return [
            'current' => $current,
            'previous' => $previous,
            'change' => $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : null,
        ];
    }

    public static function rupees(float $amount): string
    {
        return match (true) {
            $amount >= 1_00_00_000 => '₹'.round($amount / 1_00_00_000, 2).' Cr',
            $amount >= 1_00_000 => '₹'.round($amount / 1_00_000, 2).' L',
            default => '₹'.number_format($amount, 0),
        };
    }
}
