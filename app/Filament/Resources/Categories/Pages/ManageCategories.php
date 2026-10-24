<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCategories extends ManageRecords
{
    protected static string $resource = CategoryResource::class;

    protected ?string $subheading = 'Category tree shown on the app home and post-ad flow. Drag rows to reorder.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New category'),
        ];
    }
}
