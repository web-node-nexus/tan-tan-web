<?php

namespace App\Filament\Resources\Listings\Pages;

use App\Filament\Resources\Listings\ListingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditListing extends EditRecord
{
    protected static string $resource = ListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) !== $this->record->status) {
            $data['moderated_at'] = now();
        }

        if (! in_array($data['status'] ?? null, ['rejected', 'blocked'], true)) {
            $data['moderation_note'] = null;
        }

        if (! ($data['is_featured'] ?? false)) {
            $data['featured_until'] = null;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return ListingResource::getUrl('view', ['record' => $this->record]);
    }
}
