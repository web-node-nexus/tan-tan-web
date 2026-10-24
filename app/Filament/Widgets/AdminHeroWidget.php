<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class AdminHeroWidget extends Widget
{
    protected static ?int $sort = -3;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.admin-hero-widget';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $hour = (int) now()->format('G');
        $greeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        $user = auth()->user();

        return [
            'greeting' => $greeting,
            'name' => $user?->name ?? 'Operator',
            'lead' => 'Your branded control room for Tan Tan — listings, reports, boosts and trust tools will land here as phases ship. For now, enjoy the ops shell tuned to DESIGN.md.',
            'pills' => [
                Carbon::now()->timezone(config('app.timezone'))->format('D, j M · H:i'),
                'Phase 1 foundation',
                'EN + हिन्दी product',
                'Primary #FF5A1F',
            ],
        ];
    }
}
