<?php

namespace App\Filament\Resources\Listings\RelationManagers;

use App\Filament\Support\ReportModeration;
use Filament\Actions\ActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'reports';

    protected static ?string $title = 'Reports';

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedFlag;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $open = $ownerRecord->reports()->whereIn('status', ['open', 'reviewing'])->count();

        return $open > 0 ? (string) $open : null;
    }

    public static function getBadgeColor(Model $ownerRecord, string $pageClass): ?string
    {
        return 'danger';
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
                TextColumn::make('reason')->badge()->color('gray')
                    ->formatStateUsing(fn (?string $state): string => ReportModeration::reasonLabel($state)),
                TextColumn::make('details')->limit(60)->placeholder('—')->wrap(),
                TextColumn::make('reporter.name')->label('Reporter'),
                TextColumn::make('status')->badge()
                    ->color(fn (?string $state): string => ReportModeration::statusColor($state))
                    ->formatStateUsing(fn (?string $state): string => ReportModeration::STATUS_LABELS[$state] ?? (string) $state),
                TextColumn::make('admin_note')->label('Moderator note')->limit(40)->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->label('Reported')->since()->sortable(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ReportModeration::startReview(),
                    ReportModeration::resolve(),
                    ReportModeration::dismiss(),
                ]),
            ])
            ->emptyStateHeading('No reports')
            ->emptyStateDescription('Nobody has flagged this listing.')
            ->emptyStateIcon(Heroicon::OutlinedShieldCheck);
    }
}
