<x-filament-widgets::widget>
    <div class="tt-admin-panel">
        <p class="tt-admin-panel__title">Product roadmap in admin</p>
        <p class="tt-admin-panel__sub">What this control room will grow into — already themed for Day 1.</p>
        <div class="tt-admin-grid tt-admin-grid-4 mt-4" style="margin-top: 1rem;">
            @foreach ($phases as $phase)
                <div class="tt-admin-card">
                    <p class="text-[0.65rem] font-bold uppercase tracking-[0.14em]" style="color: {{ $phase['accent'] }};">{{ $phase['kicker'] }}</p>
                    <p class="tt-admin-card__title">{{ $phase['title'] }}</p>
                    <p class="tt-admin-card__text">{{ $phase['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
