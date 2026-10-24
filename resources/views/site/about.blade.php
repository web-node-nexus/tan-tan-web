@extends('layouts.site')

@section('content')
    @include('partials.page-hero', [
        'heading' => 'About Tan Tan',
        'lead' => 'Tan Tan is a photo-first classifieds marketplace built for Indian neighbourhoods. One mobile app where every person is both buyer and seller — with clear ₹ prices, masked phone numbers, realtime chat, and safety tools that are part of the product, not an afterthought.',
        'image' => 'images/site/hero-market.jpg',
        'imageCaption' => 'Local trade, designed to feel calm and trustworthy',
        'actions' => '<a href="'.route('site.download').'" class="btn-primary">Download free</a><a href="'.route('site.how').'" class="btn-secondary">How it works</a><a href="'.route('site.contact').'" class="btn-ghost">Contact</a>',
        'meta' => '<span class="stat-pill">Buyer = seller</span><span class="stat-pill">EN + हिन्दी</span><span class="stat-pill">Free to start</span><span class="stat-pill">India-first</span>',
    ])

    @include('partials.stats-strip', ['items' => [
        ['1', 'Account for buy + sell'],
        ['2', 'Languages'],
        ['25+', 'Category trees'],
        ['50+', 'Cities roadmap'],
    ]])

    <section class="section border-b border-line">
        <div class="site-container grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <p class="eyebrow">The problem</p>
                <h2 class="section-title mt-1">Everyday trade became noisy</h2>
            </div>
            <div class="dense-prose space-y-3 text-sm leading-relaxed text-ink-500 lg:col-span-8 sm:text-[15px]">
                <p>India already has a massive second-hand and local services economy. People sell phones when they upgrade, scooters when they move cities, furniture when they leave a PG, and rent rooms through forwards in family groups. The intent is healthy. The tools are often not.</p>
                <p>Chat groups bury good listings under stickers. Social marketplaces mix genuine sellers with spam and fake “urgent abroad” prices. Classic classifieds sites sometimes expose phone numbers to everyone, which turns into robocalls. Photos are blurry. Prices are vague. Trust is something you invent on the footpath after you have already travelled across town.</p>
                <p>Tan Tan exists to make that same everyday trade feel <strong class="text-ink-900">simple, local and trustworthy</strong> — without forcing you to become a “professional seller” or learn a complicated dashboard.</p>
            </div>
        </div>
    </section>

    <section class="section border-b border-line bg-surface">
        <div class="site-container grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <p class="eyebrow">Our approach</p>
                <h2 class="section-title mt-1">What we chose on purpose</h2>
            </div>
            <div class="dense-prose space-y-3 text-sm leading-relaxed text-ink-500 lg:col-span-8 sm:text-[15px]">
                <p><strong class="text-ink-900">One account, both roles.</strong> There is no separate seller registration and no app switching. If you can browse, you can post. If you can post, you can favourite and chat.</p>
                <p><strong class="text-ink-900">Photos first.</strong> Listing cards are built around imagery and ₹ price. The UI stays quiet so content can lead — the same philosophy as our mobile design system.</p>
                <p><strong class="text-ink-900">Privacy by default.</strong> Phone numbers stay masked until a seller opts into Call/WhatsApp. Chat and Make Offer are first-class, so discovery does not require giving strangers your number on day one.</p>
                <p><strong class="text-ink-900">India-first details.</strong> OTP login, English + हिन्दी typography, Indian digit grouping for money, city/area location, Razorpay for boosts, and performance budgets aimed at mid-range Android.</p>
                <p><strong class="text-ink-900">Free first, boost later.</strong> Posting within free limits is core. Packages exist when you want speed — never as a paywall to try the product.</p>
            </div>
        </div>
    </section>

    <section class="section border-b border-line">
        <div class="site-container">
            <p class="eyebrow">Who it is for</p>
            <h2 class="section-title mt-1">People we design for every week</h2>
            <div class="mt-4 grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Students & PGs', 'Buy study tables, cycles and mattresses near campus. Sell them again when the semester ends — without spamming class groups.'],
                    ['Young families', 'Furniture, appliances and kids’ gear with honest condition photos and distance filters so you are not driving 90 minutes for a “maybe”.'],
                    ['First-time sellers', 'Clearing a cupboard or moving city? The 4-step Post Ad flow explains itself: category → photos → details → review.'],
                    ['Side-hustle resellers', 'My Ads, insights, renew/repost and optional boosts when a listing needs more eyes — still one consumer account.'],
                    ['Service providers', 'Tutors, electricians, cleaners and freelancers list services with area coverage and pricing model fields.'],
                    ['Vehicle & property seekers', 'Category fields (fuel, km, BHK, deposit) make comparisons possible before you call anyone.'],
                ] as $card)
                    <article class="tile !p-3.5">
                        <h3 class="text-sm font-bold text-ink-900">{{ $card[0] }}</h3>
                        <p class="mt-1.5 text-xs leading-relaxed text-ink-500 sm:text-sm">{{ $card[1] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section border-b border-line bg-surface">
        <div class="site-container">
            <p class="eyebrow">Product beliefs</p>
            <h2 class="section-title mt-1">Nine principles behind the screens</h2>
            <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Content is the hero', 'Photos and price speak before chrome.'],
                    ['Trust must be visible', 'Badges, ratings, safety tips, report/block.'],
                    ['Local beats endless scroll', 'Near you is the default discovery mode.'],
                    ['Hindi + English equally', 'Proper Devanagari, not bolted-on translation.'],
                    ['Speed is respect', 'Skeletons, optimistic UI, compressed uploads.'],
                    ['Free first', 'Boosts are optional accelerators.'],
                    ['Phone privacy default', 'Masked until the seller opts in.'],
                    ['Moderation that acts', 'Pending/rejected reasons; auto-hide on reports.'],
                    ['India payments', 'Razorpay UPI/cards with webhook truth.'],
                ] as $i => $b)
                    <div class="rounded-lg bg-canvas p-3.5 ring-1 ring-line">
                        <p class="text-[10px] font-bold text-primary-500">0{{ $i + 1 }}</p>
                        <h3 class="mt-1 text-sm font-bold text-ink-900">{{ $b[0] }}</h3>
                        <p class="mt-1 text-xs text-ink-500">{{ $b[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section border-b border-line">
        <div class="site-container">
            <p class="eyebrow">Cities</p>
            <h2 class="section-title mt-1">Where Tan Tan is aimed</h2>
            <p class="lede mt-2">Launch focus is major Indian metros and fast-growing Tier-2 cities. Location is user-selected — you are never forced into a wrong city just to browse.</p>
            <div class="mt-4 flex flex-wrap gap-1.5">
                @foreach (\App\Support\SiteCopy::metroCities() as $city)
                    <span class="rounded-full bg-surface px-3 py-1.5 text-xs font-medium text-ink-700 ring-1 ring-line">{{ $city }}</span>
                @endforeach
            </div>
            <p class="mt-3 text-sm text-ink-500">If your city is smaller, you can still set a nearby hub and use distance filters. City coverage expands with demand and seeding.</p>
        </div>
    </section>

    <section class="section border-b border-line bg-surface">
        <div class="site-container">
            <h2 class="section-title mb-3">Tan Tan vs typical classifieds noise</h2>
            <div class="overflow-hidden rounded-lg ring-1 ring-line">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-ink-900 text-white">
                        <tr>
                            <th class="px-3 py-2.5 font-semibold sm:px-4">Topic</th>
                            <th class="px-3 py-2.5 font-semibold text-white/60 sm:px-4">Typical noise</th>
                            <th class="px-3 py-2.5 font-semibold text-primary-100 sm:px-4">Tan Tan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line bg-canvas">
                        @foreach ([
                            ['Account model', 'Separate buyer/seller flows or “business packs” before you try', 'One consumer account that buys and sells'],
                            ['Listing quality', 'Blurry photos, ALL CAPS titles, missing details', 'Photo-first cards + category fields'],
                            ['Contact privacy', 'Phone visible to everyone → spam', 'Masked until seller enables Show phone'],
                            ['Negotiation', 'Forced early jump to random chat apps', 'In-app chat, images, Make Offer'],
                            ['Language', 'English-only or broken Hindi', 'English + हिन्दी with proper type'],
                            ['Monetisation', 'Paywalls or confusing credits', 'Free posts; optional Razorpay boosts'],
                            ['Trust tools', 'Buried report links', 'Report/block + safety banners in context'],
                            ['Performance', 'Heavy pages on mid-range phones', 'Mobile-first budgets, compressed media'],
                        ] as $row)
                            <tr>
                                <td class="px-3 py-2.5 font-semibold text-ink-900 sm:px-4">{{ $row[0] }}</td>
                                <td class="px-3 py-2.5 text-ink-500 sm:px-4">{{ $row[1] }}</td>
                                <td class="px-3 py-2.5 font-medium text-ink-800 sm:px-4">{{ $row[2] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="site-container grid gap-4 lg:grid-cols-2">
            <div class="panel">
                <h2 class="text-base font-bold text-ink-900">What Tan Tan is not</h2>
                <ul class="mt-3 space-y-2 text-sm leading-relaxed text-ink-500">
                    <li>• Not an escrow for every goods purchase (you pay the other person offline).</li>
                    <li>• Not a guarantee that every seller is perfect — trust tools + inspection still matter.</li>
                    <li>• Not a place for illegal goods, hate, or scams — those get removed.</li>
                    <li>• Not a desktop-first posting portal — the app is the product.</li>
                </ul>
            </div>
            <div class="panel">
                <h2 class="text-base font-bold text-ink-900">Talk to us</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink-500">
                    Support: <a class="font-semibold text-primary-500" href="mailto:support@tantan.app">support@tantan.app</a><br>
                    Partnerships / press: <a class="font-semibold text-primary-500" href="mailto:hello@tantan.app">hello@tantan.app</a><br>
                    Privacy: <a class="font-semibold text-primary-500" href="mailto:privacy@tantan.app">privacy@tantan.app</a>
                </p>
                <p class="mt-3 text-sm text-ink-500">Or use the <a href="{{ route('site.contact') }}" class="font-semibold text-primary-500">contact form</a>. Read <a href="{{ route('site.terms') }}" class="font-semibold text-primary-500">Terms</a> and <a href="{{ route('site.privacy') }}" class="font-semibold text-primary-500">Privacy</a> for legal detail.</p>
            </div>
        </div>
    </section>

    @include('partials.cta-band', [
        'heading' => 'Join the neighbourhood marketplace',
        'text' => 'Download Tan Tan — post free, chat safe, sell local.',
        'secondaryHref' => route('site.faq'),
        'secondaryLabel' => 'Read FAQ',
    ])
@endsection
