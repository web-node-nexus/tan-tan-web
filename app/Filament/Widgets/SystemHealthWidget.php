<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemHealthWidget extends Widget
{
    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = ['md' => 6, 'xl' => 5];

    protected string $view = 'filament.widgets.system-health-widget';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        try {
            DB::connection()->getPdo();
            $dbOk = true;
        } catch (\Throwable) {
            $dbOk = false;
        }

        $pendingJobs = $dbOk && Schema::hasTable('jobs') ? DB::table('jobs')->count() : null;
        $failedJobs = $dbOk && Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : null;
        $storageLinked = is_link(public_path('storage')) || is_dir(public_path('storage'));
        $debug = (bool) config('app.debug');
        $production = app()->isProduction();

        return [
            'checks' => [
                [
                    'label' => 'Database',
                    'meta' => config('database.default').' · '.basename((string) config('database.connections.'.config('database.default').'.database')),
                    'state' => $dbOk ? 'ok' : 'down',
                    'value' => $dbOk ? 'Connected' : 'Unreachable',
                ],
                [
                    'label' => 'Queue',
                    'meta' => 'Driver: '.config('queue.default').($pendingJobs !== null ? ' · '.$pendingJobs.' waiting' : ''),
                    'state' => ($failedJobs ?? 0) > 0 ? 'warn' : 'ok',
                    'value' => ($failedJobs ?? 0) > 0 ? $failedJobs.' failed' : 'Healthy',
                ],
                [
                    'label' => 'Media storage',
                    'meta' => 'public/storage symlink',
                    'state' => $storageLinked ? 'ok' : 'warn',
                    'value' => $storageLinked ? 'Linked' : 'Run storage:link',
                ],
                [
                    'label' => 'Realtime chat',
                    'meta' => 'Broadcast: '.config('broadcasting.default'),
                    'state' => config('broadcasting.default') === 'reverb' ? 'ok' : 'warn',
                    'value' => config('broadcasting.default') === 'reverb' ? 'Reverb' : 'Not realtime',
                ],
                [
                    'label' => 'Debug mode',
                    'meta' => 'APP_ENV='.app()->environment(),
                    'state' => $debug && $production ? 'down' : ($debug ? 'warn' : 'ok'),
                    'value' => $debug ? 'On' : 'Off',
                ],
            ],
            'versions' => 'PHP '.PHP_VERSION.' · Laravel '.app()->version(),
        ];
    }
}
