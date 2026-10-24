<?php

namespace App\Filament\Resources\Cities;

use App\Filament\Resources\Cities\Pages\ManageCities;
use App\Models\City;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')->required()->maxLength(120),
                Select::make('state_id')->label('State')->relationship('state', 'name')->searchable()->preload()->required()
                    ->createOptionForm([
                        TextInput::make('name')->required()->maxLength(120),
                        TextInput::make('code')->required()->maxLength(8)->unique('states', 'code'),
                    ]),
                TextInput::make('latitude')->numeric()->minValue(-90)->maxValue(90),
                TextInput::make('longitude')->numeric()->minValue(-180)->maxValue(180),
                Toggle::make('is_active')->label('Available in app')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('state')->withCount([
                'listings as active_listings_count' => fn (Builder $l) => $l->where('status', 'active'),
            ]))
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight(FontWeight::SemiBold),
                TextColumn::make('state.name')->label('State')->sortable()->badge()->color('gray'),
                TextColumn::make('active_listings_count')->label('Live ads')->numeric()->sortable()->alignCenter(),
                TextColumn::make('coordinates')
                    ->state(fn (City $record): ?string => $record->latitude !== null ? number_format($record->latitude, 4).', '.number_format($record->longitude, 4) : null)
                    ->placeholder('—')
                    ->color('gray')
                    ->toggleable(),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->filters([
                SelectFilter::make('state')->relationship('state', 'name')->searchable()->preload()->multiple(),
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')->label('Activate')->icon(Heroicon::OutlinedCheck)
                        ->action(fn (Collection $records) => City::query()->whereKey($records->modelKeys())->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')->label('Deactivate')->icon(Heroicon::OutlinedXMark)->color('gray')
                        ->action(fn (Collection $records) => City::query()->whereKey($records->modelKeys())->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('name')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCities::route('/'),
        ];
    }
}
