<?php

namespace App\Filament\Resources\Promotions;

use App\Filament\Resources\Listings\ListingResource;
use App\Filament\Resources\Promotions\Pages\ListPromotions;
use App\Models\ListingPromotion;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class PromotionResource extends Resource
{
    protected static ?string $model = ListingPromotion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Monetization';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Boosts';

    protected static ?string $modelLabel = 'boost';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $live = ListingPromotion::query()->where('status', 'active')->where('ends_at', '>', now())->count();

        return $live > 0 ? (string) $live : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Live boosts';
    }

    public static function isLive(ListingPromotion $p): bool
    {
        return $p->status === 'active' && $p->ends_at?->isFuture();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['listing', 'user', 'package']))
            ->columns([
                TextColumn::make('listing.title')->label('Listing')->searchable()->limit(36)->weight(FontWeight::SemiBold)
                    ->url(fn (ListingPromotion $record): ?string => $record->listing ? ListingResource::getUrl('view', ['record' => $record->listing]) : null),
                TextColumn::make('user.name')->label('Seller')->searchable(),
                TextColumn::make('package.name_en')->label('Package')->badge()->color('warning')->placeholder('Manual (admin)'),
                TextColumn::make('state')
                    ->label('Status')
                    ->state(fn (ListingPromotion $record): string => match (true) {
                        self::isLive($record) => 'Live',
                        $record->status === 'cancelled' => 'Cancelled',
                        default => 'Ended',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Live' => 'success',
                        'Cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('starts_at')->label('Starts')->dateTime('d M Y')->sortable(),
                TextColumn::make('ends_at')->label('Ends')->dateTime('d M Y, H:i')->sortable()
                    ->description(fn (ListingPromotion $record): ?string => self::isLive($record) ? $record->ends_at->diffForHumans() : null),
            ])
            ->filters([
                SelectFilter::make('state')
                    ->label('Status')
                    ->options(['live' => 'Live', 'ended' => 'Ended', 'cancelled' => 'Cancelled'])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'live' => $query->where('status', 'active')->where('ends_at', '>', now()),
                        'ended' => $query->where('status', '!=', 'cancelled')->where('ends_at', '<=', now()),
                        'cancelled' => $query->where('status', 'cancelled'),
                        default => $query,
                    }),
                SelectFilter::make('package')->relationship('package', 'name_en'),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('extend')
                        ->label('Extend')
                        ->icon(Heroicon::OutlinedClock)
                        ->color('warning')
                        ->visible(fn (ListingPromotion $record): bool => self::isLive($record))
                        ->schema([
                            TextInput::make('days')->numeric()->minValue(1)->maxValue(90)->default(7)->required(),
                        ])
                        ->action(function (ListingPromotion $record, array $data): void {
                            DB::transaction(function () use ($record, $data): void {
                                $record->update(['ends_at' => $record->ends_at->copy()->addDays((int) $data['days'])]);
                                $record->listing?->forceFill(['is_featured' => true, 'featured_until' => $record->ends_at])->save();
                            });
                            Notification::make()->success()->title('Boost extended by '.$data['days'].' days')->send();
                        }),
                    Action::make('cancel')
                        ->label('Cancel boost')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('danger')
                        ->visible(fn (ListingPromotion $record): bool => self::isLive($record))
                        ->requiresConfirmation()
                        ->action(function (ListingPromotion $record): void {
                            DB::transaction(function () use ($record): void {
                                $record->update(['status' => 'cancelled', 'ends_at' => now()]);
                                $record->listing?->forceFill(['is_featured' => false, 'featured_until' => null])->save();
                            });
                            Notification::make()->success()->title('Boost cancelled')->send();
                        }),
                ]),
            ])
            ->striped()
            ->defaultSort('ends_at', 'desc')
            ->emptyStateIcon(Heroicon::OutlinedSparkles)
            ->emptyStateHeading('No boosts yet');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromotions::route('/'),
        ];
    }
}
