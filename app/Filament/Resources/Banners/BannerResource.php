<?php

namespace App\Filament\Resources\Banners;

use App\Filament\Resources\Banners\Pages\ManageBanners;
use App\Models\Banner;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Home banners';

    public const APP_ROUTES = [
        '/post-ad' => 'Post an ad',
        '/packages' => 'Boost packages',
        '/safety' => 'Safety tips',
        '/search' => 'Search',
        '/sell' => 'Sell tab',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                FileUpload::make('image_path')
                    ->label('Banner image')
                    ->helperText('Recommended 1200×500, JPG/WebP under 400 KB.')
                    ->image()
                    ->imageEditor()
                    ->imageAspectRatio('12:5')
                    ->disk('public')
                    ->directory('banners')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('title')->required()->maxLength(120),
                TextInput::make('subtitle')->maxLength(200),
                Section::make('Call to action')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('cta_label')->label('Button label')->maxLength(40),
                        TextInput::make('cta_href')
                            ->label('Opens')
                            ->datalist(array_keys(self::APP_ROUTES))
                            ->helperText('App route, e.g. '.implode(', ', array_keys(self::APP_ROUTES)))
                            ->maxLength(120),
                    ]),
                TextInput::make('sort_order')->numeric()->default(0),
                Toggle::make('is_active')->label('Live in app')->default(true)->inline(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Preview')
                    ->state(fn (Banner $record): string => $record->imageUrl())
                    ->imageHeight(56)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover aspect-[12/5]']),
                TextColumn::make('title')->searchable()->weight(FontWeight::SemiBold)
                    ->description(fn (Banner $record): ?string => $record->subtitle),
                TextColumn::make('cta_label')->label('CTA')->badge()->color('primary')->placeholder('—')
                    ->description(fn (Banner $record): ?string => $record->cta_href),
                TextColumn::make('updated_at')->label('Updated')->since()->toggleable(),
                ToggleColumn::make('is_active')->label('Live'),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Live'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedPhoto)
            ->emptyStateHeading('No banners')
            ->emptyStateDescription('Add a banner to show in the app home carousel.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBanners::route('/'),
        ];
    }
}
