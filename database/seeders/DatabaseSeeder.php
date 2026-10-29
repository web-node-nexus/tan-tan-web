<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Super Admin', 'Admin', 'Moderator', 'Support'] as $role) {
            Role::findOrCreate($role);
        }

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@tantan.app'],
            [
                'name' => 'Tan Tan Admin',
                'password' => Hash::make('password'),
                'phone' => '9999999999',
                'locale' => 'en',
                'theme' => 'system',
                'referral_code' => 'ADMIN001',
            ],
        );
        if (! $admin->hasRole('Super Admin')) {
            $admin->assignRole('Super Admin');
        }

        $this->call([
            MarketplaceSeeder::class,
            AreaSeeder::class,
            CatalogSeeder::class,
            AdvancedMarketplaceSeeder::class,
        ]);
    }
}
