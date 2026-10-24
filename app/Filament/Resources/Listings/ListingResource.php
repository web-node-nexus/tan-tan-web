<?php

namespace App\Filament\Resources\Listings;

use App\Filament\Resources\Listings\Pages\ListListings;
use App\Filament\Resources\Listings\Pages\EditListing;
use App\Models\Listing;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(120)->columnSpanFull(),
            Textarea::make('description')->required()->rows(5)->columnSpanFull(),
            TextInput::make('price')->numeric()->required(),
            Select::make('category_id')->relationship('category', 'name_en')->required()->searchable()->preload(),
            Select::make('city_id')->relationship('city', 'name')->searchable()->preload(),
            Select::make('condition')->options([
                'new' => 'New',
                'like_new' => 'Like new',
                'used' => 'Used',
                'for_parts' => 'For parts',
            ])->required(),
            Select::make('status')->options([
                'draft' => 'Draft',
                'pending' => 'Pending',
                'active' => 'Active',
                'sold' => 'Sold',
                'expired' => 'Expired',
                'rejected' => 'Rejected',
                'blocked' => 'Blocked',
            ])->required(),
            Toggle::make('is_featured'),
            TextInput::make('location_label')->maxLength(120),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->limit(40),
                TextColumn::make('price')->money('INR'),
                TextColumn::make('status')->badge(),
                TextColumn::make('city.name')->toggleable(),
                TextColumn::make('user.name')->label('Seller'),
                IconColumn::make('is_featured')->boolean(),
                TextColumn::make('published_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'active' => 'Active',
                    'pending' => 'Pending',
                    'sold' => 'Sold',
                    'blocked' => 'Blocked',
                ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListListings::route('/'),
            'edit' => EditListing::route('/{record}/edit'),
        ];
    }
}
