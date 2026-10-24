<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OpsPulseWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected string $view = 'filament.widgets.ops-pulse-widget';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $dbOk = false;
        try {
            DB::connection()->getPdo();
            $dbOk = true;
        } catch (\Throwable) {
            $dbOk = false;
        }

        $rolesReady = Schema::hasTable('roles');
        $mediaReady = Schema::hasTable('media');
        $cacheDriver = config('cache.default', 'file');

        return [
            'items' => [
                [
                    'label' => 'Database',
                    'meta' => $dbOk ? 'Connection healthy' : 'Check .env DB_*',
                    'status' => $dbOk ? 'OK' : 'Down',
                    'color' => $dbOk ? '#0F9D8A' : '#E5484D',
                    'glow' => $dbOk ? 'rgba(15,157,138,0.18)' : 'rgba(229,72,77,0.18)',
                ],
                [
                    'label' => 'Roles & permissions',
                    'meta' => $rolesReady ? 'Spatie tables present' : 'Run migrations / seeders',
                    'status' => $rolesReady ? 'Ready' : 'Todo',
                    'color' => $rolesReady ? '#0F9D8A' : '#F5A524',
                    'glow' => $rolesReady ? 'rgba(15,157,138,0.18)' : 'rgba(245,165,36,0.18)',
                ],
                [
                    'label' => 'Media library',
                    'meta' => $mediaReady ? 'Listing images pipeline ready' : 'media table missing',
                    'status' => $mediaReady ? 'Ready' : 'Todo',
                    'color' => $mediaReady ? '#FF5A1F' : '#F5A524',
                    'glow' => $mediaReady ? 'rgba(255,90,31,0.18)' : 'rgba(245,165,36,0.18)',
                ],
                [
                    'label' => 'Cache driver',
                    'meta' => 'Using '.$cacheDriver,
                    'status' => strtoupper((string) $cacheDriver),
                    'color' => '#F2B01E',
                    'glow' => 'rgba(242,176,30,0.18)',
                ],
            ],
        ];
    }
}
