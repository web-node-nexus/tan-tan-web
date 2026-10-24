<x-filament-widgets::widget>
    <div class="tt-admin-panel">
        <p class="tt-admin-panel__title">Quick actions</p>
        <p class="tt-admin-panel__sub">Jump into the surfaces that matter while resources come online.</p>
        <div class="tt-admin-grid tt-admin-grid-2 mt-4" style="margin-top: 1rem;">
            @foreach ($actions as $action)
                <a href="{{ $action['url'] }}" class="tt-admin-card" @if (($action['external'] ?? false)) target="_blank" rel="noopener" @endif>
                    <span class="tt-admin-card__icon">
                        <x-filament::icon :icon="$action['icon']" class="h-5 w-5" />
                    </span>
                    <p class="tt-admin-card__title">{{ $action['title'] }}</p>
                    <p class="tt-admin-card__text">{{ $action['text'] }}</p>
                    <span class="tt-admin-card__link">{{ $action['cta'] }} →</span>
                </a>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
