@extends('layouts.site')

@section('content')
    <section class="relative overflow-hidden grain">
        <div class="absolute inset-0 mesh-hero" aria-hidden="true"></div>
        <div class="site-container relative grid items-center gap-6 py-8 sm:gap-8 sm:py-10 lg:grid-cols-12">
            <div class="lg:col-span-6">
                <div class="reveal flex items-center gap-3">
                    <img
                        src="{{ asset('images/brand/logo-320.png') }}"
                        alt="TANTAN"
                        width="72"
                        height="72"
                        class="h-[4.5rem] w-[4.5rem] object-contain drop-shadow-sm"
                    />
                    <div>
                        <p class="text-2xl font-extrabold tracking-[0.06em] text-[#0B1F3A] sm:text-3xl">TANTAN</p>
                        <p class="mt-1 text-[0.7rem] font-semibold uppercase tracking-[0.22em] text-ink-400">Buy · Sell · Discover</p>
                    </div>
                </div>
                <h1 class="display mt-4 reveal reveal-delay-1">Buy & sell near you</h1>
                <p class="lede mt-3 reveal reveal-delay-2">
                    India’s friendly classifieds app — post free ads, chat safely, filter by distance, and close deals in your neighbourhood. Phones, bikes, furniture, rentals, jobs & services.
                </p>
                <div class="mt-5 flex flex-wrap gap-2.5 reveal reveal-delay-3">
                    <a href="{{ route('site.download') }}" class="btn-primary">Download free</a>
                    <a href="{{ route('site.how') }}" class="btn-secondary">How it works</a>
                    <a href="{{ route('site.categories') }}" class="btn-ghost">Categories</a>
                </div>
                <div class="mt-4 flex flex-wrap gap-1.5 reveal reveal-delay-3">
                    <span class="stat-pill">✓ Verified sellers</span>
                    <span class="stat-pill">✓ OTP login</span>
                    <span class="stat-pill">✓ In-app chat</span>
                    <span class="stat-pill">✓ English + हिन्दी</span>
                    <span class="stat-pill">✓ Razorpay boosts</span>
                </div>
            </div>

            <div class="relative lg:col-span-6">
                <div class="listing-mosaic">
                    @foreach ([
                        ['iPhone 13', '₹42,000', 'Andheri', 'images/listings/iphone.jpg', 'Featured'],
                        ['Activa 6G', '₹48,500', 'Pune', 'images/listings/scooter.jpg', null],
                        ['Sofa set', '₹8,999', 'Jaipur', 'images/listings/sofa.jpg', null],
                        ['MacBook Air', '₹62,000', 'Bengaluru', 'images/listings/macbook.jpg', 'Verified'],
                        ['Study table', '₹2,499', 'Indore', 'images/site/hero-chat.jpg', null],
                        ['City rental', '₹18,000', 'Hyderabad', 'images/site/hero-city.jpg', 'Rent'],
                        ['Market finds', '₹1,200', 'Delhi', 'images/site/hero-market.jpg', null],
                        ['Weekend sale', '₹999', 'Surat', 'images/site/hero-sell.jpg', 'Hot'],
                    ] as $item)
                        <article class="overflow-hidden rounded-md bg-surface shadow-[var(--shadow-e1)] ring-1 ring-line">
                            <div class="relative aspect-[4/3] overflow-hidden bg-primary-50">
                                <img src="{{ asset($item[3]) }}" alt="{{ $item[0] }}" class="h-full w-full object-cover" loading="lazy">
                                @if ($item[4])
                                    <span class="absolute left-1.5 top-1.5 rounded bg-gold-500 px-1.5 py-0.5 text-[10px] font-bold text-ink-900">{{ $item[4] }}</span>
                                @endif
                            </div>
                            <div class="space-y-0.5 p-2">
                                <p class="text-sm font-extrabold tabular-nums text-ink-900">{{ $item[1] }}</p>
                                <p class="truncate text-xs font-medium text-ink-700">{{ $item[0] }}</p>
                                <p class="text-[10px] text-ink-400">{{ $item[2] }} · 2h ago</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    @include('partials.stats-strip', ['items' => [
        ['50+', 'Cities live'],
        ['25+', 'Categories'],
        ['1 app', 'Buy + sell'],
        ['₹0', 'To start posting'],
    ]])

    <section class="section border-b border-line bg-surface">
        <div class="site-container">
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <p class="eyebrow">Why people switch</p>
                    <h2 class="section-title mt-1">Marketplace that feels local</h2>
                </div>
                <a href="{{ route('site.about') }}" class="shrink-0 text-xs font-semibold text-primary-500 sm:text-sm">Our story →</a>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Photo-first cards', '4:5 imagery, blurhash, clear ₹ price — buyers decide in a glance.', 'images/listings/sofa.jpg'],
                    ['Safe chat + offers', 'Realtime messages, make-offer chips, safety banner on every thread.', 'images/site/hero-chat.jpg'],
                    ['Distance filters', 'GPS or city search. “Near you” is the default, not an afterthought.', 'images/site/hero-city.jpg'],
                    ['Optional boosts', 'Bump, Featured, Top Ad via Razorpay — free posting stays default.', 'images/site/hero-sell.jpg'],
                ] as $f)
                    <article class="overflow-hidden rounded-lg bg-canvas ring-1 ring-line">
                        <div class="aspect-[16/10] overflow-hidden">
                            <img src="{{ asset($f[2]) }}" alt="{{ $f[0] }}" class="h-full w-full object-cover" loading="lazy">
                        </div>
                        <div class="p-3.5">
                            <h3 class="text-sm font-bold text-ink-900">{{ $f[0] }}</h3>
                            <p class="mt-1 text-xs leading-relaxed text-ink-500">{{ $f[1] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section">
        <div class="site-container grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <p class="eyebrow">How it works</p>
                <h2 class="section-title mt-1">Four steps. Done.</h2>
                <p class="mt-2 text-sm text-ink-500">One account for buying and selling — no app switching.</p>
                <a href="{{ route('site.how') }}" class="mt-4 inline-flex text-sm font-semibold text-primary-500">Full walkthrough →</a>
            </div>
            <div class="grid gap-2.5 sm:grid-cols-2 lg:col-span-8">
                @foreach ([
                    ['01', 'OTP signup', 'Mobile login in English or हिन्दी. Google/email secondary.'],
                    ['02', 'Set area', 'GPS or manual city/area — powers near-you feeds.'],
                    ['03', 'Post or browse', '4-step seller flow or Home/Search with filters.'],
                    ['04', 'Chat & close', 'Offers, images, Call/WhatsApp if seller allows, mark sold.'],
                ] as $s)
                    <div class="flex gap-3 rounded-lg bg-surface p-3.5 ring-1 ring-line">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-500 text-xs font-extrabold text-white">{{ $s[0] }}</span>
                        <div>
                            <h3 class="text-sm font-bold text-ink-900">{{ $s[1] }}</h3>
                            <p class="mt-0.5 text-xs leading-relaxed text-ink-500">{{ $s[2] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section border-y border-line bg-surface">
        <div class="site-container">
            <div class="mb-4 flex items-end justify-between">
                <div>
                    <p class="eyebrow">Categories</p>
                    <h2 class="section-title mt-1">Shop what people actually trade</h2>
                </div>
                <a href="{{ route('site.categories') }}" class="text-xs font-semibold text-primary-500 sm:text-sm">All categories →</a>
            </div>
            <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ([
                    ['Vehicles', 'images/listings/scooter.jpg'],
                    ['Property', 'images/site/hero-city.jpg'],
                    ['Mobiles', 'images/listings/iphone.jpg'],
                    ['Electronics', 'images/listings/macbook.jpg'],
                    ['Furniture', 'images/listings/sofa.jpg'],
                    ['Fashion', 'images/site/hero-sell.jpg'],
                    ['Jobs', 'images/site/hero-chat.jpg'],
                    ['Services', 'images/site/hero-safe.jpg'],
                    ['Pets', 'images/site/hero-market.jpg'],
                    ['Books', 'images/listings/macbook.jpg'],
                    ['Kids', 'images/listings/sofa.jpg'],
                    ['More', 'images/site/hero-market.jpg'],
                ] as $cat)
                    <a href="{{ route('site.categories') }}" class="group overflow-hidden rounded-lg ring-1 ring-line transition duration-300 hover:-translate-y-0.5 hover:shadow-[var(--shadow-e2)]">
                        <div class="aspect-square overflow-hidden">
                            <img src="{{ asset($cat[1]) }}" alt="{{ $cat[0] }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy">
                        </div>
                        <p class="bg-canvas px-2 py-2 text-center text-xs font-semibold text-ink-800">{{ $cat[0] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section">
        <div class="site-container grid gap-3 lg:grid-cols-3">
            <a href="{{ route('site.buyers') }}" class="tile lg:col-span-1">
                <p class="text-xs font-bold uppercase tracking-wider text-primary-500">Buyers</p>
                <h3 class="mt-1 text-base font-bold text-ink-900">Search · filter · offer · meet</h3>
                <p class="mt-1.5 text-xs leading-relaxed text-ink-500">Saved searches, favourites, seller ratings, sticky Chat CTA. Checklist before you travel across town.</p>
            </a>
            <a href="{{ route('site.sellers') }}" class="tile lg:col-span-1">
                <p class="text-xs font-bold uppercase tracking-wider text-primary-500">Sellers</p>
                <h3 class="mt-1 text-base font-bold text-ink-900">Photos · price · chat · sold</h3>
                <p class="mt-1.5 text-xs leading-relaxed text-ink-500">My Ads dashboard, insights (views/chats/hearts), renew, repost, boost when you need speed.</p>
            </a>
            <a href="{{ route('site.safety') }}" class="tile lg:col-span-1">
                <p class="text-xs font-bold uppercase tracking-wider text-trust-500">Trust</p>
                <h3 class="mt-1 text-base font-bold text-ink-900">Masked phone · report · block</h3>
                <p class="mt-1.5 text-xs leading-relaxed text-ink-500">Safety tips on detail + chat. Auto-hide after repeated reports. Public delete-account page.</p>
            </a>
        </div>
    </section>

    <section class="section border-t border-line">
        <div class="site-container grid gap-6 lg:grid-cols-2">
            <div>
                <p class="eyebrow">What you can trade</p>
                <h2 class="section-title mt-1">From scooters to services</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink-500">
                    Vehicles, property & PG, mobiles, electronics, furniture, fashion, jobs, local services, pets (where legal), books, kids’ gear and more. Each category unlocks its own fields so buyers can filter properly — fuel and km for cars, BHK and deposit for rentals, storage for phones.
                </p>
                <p class="mt-2 text-sm leading-relaxed text-ink-500">
                    Illegal items, weapons, stolen goods and scams are banned. Read <a href="{{ route('site.safety') }}" class="font-semibold text-primary-500">Safety tips</a> and <a href="{{ route('site.categories') }}" class="font-semibold text-primary-500">Categories</a> before you post something unusual.
                </p>
                <a href="{{ route('site.categories') }}" class="mt-3 inline-flex text-sm font-semibold text-primary-500">Explore all categories →</a>
            </div>
            <div>
                <p class="eyebrow">Cities</p>
                <h2 class="section-title mt-1">Built for Indian neighbourhoods</h2>
                <p class="mt-2 text-sm text-ink-500">Set GPS or pick your city/area. Distance filters keep meetups realistic.</p>
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach (array_slice(\App\Support\SiteCopy::metroCities(), 0, 18) as $city)
                        <span class="rounded-full bg-surface px-2.5 py-1 text-[11px] font-medium text-ink-700 ring-1 ring-line">{{ $city }}</span>
                    @endforeach
                    <a href="{{ route('site.about') }}" class="rounded-full bg-primary-100 px-2.5 py-1 text-[11px] font-semibold text-primary-600">+ more →</a>
                </div>
            </div>
        </div>
    </section>

    <section class="section border-t border-line bg-surface">
        <div class="site-container">
            <div class="mb-3 flex items-end justify-between gap-3">
                <div>
                    <p class="eyebrow">Learn more</p>
                    <h2 class="section-title mt-1">Guides on this website</h2>
                </div>
            </div>
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['How it works', 'Onboarding, posting, chat, boosts — full walkthrough.', route('site.how')],
                    ['Buyer’s guide', 'Search, filters, offers, meetup checklist.', route('site.buyers')],
                    ['Seller’s playbook', 'Photos, pricing, My Ads, when to boost.', route('site.sellers')],
                    ['Safety tips', 'Scam patterns, meetup hygiene, report/block.', route('site.safety')],
                    ['Packages', 'Bump, Featured, Top Ad + invoices.', route('site.packages')],
                    ['FAQ', '80+ long answers · search · 13 topics.', route('site.faq')],
                ] as $g)
                    <a href="{{ $g[2] }}" class="tile !p-3.5">
                        <h3 class="text-sm font-bold text-ink-900">{{ $g[0] }}</h3>
                        <p class="mt-1 text-xs text-ink-500">{{ $g[1] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section-tight border-t border-line">
        <div class="site-container">
            <div class="mb-4 flex items-end justify-between">
                <h2 class="section-title">Neighbourhood voices</h2>
                <a href="{{ route('site.faq') }}" class="text-xs font-semibold text-primary-500">FAQ →</a>
            </div>
            <div class="grid gap-2.5 md:grid-cols-3">
                @foreach ([
                    ['Ananya · Mumbai', 'Sold my phone in two days. Chat felt safer than random WhatsApp numbers.'],
                    ['Rohit · Indore', 'Study table for ₹2,500 near my PG. Clear photos, verified seller.'],
                    ['Meera · Hyderabad', 'Featured boost pushed my scooter — three serious buyers same evening.'],
                ] as $t)
                    <blockquote class="rounded-lg bg-canvas p-4 ring-1 ring-line">
                        <div class="flex gap-0.5 text-gold-500" aria-hidden="true">
                            @for ($i = 0; $i < 5; $i++)
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.25l2.472 6.36h6.528l-5.25 3.96 1.98 6.18L12 15.39l-5.73 3.36 1.98-6.18-5.25-3.96h6.528L12 2.25z"/></svg>
                            @endfor
                        </div>
                        <p class="mt-2 text-xs leading-relaxed text-ink-700">“{{ $t[1] }}”</p>
                        <footer class="mt-2 text-xs font-bold text-ink-900">{{ $t[0] }}</footer>
                    </blockquote>
                @endforeach
            </div>
        </div>
    </section>

    @include('partials.cta-band', [
        'heading' => 'Post your first free ad today',
        'text' => 'Android + iOS. English & हिन्दी. No separate seller account.',
        'secondaryHref' => route('site.packages'),
        'secondaryLabel' => 'See packages',
    ])
@endsection
