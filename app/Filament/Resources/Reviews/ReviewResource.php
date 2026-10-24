<?php

namespace App\Filament\Resources\Reviews;

use App\Filament\Resources\Reviews\Pages\ManageReviews;
use App\Models\Review;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = 'Trust & safety';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function recalculateRating(int $userId): void
    {
        $stats = Review::query()->where('reviewee_id', $userId)
            ->selectRaw('avg(rating) as avg_rating, count(*) as total')
            ->first();

        User::query()->whereKey($userId)->update([
            'rating_avg' => round((float) ($stats->avg_rating ?? 0), 2),
            'rating_count' => (int) ($stats->total ?? 0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['reviewer', 'reviewee', 'listing']))
            ->columns([
                TextColumn::make('rating')
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state).str_repeat('☆', 5 - $state))
                    ->color(fn (int $state): string => $state <= 2 ? 'danger' : 'warning')
                    ->sortable()
                    ->summarize(Average::make()->label('Avg')->numeric(decimalPlaces: 2)),
                TextColumn::make('comment')->wrap()->limit(90)->searchable()->placeholder('—'),
                TextColumn::make('reviewee.name')->label('Seller')->searchable(),
                TextColumn::make('reviewer.name')->label('Reviewer')->searchable(),
                TextColumn::make('listing.title')->label('Listing')->limit(30)->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->label('Posted')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('rating')->options([5 => '5 ★', 4 => '4 ★', 3 => '3 ★', 2 => '2 ★', 1 => '1 ★'])->multiple(),
                Filter::make('negative')->label('Negative only (≤ 2★)')->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('rating', '<=', 2)),
                Filter::make('with_comment')->label('Has comment')->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('comment')->where('comment', '!=', '')),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->modalDescription('Removes the review and recalculates the seller rating.')
                    ->after(fn (Review $record) => self::recalculateRating($record->reviewee_id)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(fn (Collection $records) => $records->pluck('reviewee_id')->unique()->each(fn (int $id) => self::recalculateRating($id))),
                ]),
            ])
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon(Heroicon::OutlinedStar)
            ->emptyStateHeading('No reviews yet');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageReviews::route('/'),
        ];
    }
}
