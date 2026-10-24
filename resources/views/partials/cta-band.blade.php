<section class="relative overflow-hidden bg-ink-900 text-white">
    <div class="absolute -left-16 top-0 h-40 w-40 rounded-full bg-primary-500/30 blur-3xl" aria-hidden="true"></div>
    <div class="absolute -right-10 bottom-0 h-36 w-36 rounded-full bg-trust-500/25 blur-3xl" aria-hidden="true"></div>
    <div class="site-container relative flex flex-col items-start justify-between gap-5 py-8 sm:py-9 md:flex-row md:items-center">
        <div class="max-w-xl">
            <p class="text-base font-extrabold lowercase text-primary-500">tantan</p>
            <h2 class="mt-1.5 text-xl font-bold tracking-tight sm:text-2xl">{{ $heading }}</h2>
            @isset($text)
                <p class="mt-2 text-sm leading-relaxed text-white/65">{{ $text }}</p>
            @endisset
        </div>
        <div class="flex flex-wrap gap-2.5">
            <a href="{{ $primaryHref ?? route('site.download') }}" class="btn-primary">{{ $primaryLabel ?? 'Download free' }}</a>
            @isset($secondaryHref)
                <a href="{{ $secondaryHref }}" class="inline-flex min-h-11 items-center justify-center rounded-md border border-white/20 px-5 text-sm font-semibold text-white transition duration-300 hover:bg-white/10 sm:min-h-12">
                    {{ $secondaryLabel }}
                </a>
            @endisset
        </div>
    </div>
</section>
