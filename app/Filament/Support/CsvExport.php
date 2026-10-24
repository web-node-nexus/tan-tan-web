<?php

namespace App\Filament\Support;

use BackedEnum;
use Closure;
use DateTimeInterface;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExport
{
    /**
     * Header action that exports the table's current filtered + sorted query.
     *
     * @param  array<string, string|Closure>  $columns  heading => dotted attribute or closure(record)
     */
    public static function tableAction(string $name, array $columns): Action
    {
        return Action::make('exportCsv')
            ->label('Export CSV')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->action(fn ($livewire): StreamedResponse => self::download(
                $livewire->getFilteredSortedTableQuery(),
                $columns,
                $name,
            ));
    }

    /**
     * @param  array<string, string|Closure>  $columns
     */
    public static function bulkAction(string $name, array $columns): BulkAction
    {
        return BulkAction::make('exportSelectedCsv')
            ->label('Export selected')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->deselectRecordsAfterCompletion()
            ->action(fn (Collection $records): StreamedResponse => self::download($records, $columns, $name));
    }

    /**
     * @param  array<string, string|Closure>  $columns
     */
    public static function download(Builder|Collection $source, array $columns, string $name): StreamedResponse
    {
        $filename = $name.'-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($source, $columns): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, array_keys($columns));

            $rows = $source instanceof Builder ? $source->lazy(500) : $source;

            foreach ($rows as $record) {
                fputcsv($out, array_map(
                    fn (string|Closure $column): string => self::format(
                        $column instanceof Closure ? $column($record) : data_get($record, $column),
                    ),
                    $columns,
                ));
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private static function format(mixed $value): string
    {
        return match (true) {
            $value === null => '',
            is_bool($value) => $value ? 'Yes' : 'No',
            $value instanceof DateTimeInterface => $value->format('Y-m-d H:i:s'),
            $value instanceof BackedEnum => (string) $value->value,
            is_array($value) => json_encode($value, JSON_UNESCAPED_UNICODE),
            default => (string) $value,
        };
    }
}
