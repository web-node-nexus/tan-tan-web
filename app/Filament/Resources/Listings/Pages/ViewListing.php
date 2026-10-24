<?php

namespace App\Filament\Resources\Listings\Pages;

use App\Filament\Resources\Listings\ListingResource;
use App\Filament\Support\ListingModeration;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewListing extends ViewRecord
{
    protected static string $resource = ListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ListingModeration::approve(),
            ListingModeration::reject(),
            ListingModeration::feature(),
            ListingModeration::unfeature(),
            EditAction::make(),
            ActionGroup::make([
                ListingModeration::block(),
                DeleteAction::make(),
            ]),
        ];
    }
}
