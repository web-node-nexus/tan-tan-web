<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Resources\Reports\ReportResource;
use App\Filament\Support\CsvExport;
use App\Models\Report;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected ?string $subheading = 'Flagged ads from the community — review, resolve and keep a paper trail.';

    protected function getHeaderActions(): array
    {
        return [
            CsvExport::tableAction('reports', ReportResource::csvColumns()),
        ];
    }

    public function getTabs(): array
    {
        $counts = Report::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        return [
            'queue' => Tab::make('Needs action')
                ->badge(($counts['open'] ?? 0) + ($counts['reviewing'] ?? 0))
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('status', ['open', 'reviewing'])),
            'open' => Tab::make('Open')->badge($counts['open'] ?? 0)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'open')),
            'reviewing' => Tab::make('In review')->badge($counts['reviewing'] ?? 0)->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'reviewing')),
            'resolved' => Tab::make('Resolved')->badge($counts['resolved'] ?? 0)->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'resolved')),
            'dismissed' => Tab::make('Dismissed')->badge($counts['dismissed'] ?? 0)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'dismissed')),
            'all' => Tab::make('All')->badge($counts->sum()),
        ];
    }
}
