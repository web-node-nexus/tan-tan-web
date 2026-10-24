<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Listings\ListingResource;
use App\Filament\Resources\Reports\Pages\ListReports;
use App\Filament\Resources\Reports\Pages\ViewReport;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Support\CsvExport;
use App\Filament\Support\ListingModeration;
use App\Filament\Support\ReportModeration;
use App\Models\Report;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Flag;

    protected static string|UnitEnum|null $navigationGroup = 'Trust & safety';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Reports';

    public static function getNavigationBadge(): ?string
    {
        $open = Report::query()->whereIn('status', ['open', 'reviewing'])->count();

        return $open > 0 ? (string) $open : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'danger';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('Report')
                        ->icon(Heroicon::OutlinedFlag)
                        ->columns(2)
                        ->schema([
                            TextEntry::make('reason')->badge()->color('danger')
                                ->formatStateUsing(fn (?string $state): string => ReportModeration::reasonLabel($state)),
                            TextEntry::make('status')->badge()
                                ->color(fn (?string $state): string => ReportModeration::statusColor($state))
                                ->formatStateUsing(fn (?string $state): string => ReportModeration::STATUS_LABELS[$state] ?? (string) $state),
                            TextEntry::make('details')->columnSpanFull()->placeholder('Reporter added no details.')->prose(),
                            TextEntry::make('created_at')->label('Reported')->dateTime('d M Y, H:i'),
                            TextEntry::make('reporter.name')->label('Reporter')
                                ->url(fn (Report $record): ?string => $record->reporter ? UserResource::getUrl('view', ['record' => $record->reporter]) : null)
                                ->color('primary'),
                        ]),
                    Section::make('Resolution')
                        ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                        ->columns(2)
                        ->visible(fn (Report $record): bool => in_array($record->status, ['resolved', 'dismissed'], true))
                        ->schema([
                            TextEntry::make('admin_note')->label('Moderator note')->columnSpanFull(),
                            TextEntry::make('resolver.name')->label('Handled by')->placeholder('—'),
                            TextEntry::make('resolved_at')->label('Closed')->dateTime('d M Y, H:i'),
                        ]),
                ])->columnSpan(['lg' => 2]),

                Section::make('Reported listing')
                    ->icon(Heroicon::OutlinedRectangleStack)
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        TextEntry::make('listing.title')->label('Title')
                            ->weight(FontWeight::SemiBold)
                            ->url(fn (Report $record): ?string => $record->listing ? ListingResource::getUrl('view', ['record' => $record->listing]) : null)
                            ->color('primary'),
                        TextEntry::make('listing.price')->label('Price')->money('INR', locale: 'en_IN', decimalPlaces: 0),
                        TextEntry::make('listing.status')->label('Listing status')->badge()
                            ->color(fn (?string $state): string => ListingModeration::statusColor($state))
                            ->formatStateUsing(fn (?string $state): string => ListingModeration::STATUS_LABELS[$state] ?? (string) $state),
                        TextEntry::make('listing.user.name')->label('Seller'),
                        TextEntry::make('listing_reports')->label('Total reports on this ad')
                            ->state(fn (Report $record): int => Report::query()->where('listing_id', $record->listing_id)->count())
                            ->badge()->color('danger'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['listing.user', 'reporter', 'resolver']))
            ->columns([
                TextColumn::make('id')->label('#')->prefix('R-')->sortable(),
                TextColumn::make('listing.title')
                    ->label('Listing')
                    ->searchable()
                    ->limit(36)
                    ->weight(FontWeight::SemiBold)
                    ->description(fn (Report $record): ?string => $record->listing?->user?->name ? 'Seller: '.$record->listing->user->name : null),
                TextColumn::make('reason')->badge()->color('gray')
                    ->formatStateUsing(fn (?string $state): string => ReportModeration::reasonLabel($state)),
                TextColumn::make('details')->limit(50)->placeholder('—')->wrap()->toggleable(),
                TextColumn::make('reporter.name')->label('Reporter')->searchable(),
                TextColumn::make('status')->badge()
                    ->color(fn (?string $state): string => ReportModeration::statusColor($state))
                    ->formatStateUsing(fn (?string $state): string => ReportModeration::STATUS_LABELS[$state] ?? (string) $state)
                    ->sortable(),
                TextColumn::make('resolver.name')->label('Handled by')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Reported')->since()->dateTimeTooltip('d M Y, H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ReportModeration::STATUS_LABELS)->multiple(),
                SelectFilter::make('reason')->options(ReportModeration::REASON_LABELS)->multiple(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    ReportModeration::startReview(),
                    ReportModeration::resolve(),
                    ReportModeration::dismiss(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ReportModeration::bulkResolve(),
                    ReportModeration::bulkDismiss(),
                    CsvExport::bulkAction('reports', self::csvColumns()),
                ]),
            ])
            ->recordUrl(fn (Report $record): string => self::getUrl('view', ['record' => $record]))
            ->poll('30s')
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon(Heroicon::OutlinedShieldCheck)
            ->emptyStateHeading('Queue is clear')
            ->emptyStateDescription('No reports in this view.');
    }

    /**
     * @return array<string, string|\Closure>
     */
    public static function csvColumns(): array
    {
        return [
            'ID' => 'id',
            'Listing' => 'listing.title',
            'Listing ID' => 'listing_id',
            'Reason' => 'reason',
            'Details' => 'details',
            'Reporter' => 'reporter.name',
            'Status' => 'status',
            'Moderator note' => 'admin_note',
            'Handled by' => 'resolver.name',
            'Reported at' => 'created_at',
            'Closed at' => 'resolved_at',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReports::route('/'),
            'view' => ViewReport::route('/{record}'),
        ];
    }
}
