<x-filament-widgets::widget>
    <div class="tt-admin-hero">
        <p class="tt-admin-hero__eyebrow">Tan Tan · Ops</p>
        <h2 class="tt-admin-hero__title">
            {{ $greeting }}, {{ $name }}
        </h2>
        <p class="tt-admin-hero__lead">
            {{ $lead }}
        </p>
        <div class="tt-admin-hero__meta">
            @foreach ($pills as $pill)
                <span class="tt-admin-pill">{{ $pill }}</span>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
