<?php

namespace App\Filament\Resources\Conversations\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OffersRelationManager extends RelationManager
{
    protected static string $relationship = 'offers';

    protected static ?string $title = 'Offers';

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedCurrencyRupee;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->offers()->count();

        return $count > 0 ? (string) $count : null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('amount')->money('INR', locale: 'en_IN', decimalPlaces: 0)->weight('bold'),
                TextColumn::make('status')->badge()->color(fn (?string $state): string => match ($state) {
                    'accepted' => 'success',
                    'rejected' => 'danger',
                    'pending' => 'warning',
                    default => 'gray',
                }),
                TextColumn::make('buyer.name')->label('Buyer'),
                TextColumn::make('created_at')->label('Made')->since(),
            ])
            ->emptyStateHeading('No offers in this chat')
            ->emptyStateIcon(Heroicon::OutlinedCurrencyRupee);
    }
}
