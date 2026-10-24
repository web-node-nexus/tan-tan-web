<?php

namespace App\Filament\Resources\Banners\Pages;

use App\Filament\Resources\Banners\BannerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBanners extends ManageRecords
{
    protected static string $resource = BannerResource::class;

    protected ?string $subheading = 'Home carousel in the mobile app. Drag rows to change the order.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New banner'),
        ];
    }
}
