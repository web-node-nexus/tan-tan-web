@php
    $image = $image ?? null;
    $kicker = $kicker ?? 'tantan';
@endphp

<section class="relative overflow-hidden grain border-b border-line">
    <div class="absolute inset-0 mesh-hero" aria-hidden="true"></div>
    <div class="site-container relative grid items-center gap-6 py-8 sm:gap-8 sm:py-10 lg:grid-cols-12 lg:gap-8">
        <div class="{{ $image ? 'lg:col-span-7' : 'lg:col-span-9' }}">
            <p class="eyebrow reveal">{{ $kicker }}</p>
            <h1 class="display mt-2 max-w-3xl reveal reveal-delay-1">{{ $heading }}</h1>
            @isset($lead)
                <p class="lede mt-3 reveal reveal-delay-2">{{ $lead }}</p>
            @endisset
            @isset($actions)
                <div class="mt-5 flex flex-wrap gap-2.5 reveal reveal-delay-3">
                    {!! $actions !!}
                </div>
            @endisset
            @isset($meta)
                <div class="mt-4 flex flex-wrap gap-2 reveal reveal-delay-3">
                    {!! $meta !!}
                </div>
            @endisset
        </div>

        @if ($image)
            <div class="reveal reveal-delay-2 lg:col-span-5">
                <div class="media-frame aspect-[16/11] sm:aspect-[5/3] lg:aspect-[4/3]">
                    <img src="{{ asset($image) }}" alt="{{ $imageAlt ?? $heading }}" width="900" height="675" class="object-cover" />
                    <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-ink-900/40 via-transparent to-transparent"></div>
                    @isset($imageCaption)
                        <p class="absolute bottom-3 left-3 right-3 text-xs font-medium text-white/95 sm:text-sm">{{ $imageCaption }}</p>
                    @endisset
                </div>
            </div>
        @endif
    </div>
</section>
