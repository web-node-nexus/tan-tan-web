<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Support\CsvExport;
use App\Models\ListingPayment;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected ?string $subheading = 'Every rupee collected for extra ad slots and boosts.';

    protected function getHeaderActions(): array
    {
        return [
            CsvExport::tableAction('payments', PaymentResource::csvColumns()),
        ];
    }

    public function getTabs(): array
    {
        $counts = ListingPayment::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        return [
            'all' => Tab::make('All')->badge($counts->sum()),
            'successful' => Tab::make('Successful')
                ->badge(($counts['paid'] ?? 0) + ($counts['consumed'] ?? 0))
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('status', ['paid', 'consumed'])),
            'pending' => Tab::make('Pending')->badge($counts['pending'] ?? 0)->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'pending')),
            'failed' => Tab::make('Failed')->badge($counts['failed'] ?? 0)->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'failed')),
            'refunded' => Tab::make('Refunded')->badge($counts['refunded'] ?? 0)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'refunded')),
        ];
    }
}
