@extends('layouts.site')

@section('content')
    <section class="relative overflow-hidden grain border-b border-line">
        <div class="absolute inset-0 mesh-hero" aria-hidden="true"></div>
        <div class="site-container relative grid items-center gap-6 py-8 sm:py-10 lg:grid-cols-12">
            <div class="lg:col-span-6">
                <img
                    src="{{ asset('images/brand/logo-320.png') }}"
                    alt="TANTAN"
                    width="88"
                    height="88"
                    class="h-20 w-20 object-contain sm:h-[5.5rem] sm:w-[5.5rem]"
                />
                <p class="mt-3 text-sm font-semibold uppercase tracking-[0.2em] text-ink-400">Buy · Sell · Discover</p>
                <h1 class="display mt-2">Get the app</h1>
                <p class="lede mt-3">Post free ads, chat safely, filter nearby deals — English & हिन्दी. Same account on Android and iOS. The website explains Tan Tan; the app is where you buy, sell and message.</p>
                <div class="mt-5 flex flex-wrap gap-2.5">
                    <span class="inline-flex min-h-12 items-center rounded-md bg-ink-900 px-5 text-sm font-semibold text-white shadow-[var(--shadow-e2)]">Google Play</span>
                    <span class="inline-flex min-h-12 items-center rounded-md border border-line bg-surface px-5 text-sm font-semibold text-ink-900 shadow-[var(--shadow-e1)]">App Store</span>
                </div>
                <div class="mt-4 flex flex-wrap gap-1.5">
                    <span class="stat-pill">Free to start</span>
                    <span class="stat-pill">Phone masked</span>
                    <span class="stat-pill">Delete anytime</span>
                    <span class="stat-pill">OTP login</span>
                    <span class="stat-pill">EN + हिन्दी</span>
                </div>
                <p class="mt-3 text-[11px] text-ink-400">Public store URLs publish at launch. Testers: EAS / Play internal tracks.</p>
            </div>
            <div class="listing-mosaic lg:col-span-6">
                @foreach ([
                    ['images/listings/iphone.jpg', 'Mobiles'],
                    ['images/listings/scooter.jpg', 'Vehicles'],
                    ['images/listings/sofa.jpg', 'Furniture'],
                    ['images/listings/macbook.jpg', 'Electronics'],
                    ['images/site/hero-city.jpg', 'Property'],
                    ['images/site/hero-market.jpg', 'Local'],
                ] as $cell)
                    <div class="overflow-hidden rounded-md ring-1 ring-line">
                        <img src="{{ asset($cell[0]) }}" alt="{{ $cell[1] }}" class="aspect-[4/3] w-full object-cover" loading="lazy">
                        <p class="bg-surface px-2 py-1.5 text-center text-[10px] font-bold text-ink-700">{{ $cell[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section border-b border-line">
        <div class="site-container grid gap-6 lg:grid-cols-2">
            <div class="dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
                <p class="eyebrow">After install</p>
                <h2 class="section-title text-ink-900">Five minutes to your first useful session</h2>
                <ol class="list-decimal space-y-2 pl-5">
                    <li>Pick English or हिन्दी — you can change later in Settings.</li>
                    <li>Sign in with +91 OTP (recommended) or Google, then link a phone if you started with Google.</li>
                    <li>Set location with GPS or search your city/area so Home ranks nearby ads.</li>
                    <li>Allow notifications if you want chat and expiry reminders (you can trim categories later).</li>
                    <li>Browse Home, open Filters, or tap Post to list something with photos and category fields.</li>
                </ol>
            </div>
            <div class="dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
                <p class="eyebrow">What the app includes</p>
                <h2 class="section-title text-ink-900">Built for camera, maps and chat</h2>
                <p>Posting needs a camera roll and compression pipeline. Chat needs realtime delivery and push. Meetup distance needs location. Payments for boosts need a secure native checkout. That is why selling and chatting live in the app while this website stays focused on guides, safety, packages explanation and legal pages.</p>
                <p>Low-end Android is a design target: careful motion, compressed uploads, and readable Hindi. Use a development or store build for full native modules — Expo Go alone may not cover maps, secure storage and payments during development.</p>
            </div>
        </div>
    </section>

    <section class="section-tight bg-surface border-b border-line">
        <div class="site-container">
            <h2 class="section-title">Learn before you list</h2>
            <p class="mt-1 text-sm text-ink-500">Short paths into the rest of the site — same product, more depth.</p>
            <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['How it works', 'OTP → location → post/browse → chat → optional boost', route('site.how')],
                    ['Buyer’s guide', 'Filters, offers, meetup checklist, red flags', route('site.buyers')],
                    ['Seller’s playbook', 'Photos, pricing, My Ads, when to boost', route('site.sellers')],
                    ['Safety tips', 'Scam patterns for phones, rentals, jobs, vehicles', route('site.safety')],
                    ['Packages', 'Bump, Featured, Top Ad + invoices', route('site.packages')],
                    ['FAQ', '80+ long answers with search & topic nav', route('site.faq')],
                    ['Categories', 'Fields and tips per category tree', route('site.categories')],
                    ['Privacy', 'What we store and how to delete', route('site.privacy')],
                    ['Delete account', 'In-app OTP or email fallback steps', route('site.delete-account')],
                ] as $card)
                    <a href="{{ $card[2] }}" class="tile !p-3.5">
                        <h2 class="text-sm font-bold text-ink-900">{{ $card[0] }}</h2>
                        <p class="mt-1 text-xs text-ink-500">{{ $card[1] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section">
        <div class="site-container grid gap-4 lg:grid-cols-3">
            <div class="panel">
                <h3 class="text-sm font-bold text-ink-900">System notes</h3>
                <p class="mt-2 text-xs leading-relaxed text-ink-500">Minimum OS versions will be listed on the store pages at launch. Keep a little free storage for photo cache. Grant camera/photos permission only when you post.</p>
            </div>
            <div class="panel">
                <h3 class="text-sm font-bold text-ink-900">Languages</h3>
                <p class="mt-2 text-xs leading-relaxed text-ink-500">English and हिन्दी ship first. Listing descriptions stay in whatever language you type — write in the language your neighbourhood actually reads.</p>
            </div>
            <div class="panel">
                <h3 class="text-sm font-bold text-ink-900">Support</h3>
                <p class="mt-2 text-xs leading-relaxed text-ink-500">In-app Help keeps account context. Or email <a href="mailto:support@tantan.app" class="font-semibold text-primary-500">support@tantan.app</a> · <a href="{{ route('site.contact') }}" class="font-semibold text-primary-500">Contact form</a>.</p>
            </div>
        </div>
    </section>
@endsection
