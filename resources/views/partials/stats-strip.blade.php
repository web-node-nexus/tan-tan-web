@php
    $items = $items ?? [
        ['50+', 'Cities covered'],
        ['25+', 'Categories'],
        ['Free', 'Ad posting'],
        ['2', 'Languages'],
    ];
@endphp
<div class="stats-strip">
    @foreach ($items as $item)
        <div>
            <p class="text-xl font-extrabold tabular-nums text-ink-900 sm:text-2xl">{{ $item[0] }}</p>
            <p class="mt-0.5 text-[11px] font-medium text-ink-500 sm:text-xs">{{ $item[1] }}</p>
        </div>
    @endforeach
</div>
