<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class RoadmapWidget extends Widget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.roadmap-widget';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'phases' => [
                [
                    'kicker' => 'Phase 1',
                    'title' => 'Foundation',
                    'text' => 'Auth shell, design tokens, public site, this branded admin.',
                    'accent' => '#0F9D8A',
                ],
                [
                    'kicker' => 'Phase 2',
                    'title' => 'Listings & search',
                    'text' => 'Categories, media, filters, My Ads moderation queues.',
                    'accent' => '#FF5A1F',
                ],
                [
                    'kicker' => 'Phase 3',
                    'title' => 'Chat & trust',
                    'text' => 'Realtime threads, reports, blocks, verified badges.',
                    'accent' => '#F2B01E',
                ],
                [
                    'kicker' => 'Phase 4',
                    'title' => 'Boosts & growth',
                    'text' => 'Razorpay packages, invoices, referrals, campaigns.',
                    'accent' => '#E64A12',
                ],
            ],
        ];
    }
}
