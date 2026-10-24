<?php

namespace App\Filament\Resources\Payments;

use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Support\CsvExport;
use App\Models\ListingPayment;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PaymentResource extends Resource
{
    protected static ?string $model = ListingPayment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Monetization';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Payments';

    protected static ?string $modelLabel = 'payment';

    public const PURPOSE_LABELS = [
        'extra_ad' => 'Extra ad slot',
        'boost' => 'Boost',
    ];

    public const STATUS_LABELS = [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'consumed' => 'Consumed',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
    ];

    public static function statusColor(?string $status): string
    {
        return match ($status) {
            'paid', 'consumed' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            default => 'gray',
        };
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'listing']))
            ->columns([
                TextColumn::make('id')->label('Payment')->prefix('PAY-')->searchable()->sortable()->weight(FontWeight::SemiBold),
                TextColumn::make('user.name')->label('Customer')->searchable()
                    ->description(fn (ListingPayment $record): ?string => $record->user?->phone),
                TextColumn::make('purpose')->badge()->color('gray')
                    ->formatStateUsing(fn (?string $state): string => self::PURPOSE_LABELS[$state] ?? (string) $state),
                TextColumn::make('amount')
                    ->money('INR', locale: 'en_IN', decimalPlaces: 0)
                    ->weight(FontWeight::Bold)
                    ->sortable()
                    ->alignEnd()
                    ->summarize([
                        Sum::make()->label('Collected')->money('INR', locale: 'en_IN', decimalPlaces: 0)
                            ->query(fn ($query) => $query->whereIn('status', ['paid', 'consumed'])),
                    ]),
                TextColumn::make('status')->badge()
                    ->color(fn (?string $state): string => self::statusColor($state))
                    ->formatStateUsing(fn (?string $state): string => self::STATUS_LABELS[$state] ?? (string) $state)
                    ->sortable(),
                TextColumn::make('provider')->badge()->color('gray')->toggleable(),
                TextColumn::make('provider_ref')->label('Gateway ref')->copyable()->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('listing.title')->label('Listing')->limit(28)->placeholder('—')->toggleable(),
                TextColumn::make('paid_at')->label('Paid')->dateTime('d M Y, H:i')->sortable()->placeholder('—'),
                TextColumn::make('created_at')->label('Created')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(self::STATUS_LABELS)->multiple(),
                SelectFilter::make('purpose')->options(self::PURPOSE_LABELS),
                SelectFilter::make('provider')->options(['demo' => 'Demo', 'razorpay' => 'Razorpay']),
                Filter::make('created_at')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('from')->native(false),
                            DatePicker::make('until')->native(false),
                        ]),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $v) => $query->whereDate('created_at', '>=', $v))
                        ->when($data['until'] ?? null, fn (Builder $query, $v) => $query->whereDate('created_at', '<=', $v))),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                Action::make('refund')
                    ->label('Mark refunded')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('gray')
                    ->visible(fn (ListingPayment $record): bool => $record->status === 'paid')
                    ->requiresConfirmation()
                    ->modalDescription('Records the refund in Tan Tan. Issue the actual refund from the payment gateway dashboard.')
                    ->action(function (ListingPayment $record): void {
                        $record->update(['status' => 'refunded']);
                        Notification::make()->success()->title('Payment marked refunded')->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    CsvExport::bulkAction('payments', self::csvColumns()),
                ]),
            ])
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon(Heroicon::OutlinedBanknotes)
            ->emptyStateHeading('No payments yet')
            ->emptyStateDescription('Extra ad slots and boosts paid through the app appear here.');
    }

    /**
     * @return array<string, string|\Closure>
     */
    public static function csvColumns(): array
    {
        return [
            'Payment ID' => fn (ListingPayment $p): string => 'PAY-'.$p->id,
            'Customer' => 'user.name',
            'Phone' => 'user.phone',
            'Purpose' => 'purpose',
            'Amount (INR)' => 'amount',
            'Status' => 'status',
            'Provider' => 'provider',
            'Gateway ref' => 'provider_ref',
            'Listing' => 'listing.title',
            'Paid at' => 'paid_at',
            'Created at' => 'created_at',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
        ];
    }
}
