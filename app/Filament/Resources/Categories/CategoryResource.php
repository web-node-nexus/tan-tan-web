<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name_en';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Names')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name_en')
                            ->label('Name (English)')
                            ->required()
                            ->maxLength(80)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set, string $operation): void {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('name_hi')->label('नाम (हिन्दी)')->required()->maxLength(80),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(80)->alphaDash(),
                        Select::make('kind')
                            ->label('Listing type')
                            ->options([
                                'product' => 'Product',
                                'job' => 'Job',
                                'service' => 'Local service',
                            ])
                            ->required()
                            ->default('product'),
                        Select::make('parent_id')
                            ->label('Parent category')
                            ->relationship('parent', 'name_en', fn (Builder $query, ?Category $record) => $query->whereNull('parent_id')->when($record, fn ($query) => $query->whereKeyNot($record->id)))
                            ->searchable()
                            ->preload()
                            ->placeholder('Top level'),
                    ]),
                Section::make('Appearance')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('icon')
                            ->label('Phosphor icon')
                            ->helperText('e.g. DeviceMobile, Car, House, Couch')
                            ->maxLength(64),
                        ColorPicker::make('tint')->label('Tile tint')->default('#FFE9DF'),
                        TextInput::make('sort_order')->numeric()->default(0),
                        Toggle::make('is_active')->label('Visible in app')->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('parent')->withCount([
                'listings',
                'listings as active_listings_count' => fn (Builder $l) => $l->where('status', 'active'),
                'children',
            ]))
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ColorColumn::make('tint')->label(''),
                TextColumn::make('name_en')
                    ->label('Category')
                    ->searchable(['name_en', 'name_hi', 'slug'])
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn (Category $record): string => $record->name_hi),
                TextColumn::make('parent.name_en')->label('Parent')->badge()->color('gray')->placeholder('Top level'),
                TextColumn::make('kind')->badge()->color(fn (string $state): string => match ($state) {
                    'job' => 'info',
                    'service' => 'warning',
                    default => 'gray',
                }),
                TextColumn::make('slug')->color('gray')->toggleable(),
                TextColumn::make('icon')->badge()->color('gray')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('active_listings_count')->label('Live ads')->numeric()->sortable()->alignCenter(),
                TextColumn::make('listings_count')->label('Total ads')->numeric()->sortable()->alignCenter()->toggleable(),
                TextColumn::make('children_count')->label('Sub-cats')->numeric()->alignCenter()->toggleable(),
                ToggleColumn::make('is_active')->label('Visible'),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Visible'),
                SelectFilter::make('level')
                    ->options(['top' => 'Top level only', 'sub' => 'Sub-categories only'])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'top' => $query->whereNull('parent_id'),
                        'sub' => $query->whereNotNull('parent_id'),
                        default => $query,
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->disabled(fn (Category $record): bool => $record->listings_count > 0)
                    ->tooltip(fn (Category $record): ?string => $record->listings_count > 0 ? 'Move or delete its listings first' : null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('show')->label('Show in app')->icon(Heroicon::OutlinedEye)
                        ->action(fn (Collection $records) => Category::query()->whereKey($records->modelKeys())->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('hide')->label('Hide from app')->icon(Heroicon::OutlinedEyeSlash)->color('gray')
                        ->action(fn (Collection $records) => Category::query()->whereKey($records->modelKeys())->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedTag)
            ->emptyStateHeading('No categories');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
        ];
    }
}
