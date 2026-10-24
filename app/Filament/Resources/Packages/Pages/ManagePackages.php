<?php

namespace App\Filament\Resources\Packages\Pages;

use App\Filament\Resources\Packages\PackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePackages extends ManageRecords
{
    protected static string $resource = PackageResource::class;

    protected ?string $subheading = 'Pricing cards shown on the Boost screen in the app. Drag to reorder.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New package'),
        ];
    }
}
