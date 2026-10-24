<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Review;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviewsReceived';

    protected static ?string $title = 'Reviews received';

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedStar;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->reviewsReceived()->count();
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
                TextColumn::make('rating')
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state).str_repeat('☆', 5 - $state))
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('comment')->wrap()->limit(80)->placeholder('—'),
                TextColumn::make('reviewer.name')->label('From'),
                TextColumn::make('listing.title')->label('Listing')->limit(30)->placeholder('—'),
                TextColumn::make('created_at')->label('Date')->since()->sortable(),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->modalDescription('Removes the review and recalculates the seller rating.')
                    ->after(fn (Review $record) => ReviewResource::recalculateRating($record->reviewee_id)),
            ])
            ->emptyStateHeading('No reviews yet')
            ->emptyStateIcon(Heroicon::OutlinedStar);
    }
}
