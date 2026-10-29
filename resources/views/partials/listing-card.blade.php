@php
    $cover = $listing->images->firstWhere('is_cover', true) ?? $listing->images->first();
    $kind = $listing->category?->kind ?? 'product';
@endphp
<a href="{{ route('site.ads.show', $listing) }}" class="group overflow-hidden rounded-lg bg-surface ring-1 ring-line transition duration-300 hover:-translate-y-0.5 hover:shadow-[var(--shadow-e2)]">
    <div class="aspect-square overflow-hidden bg-canvas">
        @if ($cover)
            <img src="{{ $cover->url() }}" alt="" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy">
        @endif
    </div>
    <div class="space-y-1 p-3">
        <p class="text-base font-extrabold text-ink-900">{{ $listing->priceLabel() }}</p>
        <p class="line-clamp-2 text-sm font-medium text-ink-700">{{ $listing->title }}</p>
        <p class="text-xs text-ink-500">{{ $listing->city?->name ?? $listing->location_label }}</p>
        @if ($kind !== 'product')
            <p class="text-[11px] font-bold uppercase tracking-wider text-primary-500">{{ $kind === 'job' ? 'Apply' : 'Book a visit' }}</p>
        @endif
    </div>
</a>
