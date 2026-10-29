<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $description ?? 'Tan Tan — buy, sell and rent near you.' }}">
    <meta property="og:title" content="{{ ($title ?? 'Tan Tan') }} · tantan">
    <meta property="og:description" content="{{ $description ?? 'Buy, sell and rent near you.' }}">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#0B1F3A">
    <meta property="og:image" content="{{ asset('images/brand/og.jpg') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/brand/logo-320.png') }}">
    <title>{{ isset($title) ? $title.' · TANTAN' : 'TANTAN — Buy · Sell · Discover' }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="flex min-h-screen flex-col"
    x-data="{
        navOpen: false,
        scrolled: false,
        init() {
            const onScroll = () => { this.scrolled = window.scrollY > 8 };
            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });
        }
    }"
>
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-primary-500 focus:px-4 focus:py-2 focus:text-white">Skip to content</a>

    <header
        class="sticky top-0 z-40 transition duration-300"
        :class="scrolled ? 'border-b border-line/80 bg-canvas/85 shadow-[var(--shadow-e1)] backdrop-blur-xl' : 'border-b border-transparent bg-canvas/60 backdrop-blur-md'"
    >
        <div class="site-container flex h-[4.25rem] items-center justify-between">
            <a href="{{ route('site.home') }}" class="inline-flex items-center gap-2.5 transition duration-300 hover:opacity-90" aria-label="TANTAN home">
                <img
                    src="{{ asset('images/brand/logo-320.png') }}"
                    alt="TANTAN"
                    width="40"
                    height="40"
                    class="h-10 w-10 object-contain"
                />
                <span class="flex flex-col leading-none">
                    <span class="text-[1.05rem] font-extrabold tracking-[0.04em] text-[#0B1F3A]">TANTAN</span>
                    <span class="mt-0.5 text-[0.58rem] font-semibold uppercase tracking-[0.18em] text-ink-400">Buy · Sell · Discover</span>
                </span>
            </a>

            <nav class="hidden items-center gap-1 text-sm font-medium text-ink-500 xl:flex" aria-label="Primary">
                @foreach ([
                    ['Browse', 'site.browse'],
                    ['Post ad', 'site.sell'],
                    ['About', 'site.about'],
                    ['How it works', 'site.how'],
                    ['Categories', 'site.categories'],
                    ['Safety', 'site.safety'],
                    ['Trust', 'site.trust'],
                    ['Work', 'site.work'],
                    ['Packages', 'site.packages'],
                    ['FAQ', 'site.faq'],
                ] as [$label, $route])
                    <a
                        href="{{ route($route) }}"
                        @class([
                            'rounded-full px-3 py-2 transition duration-300 hover:bg-primary-50 hover:text-ink-900',
                            'bg-primary-50 text-primary-600' => request()->routeIs($route),
                        ])
                    >{{ $label }}</a>
                @endforeach
                <a href="{{ route('site.download') }}" class="ml-2 inline-flex h-11 items-center rounded-md bg-primary-500 px-4 font-semibold text-white shadow-[var(--shadow-e1)] transition duration-300 hover:bg-primary-600">
                    Get the app
                </a>
            </nav>

            <button
                type="button"
                class="inline-flex h-11 w-11 items-center justify-center rounded-md border border-line bg-surface text-ink-700 shadow-[var(--shadow-e1)] transition duration-300 xl:hidden"
                @click="navOpen = !navOpen"
                :aria-expanded="navOpen.toString()"
                aria-label="Toggle menu"
            >
                <svg x-show="!navOpen" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg x-show="navOpen" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div x-show="navOpen" x-cloak x-transition class="border-t border-line bg-surface/95 backdrop-blur-xl xl:hidden">
            <nav class="site-container flex flex-col gap-1 py-3 text-sm font-medium text-ink-700" aria-label="Mobile">
                @foreach ([
                    ['Browse', 'site.browse'],
                    ['Post ad', 'site.sell'],
                    ['About', 'site.about'],
                    ['How it works', 'site.how'],
                    ['Categories', 'site.categories'],
                    ['For buyers', 'site.buyers'],
                    ['For sellers', 'site.sellers'],
                    ['Safety', 'site.safety'],
                    ['Trust', 'site.trust'],
                    ['Work', 'site.work'],
                    ['Packages', 'site.packages'],
                    ['FAQ', 'site.faq'],
                    ['Contact', 'site.contact'],
                    ['Download', 'site.download'],
                ] as [$label, $route])
                    <a href="{{ route($route) }}" class="rounded-md px-3 py-3 transition duration-300 hover:bg-canvas" @click="navOpen = false">{{ $label }}</a>
                @endforeach
            </nav>
        </div>
    </header>

    <main id="main" class="flex-1">
        @yield('content')
    </main>

    <footer class="relative overflow-hidden border-t border-line bg-surface">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary-500/40 to-transparent" aria-hidden="true"></div>
        <div class="site-container grid gap-12 py-10 md:grid-cols-2 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <a href="{{ route('site.home') }}" class="inline-flex items-center gap-3">
                    <img src="{{ asset('images/brand/logo-320.png') }}" alt="TANTAN" width="52" height="52" class="h-13 w-13 h-[52px] w-[52px] object-contain" />
                    <span>
                        <span class="block text-xl font-extrabold tracking-[0.06em] text-[#0B1F3A]">TANTAN</span>
                        <span class="mt-0.5 block text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-ink-400">Buy · Sell · Discover</span>
                    </span>
                </a>
                <p class="mt-3 max-w-sm text-sm leading-relaxed text-ink-500">
                    India’s friendly classifieds marketplace. Buy, sell and rent near you — photo-first, chat-safe, local.
                </p>
                <div class="mt-4 flex flex-wrap gap-1.5">
                    <span class="stat-pill">English & हिन्दी</span>
                    <span class="stat-pill">Free to download</span>
                    <span class="stat-pill">₹ pricing</span>
                </div>
            </div>
            <div class="lg:col-span-2">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Explore</p>
                <ul class="mt-4 space-y-2.5 text-sm text-ink-500">
                    <li><a href="{{ route('site.browse') }}" class="transition duration-300 hover:text-primary-500">Browse ads</a></li>
                    <li><a href="{{ route('site.about') }}" class="transition duration-300 hover:text-primary-500">About</a></li>
                    <li><a href="{{ route('site.how') }}" class="transition duration-300 hover:text-primary-500">How it works</a></li>
                    <li><a href="{{ route('site.categories') }}" class="transition duration-300 hover:text-primary-500">Categories</a></li>
                    <li><a href="{{ route('site.buyers') }}" class="transition duration-300 hover:text-primary-500">For buyers</a></li>
                    <li><a href="{{ route('site.sellers') }}" class="transition duration-300 hover:text-primary-500">For sellers</a></li>
                    <li><a href="{{ route('site.packages') }}" class="transition duration-300 hover:text-primary-500">Packages</a></li>
                </ul>
            </div>
            <div class="lg:col-span-2">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Trust</p>
                <ul class="mt-4 space-y-2.5 text-sm text-ink-500">
                    <li><a href="{{ route('site.safety') }}" class="transition duration-300 hover:text-primary-500">Safety tips</a></li>
                    <li><a href="{{ route('site.faq') }}" class="transition duration-300 hover:text-primary-500">FAQ</a></li>
                    <li><a href="{{ route('site.contact') }}" class="transition duration-300 hover:text-primary-500">Contact</a></li>
                    <li><a href="{{ route('site.privacy') }}" class="transition duration-300 hover:text-primary-500">Privacy</a></li>
                    <li><a href="{{ route('site.terms') }}" class="transition duration-300 hover:text-primary-500">Terms</a></li>
                    <li><a href="{{ route('site.delete-account') }}" class="transition duration-300 hover:text-primary-500">Delete account</a></li>
                </ul>
            </div>
            <div class="lg:col-span-3">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Get the app</p>
                <p class="mt-4 text-sm leading-relaxed text-ink-500">Post your first free ad in minutes. Chat safely. Sell locally.</p>
                <a href="{{ route('site.download') }}" class="btn-primary mt-5 !min-h-11 !px-4 !text-sm">Download Tan Tan</a>
            </div>
        </div>
        <div class="border-t border-line">
            <p class="site-container py-5 text-xs text-ink-400">
                © {{ date('Y') }} Tan Tan. All rights reserved. Prices in Indian Rupees (₹).
            </p>
        </div>
    </footer>
</body>
</html>
