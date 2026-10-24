<?php

namespace App\Filament\Resources\Listings\Pages;

use App\Filament\Resources\Listings\ListingResource;
use App\Filament\Support\CsvExport;
use App\Models\Listing;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListListings extends ListRecords
{
    protected static string $resource = ListingResource::class;

    protected ?string $subheading = 'Review, approve, feature and moderate every ad on Tan Tan.';

    protected function getHeaderActions(): array
    {
        return [
            CsvExport::tableAction('listings', ListingResource::csvColumns()),
        ];
    }

    public function getTabs(): array
    {
        $counts = Listing::query()
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $tab = fn (string $label, ?string $status, string $color = 'gray'): Tab => Tab::make($label)
            ->badge($status ? ($counts[$status] ?? 0) : $counts->sum())
            ->badgeColor($color)
            ->modifyQueryUsing(fn (Builder $query): Builder => $status ? $query->where('status', $status) : $query);

        return [
            'all' => $tab('All', null),
            'pending' => $tab('Pending', 'pending', 'warning'),
            'active' => $tab('Active', 'active', 'success'),
            'featured' => Tab::make('Featured')
                ->badge(Listing::query()->where('is_featured', true)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_featured', true)),
            'sold' => $tab('Sold', 'sold', 'info'),
            'expired' => $tab('Expired', 'expired'),
            'rejected' => $tab('Rejected', 'rejected', 'danger'),
            'blocked' => $tab('Blocked', 'blocked', 'danger'),
        ];
    }
}
