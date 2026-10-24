<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Support\CsvExport;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected ?string $subheading = 'Buyers, sellers and staff — verify, suspend and audit accounts.';

    protected function getHeaderActions(): array
    {
        return [
            CsvExport::tableAction('users', UserResource::csvColumns()),
            CreateAction::make()->label('New user')->visible(fn (): bool => UserResource::canManageRoles()),
        ];
    }

    public function getTabs(): array
    {
        $staff = fn (Builder $query): Builder => $query->whereHas('roles', fn (Builder $r) => $r->whereIn('name', UserResource::STAFF_ROLES));

        return [
            'all' => Tab::make('All')->badge(User::query()->count()),
            'customers' => Tab::make('Customers')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereDoesntHave('roles', fn (Builder $r) => $r->whereIn('name', UserResource::STAFF_ROLES))),
            'sellers' => Tab::make('Sellers')
                ->badge(User::query()->has('listings')->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->has('listings')),
            'verified' => Tab::make('Verified')
                ->badge(User::query()->where('is_verified', true)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_verified', true)),
            'staff' => Tab::make('Staff')
                ->badge($staff(User::query())->count())
                ->badgeColor('primary')
                ->modifyQueryUsing($staff),
            'suspended' => Tab::make('Suspended')
                ->badge(User::query()->whereNotNull('banned_at')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereNotNull('banned_at')),
        ];
    }
}
