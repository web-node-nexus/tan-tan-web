<?php

namespace App\Filament\Resources\Packages;

use App\Filament\Resources\Packages\Pages\ManagePackages;
use App\Models\ListingPromotion;
use App\Models\Package;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static string|UnitEnum|null $navigationGroup = 'Monetization';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Boost packages';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Pricing')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('code')->required()->maxLength(40)->unique(ignoreRecord: true)->alphaDash(),
                        TextInput::make('price')->numeric()->prefix('₹')->required()->minValue(0),
                        TextInput::make('duration_days')->label('Duration')->numeric()->suffix('days')->required()->minValue(1)->default(7),
                    ]),
                Tabs::make('Content')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('English')->schema([
                            TextInput::make('name_en')->label('Name')->required()->maxLength(80),
                            Textarea::make('description_en')->label('Description')->rows(3),
                        ]),
                        Tab::make('हिन्दी')->schema([
                            TextInput::make('name_hi')->label('नाम')->required()->maxLength(80),
                            Textarea::make('description_hi')->label('विवरण')->rows(3),
                        ]),
                    ]),
                TagsInput::make('benefits')
                    ->placeholder('Add a benefit and press Enter')
                    ->columnSpanFull(),
                Section::make('Display')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('is_active')->label('Active')->default(true),
                        Toggle::make('is_featured_boost')->label('Features the ad')->default(true),
                        Toggle::make('is_best_value')->label('Best value badge'),
                        TextInput::make('sort_order')->numeric()->default(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name_en')->label('Package')->weight(FontWeight::SemiBold)->searchable()
                    ->description(fn (Package $record): string => $record->name_hi),
                TextColumn::make('code')->badge()->color('gray'),
                TextColumn::make('price')->money('INR', locale: 'en_IN', decimalPlaces: 0)->weight(FontWeight::Bold)->sortable(),
                TextColumn::make('duration_days')->label('Duration')->suffix(' days')->sortable(),
                TextColumn::make('benefits')->badge()->color('gray')->limitList(3)->expandableLimitedList(),
                TextColumn::make('promotions_sold')
                    ->label('Sold')
                    ->state(fn (Package $record): int => ListingPromotion::query()->where('package_id', $record->id)->count())
                    ->numeric(),
                IconColumn::make('is_best_value')->label('Best value')->boolean()->trueIcon(Heroicon::Trophy)->trueColor('warning'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
                ReplicateAction::make()
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['code'] = $data['code'].'-copy';
                        $data['is_active'] = false;

                        return $data;
                    }),
                DeleteAction::make(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedGift)
            ->emptyStateHeading('No packages');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePackages::route('/'),
        ];
    }
}
