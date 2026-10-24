<x-filament-widgets::widget>
    <div class="tt-admin-panel h-full">
        <p class="tt-admin-panel__title">Ops pulse</p>
        <p class="tt-admin-panel__sub">Foundation health while marketplace modules land.</p>
        <div class="tt-admin-rail">
            @foreach ($items as $item)
                <div class="tt-admin-rail-item">
                    <span class="tt-admin-rail-item__dot" style="background: {{ $item['color'] }}; box-shadow: 0 0 0 4px {{ $item['glow'] }};"></span>
                    <div class="min-w-0 flex-1">
                        <p class="tt-admin-rail-item__label">{{ $item['label'] }}</p>
                        <p class="tt-admin-rail-item__meta">{{ $item['meta'] }}</p>
                    </div>
                    <span class="text-xs font-bold" style="color: {{ $item['color'] }};">{{ $item['status'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
