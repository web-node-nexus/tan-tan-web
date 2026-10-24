<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return UserResource::canManageRoles();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] ??= bcrypt(Str::random(40));
        $data['referral_code'] = strtoupper(Str::random(8));

        return $data;
    }
}
