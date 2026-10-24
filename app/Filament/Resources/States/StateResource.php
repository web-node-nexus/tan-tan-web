<?php

namespace App\Filament\Resources\States;

use App\Filament\Resources\States\Pages\ManageStates;
use App\Models\State;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class StateResource extends Resource
{
    protected static ?string $model = State::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('code')->required()->maxLength(8)->unique(ignoreRecord: true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('cities'))
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight(FontWeight::SemiBold),
                TextColumn::make('code')->badge()->color('gray'),
                TextColumn::make('cities_count')->label('Cities')->numeric()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->disabled(fn (State $record): bool => $record->cities_count > 0)
                    ->tooltip(fn (State $record): ?string => $record->cities_count > 0 ? 'Remove its cities first' : null),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStates::route('/'),
        ];
    }
}
