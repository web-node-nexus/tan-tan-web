<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Payments\PaymentResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Payments';

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedBanknotes;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->payments()->count();

        return $count > 0 ? (string) $count : null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')->label('#')->prefix('PAY-'),
                TextColumn::make('purpose')->badge()->color('gray')
                    ->formatStateUsing(fn (?string $state): string => PaymentResource::PURPOSE_LABELS[$state] ?? (string) $state),
                TextColumn::make('amount')->money('INR', locale: 'en_IN', decimalPlaces: 0)
                    ->summarize(Sum::make()->label('Total')->money('INR', locale: 'en_IN', decimalPlaces: 0)),
                TextColumn::make('status')->badge()
                    ->color(fn (?string $state): string => PaymentResource::statusColor($state)),
                TextColumn::make('provider')->badge()->color('gray'),
                TextColumn::make('listing.title')->label('Listing')->limit(30)->placeholder('—'),
                TextColumn::make('created_at')->label('Date')->dateTime('d M Y, H:i')->sortable(),
            ])
            ->emptyStateHeading('No payments')
            ->emptyStateIcon(Heroicon::OutlinedBanknotes);
    }
}
