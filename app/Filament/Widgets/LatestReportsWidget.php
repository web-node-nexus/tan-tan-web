<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\ReportResource;
use App\Filament\Support\ReportModeration;
use App\Models\Report;
use Filament\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestReportsWidget extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = ['md' => 6, 'xl' => 5];

    public function table(Table $table): Table
    {
        return $table
            ->heading('Open reports')
            ->description('Community flags needing a decision')
            ->query(fn () => Report::query()->whereIn('status', ['open', 'reviewing'])->with('listing')->latest())
            ->columns([
                TextColumn::make('listing.title')->label('Listing')->limit(24)->weight(FontWeight::SemiBold)
                    ->description(fn (Report $record): string => ReportModeration::reasonLabel($record->reason)),
                TextColumn::make('status')->badge()
                    ->color(fn (?string $state): string => ReportModeration::statusColor($state))
                    ->formatStateUsing(fn (?string $state): string => ReportModeration::STATUS_LABELS[$state] ?? (string) $state),
                TextColumn::make('created_at')->label('')->since(),
            ])
            ->recordUrl(fn (Report $record): string => ReportResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Action::make('all')->label('View all')->link()->url(ReportResource::getUrl()),
            ])
            ->paginated([5])
            ->defaultPaginationPageOption(5)
            ->emptyStateIcon(Heroicon::OutlinedShieldCheck)
            ->emptyStateHeading('Nothing flagged')
            ->emptyStateDescription('The community has no open reports.');
    }
}
