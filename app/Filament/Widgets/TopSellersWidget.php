<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopSellersWidget extends TableWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = ['md' => 6, 'xl' => 7];

    public function table(Table $table): Table
    {
        return $table
            ->heading('Top sellers')
            ->description('Most live ads right now')
            ->query(fn () => User::query()
                ->whereHas('listings', fn (Builder $query) => $query->where('status', 'active'))
                ->withCount([
                    'listings as live_count' => fn (Builder $query) => $query->where('status', 'active'),
                    'listings as sold_count' => fn (Builder $query) => $query->where('status', 'sold'),
                ])
                ->withSum(['listings as views_total' => fn (Builder $query) => $query->where('status', 'active')], 'views_count')
                ->orderByDesc('live_count'))
            ->columns([
                TextColumn::make('name')->weight(FontWeight::SemiBold)
                    ->icon(fn (User $record): ?Heroicon => $record->is_verified ? Heroicon::CheckBadge : null)
                    ->iconColor('success')
                    ->iconPosition('after')
                    ->description(fn (User $record): ?string => $record->phone),
                TextColumn::make('live_count')->label('Live')->numeric()->badge()->color('success'),
                TextColumn::make('sold_count')->label('Sold')->numeric(),
                TextColumn::make('views_total')->label('Views')->numeric(),
                TextColumn::make('rating_avg')->label('Rating')
                    ->formatStateUsing(fn ($state, User $record): string => $record->rating_count > 0 ? number_format((float) $state, 1).' ★' : '—')
                    ->color('warning'),
            ])
            ->recordUrl(fn (User $record): string => UserResource::getUrl('view', ['record' => $record]))
            ->paginated([5])
            ->defaultPaginationPageOption(5)
            ->emptyStateIcon(Heroicon::OutlinedUsers)
            ->emptyStateHeading('No active sellers yet');
    }
}
