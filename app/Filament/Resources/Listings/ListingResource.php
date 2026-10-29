<?php

namespace App\Filament\Resources\Listings;

use App\Filament\Resources\Listings\Pages\EditListing;
use App\Filament\Resources\Listings\Pages\ListListings;
use App\Filament\Resources\Listings\Pages\ViewListing;
use App\Filament\Resources\Listings\RelationManagers\ConversationsRelationManager;
use App\Filament\Resources\Listings\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\Listings\RelationManagers\ReportsRelationManager;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Support\CsvExport;
use App\Filament\Support\ListingModeration;
use App\Models\Listing;
use App\Models\ListingImage;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;
use UnitEnum;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::RectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationBadge(): ?string
    {
        $pending = Listing::query()->where('status', 'pending')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Awaiting review';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'user.name', 'user.phone'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Price' => '₹'.Number::format((float) $record->price, locale: 'en_IN'),
            'Status' => ListingModeration::STATUS_LABELS[$record->status] ?? $record->status,
            'Seller' => $record->user?->name,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('user');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('Ad details')
                        ->icon(Heroicon::OutlinedDocumentText)
                        ->columns(2)
                        ->schema([
                            TextInput::make('title')->required()->maxLength(120)->columnSpanFull(),
                            Textarea::make('description')->required()->rows(6)->columnSpanFull(),
                            Select::make('category_id')
                                ->label('Category')
                                ->relationship('category', 'name_en')
                                ->required()
                                ->searchable()
                                ->preload(),
                            Select::make('condition')
                                ->options(ListingModeration::CONDITION_LABELS)
                                ->required()
                                ->native(false),
                        ]),
                    Section::make('Pricing')
                        ->icon(Heroicon::OutlinedCurrencyRupee)
                        ->columns(3)
                        ->schema([
                            TextInput::make('price')->numeric()->prefix('₹')->required()->minValue(0),
                            TextInput::make('previous_price')->label('Previous price')->numeric()->prefix('₹'),
                            TextInput::make('posting_fee')->label('Posting fee')->numeric()->prefix('₹')->disabled()->dehydrated(false),
                            Toggle::make('is_negotiable')->label('Price negotiable')->inline(false),
                        ]),
                    Section::make('Location')
                        ->icon(Heroicon::OutlinedMapPin)
                        ->columns(2)
                        ->collapsible()
                        ->schema([
                            Select::make('city_id')->label('City')->relationship('city', 'name')->searchable()->preload(),
                            Select::make('area_id')->label('Area')->relationship('area', 'name')->searchable()->preload(),
                            TextInput::make('location_label')->label('Locality')->maxLength(120),
                            TextInput::make('latitude')->numeric(),
                            TextInput::make('longitude')->numeric(),
                        ]),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    Section::make('Moderation')
                        ->icon(Heroicon::OutlinedShieldCheck)
                        ->schema([
                            Select::make('status')
                                ->options(ListingModeration::STATUS_LABELS)
                                ->required()
                                ->native(false)
                                ->live(),
                            Textarea::make('moderation_note')
                                ->label('Moderation note')
                                ->rows(3)
                                ->maxLength(500)
                                ->visible(fn ($get): bool => in_array($get('status'), ['rejected', 'blocked'], true)),
                            DateTimePicker::make('expires_at')->label('Expires at')->native(false),
                        ]),
                    Section::make('Promotion')
                        ->icon(Heroicon::OutlinedSparkles)
                        ->schema([
                            Toggle::make('is_featured')->label('Featured')->live(),
                            DateTimePicker::make('featured_until')
                                ->label('Featured until')
                                ->native(false)
                                ->visible(fn ($get): bool => (bool) $get('is_featured')),
                        ]),
                    Section::make('Seller')
                        ->icon(Heroicon::OutlinedUser)
                        ->schema([
                            Select::make('user_id')
                                ->label('Owner')
                                ->relationship('user', 'name')
                                ->searchable(['name', 'phone', 'email'])
                                ->required(),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make()
                        ->schema([
                            ImageEntry::make('gallery')
                                ->hiddenLabel()
                                ->state(fn (Listing $record): array => $record->images->map(fn (ListingImage $i): string => $i->url())->all())
                                ->imageHeight(160)
                                ->extraImgAttributes(['class' => 'rounded-xl object-cover'])
                                ->placeholder('No photos uploaded'),
                            TextEntry::make('title')
                                ->hiddenLabel()
                                ->size('lg')
                                ->weight(FontWeight::Bold),
                            Grid::make(4)->schema([
                                TextEntry::make('price')->money('INR', locale: 'en_IN', decimalPlaces: 0)->weight(FontWeight::ExtraBold),
                                TextEntry::make('previous_price')->label('Was')->money('INR', locale: 'en_IN', decimalPlaces: 0)->placeholder('—'),
                                TextEntry::make('condition')->badge()->color('gray')
                                    ->formatStateUsing(fn (?string $state): string => ListingModeration::CONDITION_LABELS[$state] ?? (string) $state),
                                IconEntry::make('is_negotiable')->label('Negotiable')->boolean(),
                            ]),
                            TextEntry::make('description')->prose()->columnSpanFull(),
                        ]),
                    Section::make('Performance')
                        ->icon(Heroicon::OutlinedChartBar)
                        ->columns(4)
                        ->schema([
                            TextEntry::make('views_count')->label('Views')->numeric()->icon(Heroicon::OutlinedEye),
                            TextEntry::make('favourites_count')->label('Favourites')->numeric()->icon(Heroicon::OutlinedHeart),
                            TextEntry::make('chats_count')->label('Chats')->numeric()->icon(Heroicon::OutlinedChatBubbleLeftRight),
                            TextEntry::make('reports_count')->label('Reports')
                                ->state(fn (Listing $record): int => $record->reports()->count())
                                ->badge()
                                ->color(fn (int $state): string => $state > 0 ? 'danger' : 'gray'),
                        ]),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    Section::make('Status')
                        ->icon(Heroicon::OutlinedShieldCheck)
                        ->schema([
                            TextEntry::make('status')
                                ->badge()
                                ->color(fn (?string $state): string => ListingModeration::statusColor($state))
                                ->icon(fn (?string $state): Heroicon => ListingModeration::statusIcon($state))
                                ->formatStateUsing(fn (?string $state): string => ListingModeration::STATUS_LABELS[$state] ?? (string) $state),
                            TextEntry::make('moderation_note')->label('Moderation note')->placeholder('—')
                                ->visible(fn (Listing $record): bool => filled($record->moderation_note)),
                            TextEntry::make('moderated_at')->label('Last moderated')->since()->placeholder('Never'),
                            IconEntry::make('is_featured')->label('Featured')->boolean()
                                ->trueIcon(Heroicon::Star)->trueColor('warning'),
                            TextEntry::make('featured_until')->label('Featured until')->dateTime('d M Y, H:i')->placeholder('—'),
                        ]),
                    Section::make('Seller')
                        ->icon(Heroicon::OutlinedUser)
                        ->schema([
                            TextEntry::make('user.name')->label('Name')
                                ->url(fn (Listing $record): ?string => $record->user ? UserResource::getUrl('view', ['record' => $record->user]) : null)
                                ->color('primary')
                                ->weight(FontWeight::SemiBold),
                            TextEntry::make('user.phone')->label('Phone')->copyable()->placeholder('—'),
                            IconEntry::make('user.is_verified')->label('Verified seller')->boolean(),
                        ]),
                    Section::make('Meta')
                        ->icon(Heroicon::OutlinedInformationCircle)
                        ->collapsible()
                        ->schema([
                            TextEntry::make('category.name_en')->label('Category'),
                            TextEntry::make('city.name')->label('City')->placeholder('—'),
                            TextEntry::make('location_label')->label('Locality')->placeholder('—'),
                            TextEntry::make('published_at')->label('Published')->dateTime('d M Y, H:i')->placeholder('—'),
                            TextEntry::make('expires_at')->label('Expires')->dateTime('d M Y, H:i')->placeholder('—'),
                            TextEntry::make('slug')->copyable()->size('xs')->color('gray'),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['images', 'user', 'category', 'city'])->withCount('reports'))
            ->columns([
                ImageColumn::make('cover')
                    ->label('')
                    ->state(fn (Listing $record): ?string => $record->images->firstWhere('is_cover', true)?->url() ?? $record->images->first()?->url())
                    ->imageSize(44)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover'])
                    ->defaultImageUrl(asset('images/brand/logo-320.png')),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->limit(42)
                    ->tooltip(fn (Listing $record): string => $record->title)
                    ->description(fn (Listing $record): string => trim(($record->category?->name_en ?? '').' · '.($record->city?->name ?? 'No city'), ' ·')),
                TextColumn::make('price')
                    ->money('INR', locale: 'en_IN', decimalPlaces: 0)
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => ListingModeration::statusColor($state))
                    ->icon(fn (?string $state): Heroicon => ListingModeration::statusIcon($state))
                    ->formatStateUsing(fn (?string $state): string => ListingModeration::STATUS_LABELS[$state] ?? (string) $state)
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Seller')
                    ->searchable(['name', 'phone'])
                    ->description(fn (Listing $record): ?string => $record->user?->phone)
                    ->url(fn (Listing $record): ?string => $record->user ? UserResource::getUrl('view', ['record' => $record->user]) : null),
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon(Heroicon::Star)
                    ->falseIcon(Heroicon::OutlinedStar)
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('views_count')->label('Views')->numeric()->sortable()->alignEnd()->toggleable(),
                TextColumn::make('favourites_count')->label('Favs')->numeric()->sortable()->alignEnd()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('chats_count')->label('Chats')->numeric()->sortable()->alignEnd()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reports_count')
                    ->label('Reports')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'danger' : 'gray')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),
                TextColumn::make('published_at')
                    ->label('Published')
                    ->since()
                    ->dateTimeTooltip('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('created_at')->label('Created')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expires_at')->label('Expires')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(ListingModeration::STATUS_LABELS)->multiple(),
                SelectFilter::make('category')->relationship('category', 'name_en')->searchable()->preload()->multiple(),
                SelectFilter::make('city')->relationship('city', 'name')->searchable()->preload()->multiple(),
                SelectFilter::make('condition')->options(ListingModeration::CONDITION_LABELS),
                TernaryFilter::make('is_featured')->label('Featured'),
                Filter::make('price')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('min')->label('Min price')->numeric()->prefix('₹'),
                            TextInput::make('max')->label('Max price')->numeric()->prefix('₹'),
                        ]),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['min'] ?? null, fn (Builder $query, $v) => $query->where('price', '>=', $v))
                        ->when($data['max'] ?? null, fn (Builder $query, $v) => $query->where('price', '<=', $v)))
                    ->indicateUsing(fn (array $data): ?string => filled($data['min'] ?? null) || filled($data['max'] ?? null)
                        ? 'Price: ₹'.($data['min'] ?: '0').' – '.($data['max'] ? '₹'.$data['max'] : 'any')
                        : null),
                Filter::make('created_at')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('from')->native(false),
                            DatePicker::make('until')->native(false),
                        ]),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $v) => $query->whereDate('created_at', '>=', $v))
                        ->when($data['until'] ?? null, fn (Builder $query, $v) => $query->whereDate('created_at', '<=', $v)))
                    ->indicateUsing(fn (array $data): ?string => filled($data['from'] ?? null) || filled($data['until'] ?? null)
                        ? 'Created '.($data['from'] ?? '…').' → '.($data['until'] ?? 'today')
                        : null),
                Filter::make('reported')
                    ->label('Has open reports')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereHas('reports', fn (Builder $query) => $query->whereIn('status', ['open', 'reviewing']))),
                TrashedFilter::make(),
            ])
            ->filtersFormColumns(2)
            ->persistFiltersInSession()
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    ListingModeration::approve(),
                    ListingModeration::reject(),
                    ListingModeration::feature(),
                    ListingModeration::unfeature(),
                    ListingModeration::block(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ListingModeration::bulkApprove(),
                    ListingModeration::bulkReject(),
                    ListingModeration::bulkFeature(),
                    ListingModeration::bulkBlock(),
                    CsvExport::bulkAction('listings', self::csvColumns()),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn (Listing $record): string => self::getUrl('view', ['record' => $record]))
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon(Heroicon::OutlinedRectangleStack)
            ->emptyStateHeading('No listings match')
            ->emptyStateDescription('Try clearing filters or switching tabs.');
    }

    /**
     * @return array<string, string|\Closure>
     */
    public static function csvColumns(): array
    {
        return [
            'ID' => 'id',
            'Title' => 'title',
            'Price (INR)' => 'price',
            'Status' => 'status',
            'Condition' => 'condition',
            'Category' => 'category.name_en',
            'City' => 'city.name',
            'Seller' => 'user.name',
            'Seller phone' => 'user.phone',
            'Featured' => 'is_featured',
            'Views' => 'views_count',
            'Favourites' => 'favourites_count',
            'Chats' => 'chats_count',
            'Published at' => 'published_at',
            'Created at' => 'created_at',
        ];
    }

    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
            ReportsRelationManager::class,
            ConversationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListListings::route('/'),
            'view' => ViewListing::route('/{record}'),
            'edit' => EditListing::route('/{record}/edit'),
        ];
    }
}
