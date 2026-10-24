<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;
use Filament\Facades\Filament;

class ListingPolicy
{
    /**
     * Staff moderate any listing, but only inside the admin panel — API rules stay owner-only.
     */
    public function before(?User $user, string $ability): ?bool
    {
        if (! $user || ! Filament::isServing()) {
            return null;
        }

        if (in_array($ability, ['viewAny', 'view'], true)) {
            return $user->hasAnyRole(['Super Admin', 'Admin', 'Moderator', 'Support']) ?: null;
        }

        return $user->hasAnyRole(['Super Admin', 'Admin', 'Moderator']) ?: null;
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Listing $listing): bool
    {
        if ($listing->status === 'active') {
            return true;
        }

        return $user !== null && $user->id === $listing->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Listing $listing): bool
    {
        return $user->id === $listing->user_id;
    }

    public function delete(User $user, Listing $listing): bool
    {
        return $user->id === $listing->user_id;
    }
}
