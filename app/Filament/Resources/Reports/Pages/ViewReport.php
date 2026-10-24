<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Resources\Reports\ReportResource;
use App\Filament\Support\ReportModeration;
use Filament\Resources\Pages\ViewRecord;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ReportModeration::startReview(),
            ReportModeration::dismiss(),
            ReportModeration::resolve(),
        ];
    }
}
