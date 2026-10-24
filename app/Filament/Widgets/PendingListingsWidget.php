<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Listings\ListingResource;
use App\Filament\Support\ListingModeration;
use App\Models\Listing;
use Filament\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingListingsWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = ['md' => 6, 'xl' => 7];

    public function table(Table $table): Table
    {
        return $table
            ->heading('Moderation queue')
            ->description('Newest ads waiting for approval')
            ->query(fn () => Listing::query()->where('status', 'pending')->with(['images', 'user', 'category'])->latest())
            ->columns([
                ImageColumn::make('cover')
                    ->label('')
                    ->state(fn (Listing $record): ?string => $record->images->first()?->url())
                    ->imageSize(40)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover'])
                    ->defaultImageUrl(asset('images/brand/logo-320.png')),
                TextColumn::make('title')->limit(32)->weight(FontWeight::SemiBold)
                    ->description(fn (Listing $record): string => ($record->user?->name ?? '—').' · '.($record->category?->name_en ?? '')),
                TextColumn::make('price')->money('INR', locale: 'en_IN', decimalPlaces: 0),
                TextColumn::make('created_at')->label('Waiting')->since(),
            ])
            ->recordActions([
                ListingModeration::approve()->iconButton()->tooltip('Approve'),
                ListingModeration::reject()->iconButton()->tooltip('Reject'),
                Action::make('open')->iconButton()->icon(Heroicon::OutlinedArrowTopRightOnSquare)->color('gray')->tooltip('Open')
                    ->url(fn (Listing $record): string => ListingResource::getUrl('view', ['record' => $record])),
            ])
            ->headerActions([
                Action::make('all')->label('View queue')->link()
                    ->url(ListingResource::getUrl('index', ['activeTab' => 'pending'])),
            ])
            ->paginated([5])
            ->defaultPaginationPageOption(5)
            ->emptyStateIcon(Heroicon::OutlinedCheckCircle)
            ->emptyStateHeading('All caught up')
            ->emptyStateDescription('No listings are waiting for review.');
    }
}
