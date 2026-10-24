<?php

namespace App\Filament\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected string $view = 'filament.widgets.quick-actions-widget';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'actions' => [
                [
                    'title' => 'Public site',
                    'text' => 'Preview the informative marketing site buyers and sellers already see.',
                    'cta' => 'Open site',
                    'url' => url('/'),
                    'icon' => Heroicon::OutlinedGlobeAlt,
                    'external' => true,
                ],
                [
                    'title' => 'API health',
                    'text' => 'Hit the health endpoint — envelope + uptime for mobile clients.',
                    'cta' => 'Check /api/health',
                    'url' => url('/api/health'),
                    'icon' => Heroicon::OutlinedHeart,
                    'external' => true,
                ],
                [
                    'title' => 'Safety guide',
                    'text' => 'Same copy moderators should know when reviewing scam reports.',
                    'cta' => 'Read safety tips',
                    'url' => url('/safety-tips'),
                    'icon' => Heroicon::OutlinedShieldCheck,
                    'external' => true,
                ],
                [
                    'title' => 'FAQ knowledge base',
                    'text' => '80+ long answers — useful when Support answers tickets.',
                    'cta' => 'Open FAQ',
                    'url' => url('/faq'),
                    'icon' => Heroicon::OutlinedQuestionMarkCircle,
                    'external' => true,
                ],
            ],
        ];
    }
}
