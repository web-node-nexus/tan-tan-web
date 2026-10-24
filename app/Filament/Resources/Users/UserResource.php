<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\RelationManagers\ListingsRelationManager;
use App\Filament\Resources\Users\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\Users\RelationManagers\ReviewsRelationManager;
use App\Filament\Support\CsvExport;
use App\Filament\Support\UserModeration;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Users;

    protected static string|UnitEnum|null $navigationGroup = 'People';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public const STAFF_ROLES = ['Super Admin', 'Admin', 'Moderator', 'Support'];

    public static function canManageRoles(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(['Super Admin', 'Admin']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return array_filter([
            'Phone' => $record->phone,
            'Status' => $record->banned_at ? 'Suspended' : null,
        ]);
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
                    Section::make('Profile')
                        ->icon(Heroicon::OutlinedUser)
                        ->columns(2)
                        ->schema([
                            TextInput::make('name')->required()->maxLength(120),
                            TextInput::make('phone')
                                ->tel()
                                ->prefix('+91')
                                ->maxLength(20)
                                ->unique(ignoreRecord: true),
                            TextInput::make('email')->email()->required()->maxLength(190)->unique(ignoreRecord: true),
                            Select::make('city_id')->label('City')->relationship('city', 'name')->searchable()->preload(),
                            Textarea::make('bio')->rows(3)->maxLength(500)->columnSpanFull(),
                        ]),
                    Section::make('Preferences')
                        ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                        ->columns(3)
                        ->collapsible()
                        ->schema([
                            Select::make('locale')->options(['en' => 'English', 'hi' => 'हिन्दी'])->default('en')->native(false),
                            Select::make('theme')->options(['system' => 'System', 'light' => 'Light', 'dark' => 'Dark'])->default('system')->native(false),
                            Toggle::make('show_phone')->label('Show phone on ads')->inline(false),
                        ]),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    Section::make('Trust')
                        ->icon(Heroicon::OutlinedCheckBadge)
                        ->schema([
                            Toggle::make('is_verified')->label('Verified seller badge'),
                        ]),
                    Section::make('Admin access')
                        ->icon(Heroicon::OutlinedKey)
                        ->description('Staff roles unlock this panel.')
                        ->visible(fn (): bool => self::canManageRoles())
                        ->schema([
                            Select::make('roles')
                                ->relationship('roles', 'name')
                                ->multiple()
                                ->preload()
                                ->native(false),
                            TextInput::make('password')
                                ->password()
                                ->revealable()
                                ->minLength(8)
                                ->dehydrated(fn (?string $state): bool => filled($state))
                                ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Leave blank to keep the current password.' : null),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 2, 'md' => 3, 'xl' => 6])->schema([
                            TextEntry::make('listings_count')->label('Listings')
                                ->state(fn (User $record): int => $record->listings()->count())
                                ->numeric()->weight(FontWeight::Bold)->size('lg'),
                            TextEntry::make('active_listings')->label('Live ads')
                                ->state(fn (User $record): int => $record->listings()->where('status', 'active')->count())
                                ->numeric()->weight(FontWeight::Bold)->size('lg')->color('success'),
                            TextEntry::make('sold_listings')->label('Sold')
                                ->state(fn (User $record): int => $record->listings()->where('status', 'sold')->count())
                                ->numeric()->weight(FontWeight::Bold)->size('lg'),
                            TextEntry::make('rating_avg')->label('Rating')
                                ->formatStateUsing(fn ($state, User $record): string => number_format((float) $state, 1).' ★ ('.$record->rating_count.')')
                                ->weight(FontWeight::Bold)->size('lg')->color('warning'),
                            TextEntry::make('reports_filed')->label('Reports filed')
                                ->state(fn (User $record): int => $record->reportsFiled()->count())
                                ->numeric()->weight(FontWeight::Bold)->size('lg'),
                            TextEntry::make('spent')->label('Total spent')
                                ->state(fn (User $record): float => (float) $record->payments()->where('status', 'paid')->sum('amount'))
                                ->money('INR', locale: 'en_IN', decimalPlaces: 0)->weight(FontWeight::Bold)->size('lg'),
                        ]),
                    ]),

                Group::make([
                    Section::make('Profile')
                        ->icon(Heroicon::OutlinedUser)
                        ->columns(2)
                        ->schema([
                            TextEntry::make('name')->weight(FontWeight::SemiBold),
                            TextEntry::make('phone')->copyable()->placeholder('—'),
                            TextEntry::make('email')->copyable(),
                            TextEntry::make('city.name')->label('City')->placeholder('—'),
                            TextEntry::make('bio')->columnSpanFull()->placeholder('No bio'),
                        ]),
                    Section::make('Suspension')
                        ->icon(Heroicon::OutlinedNoSymbol)
                        ->visible(fn (User $record): bool => $record->isBanned())
                        ->schema([
                            TextEntry::make('banned_at')->label('Suspended')->dateTime('d M Y, H:i')->color('danger'),
                            TextEntry::make('ban_reason')->label('Reason'),
                        ]),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    Section::make('Account')
                        ->icon(Heroicon::OutlinedIdentification)
                        ->schema([
                            TextEntry::make('account_status')->label('Status')
                                ->state(fn (User $record): string => match (true) {
                                    $record->trashed() => 'Deleted',
                                    $record->isBanned() => 'Suspended',
                                    default => 'Active',
                                })
                                ->badge()
                                ->color(fn (string $state): string => $state === 'Active' ? 'success' : 'danger'),
                            IconEntry::make('is_verified')->label('Verified')->boolean(),
                            TextEntry::make('roles.name')->label('Roles')->badge()->placeholder('Customer'),
                            TextEntry::make('referral_code')->label('Referral code')->copyable()->placeholder('—'),
                            TextEntry::make('created_at')->label('Joined')->dateTime('d M Y'),
                            TextEntry::make('last_active_at')->label('Last active')->since()->placeholder('Never'),
                            TextEntry::make('locale')->formatStateUsing(fn (?string $state): string => $state === 'hi' ? 'हिन्दी' : 'English'),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['roles', 'city'])->withCount('listings'))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn (User $record): ?string => $record->email && ! str_ends_with($record->email, '@phone.tantan.local') ? $record->email : null)
                    ->icon(fn (User $record): ?Heroicon => $record->is_verified ? Heroicon::CheckBadge : null)
                    ->iconColor('success')
                    ->iconPosition('after'),
                TextColumn::make('phone')->searchable()->copyable()->placeholder('—'),
                TextColumn::make('roles.name')->label('Role')->badge()->color('primary')->placeholder('Customer'),
                TextColumn::make('status')
                    ->state(fn (User $record): string => match (true) {
                        $record->trashed() => 'Deleted',
                        $record->isBanned() => 'Suspended',
                        default => 'Active',
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Active' ? 'success' : 'danger'),
                TextColumn::make('listings_count')->label('Ads')->numeric()->sortable()->alignCenter(),
                TextColumn::make('rating_avg')
                    ->label('Rating')
                    ->formatStateUsing(fn ($state, User $record): string => $record->rating_count > 0 ? number_format((float) $state, 1).' ★' : '—')
                    ->color('warning')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('city.name')->label('City')->toggleable()->placeholder('—'),
                IconColumn::make('is_verified')->label('Verified')->boolean()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_active_at')->label('Last active')->since()->sortable()->placeholder('Never'),
                TextColumn::make('created_at')->label('Joined')->date('d M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('roles')->relationship('roles', 'name')->multiple()->preload(),
                TernaryFilter::make('is_verified')->label('Verified'),
                TernaryFilter::make('banned')
                    ->label('Suspended')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('banned_at'),
                        false: fn (Builder $query) => $query->whereNull('banned_at'),
                    ),
                SelectFilter::make('city')->relationship('city', 'name')->searchable()->preload(),
                Filter::make('has_listings')->label('Has posted ads')->toggle()
                    ->query(fn (Builder $query): Builder => $query->has('listings')),
                Filter::make('created_at')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('from')->label('Joined from')->native(false),
                            DatePicker::make('until')->label('Joined until')->native(false),
                        ]),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $v) => $query->whereDate('created_at', '>=', $v))
                        ->when($data['until'] ?? null, fn (Builder $query, $v) => $query->whereDate('created_at', '<=', $v))),
                TrashedFilter::make(),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    UserModeration::toggleVerified(),
                    UserModeration::ban(),
                    UserModeration::unban(),
                    DeleteAction::make()->hidden(fn (User $record): bool => $record->id === auth()->id()),
                    RestoreAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    UserModeration::bulkVerify(),
                    UserModeration::bulkBan(),
                    CsvExport::bulkAction('users', self::csvColumns()),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn (User $record): string => self::getUrl('view', ['record' => $record]))
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->defaultSort('created_at', 'desc');
    }

    /**
     * @return array<string, string|\Closure>
     */
    public static function csvColumns(): array
    {
        return [
            'ID' => 'id',
            'Name' => 'name',
            'Phone' => 'phone',
            'Email' => 'email',
            'City' => 'city.name',
            'Verified' => 'is_verified',
            'Roles' => fn (User $u): string => $u->roles->pluck('name')->join(', '),
            'Rating' => 'rating_avg',
            'Suspended at' => 'banned_at',
            'Last active' => 'last_active_at',
            'Joined' => 'created_at',
        ];
    }

    public static function getRelations(): array
    {
        return [
            ListingsRelationManager::class,
            ReviewsRelationManager::class,
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
