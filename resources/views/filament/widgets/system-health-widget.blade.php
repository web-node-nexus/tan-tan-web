<x-filament-widgets::widget>
    <x-filament::section
        heading="System health"
        :description="$versions"
        icon="heroicon-o-cpu-chip"
    >
        <ul class="tt-health">
            @foreach ($checks as $check)
                <li class="tt-health__row">
                    <span @class(['tt-health__dot', 'tt-health__dot--'.$check['state']])></span>
                    <div class="min-w-0 flex-1">
                        <p class="tt-health__label">{{ $check['label'] }}</p>
                        <p class="tt-health__meta">{{ $check['meta'] }}</p>
                    </div>
                    <span @class(['tt-health__value', 'tt-health__value--'.$check['state']])>{{ $check['value'] }}</span>
                </li>
            @endforeach
        </ul>
    </x-filament::section>
</x-filament-widgets::widget>
