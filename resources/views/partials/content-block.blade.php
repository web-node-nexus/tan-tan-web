@props(['title' => null, 'eyebrow' => null])
<section {{ $attributes->merge(['class' => 'section border-b border-line']) }}>
    <div class="site-container">
        @if ($eyebrow)
            <p class="eyebrow">{{ $eyebrow }}</p>
        @endif
        @if ($title)
            <h2 class="section-title {{ $eyebrow ? 'mt-1' : '' }}">{{ $title }}</h2>
        @endif
        <div class="{{ $title || $eyebrow ? 'mt-4' : '' }}">
            {{ $slot }}
        </div>
    </div>
</section>
