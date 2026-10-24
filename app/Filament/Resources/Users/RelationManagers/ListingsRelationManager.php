<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Listings\ListingResource;
use App\Filament\Support\ListingModeration;
use App\Models\Listing;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ListingsRelationManager extends RelationManager
{
    protected static string $relationship = 'listings';

    protected static ?string $title = 'Listings';

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedRectangleStack;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->listings()->count();
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable()->limit(40)->weight('semibold'),
                TextColumn::make('price')->money('INR', locale: 'en_IN', decimalPlaces: 0)->sortable(),
                TextColumn::make('status')->badge()
                    ->color(fn (?string $state): string => ListingModeration::statusColor($state))
                    ->formatStateUsing(fn (?string $state): string => ListingModeration::STATUS_LABELS[$state] ?? (string) $state),
                IconColumn::make('is_featured')->label('Featured')->boolean()->trueIcon(Heroicon::Star)->trueColor('warning'),
                TextColumn::make('views_count')->label('Views')->numeric()->sortable(),
                TextColumn::make('created_at')->label('Posted')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ListingModeration::STATUS_LABELS),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('open')
                        ->label('Open')
                        ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                        ->url(fn (Listing $record): string => ListingResource::getUrl('view', ['record' => $record])),
                    ListingModeration::approve(),
                    ListingModeration::reject(),
                    ListingModeration::block(),
                ]),
            ])
            ->emptyStateHeading('No listings posted')
            ->emptyStateIcon(Heroicon::OutlinedRectangleStack);
    }
}
