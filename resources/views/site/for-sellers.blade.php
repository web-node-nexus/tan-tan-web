@extends('layouts.site')

@section('content')
    @include('partials.page-hero', [
        'heading' => 'The complete seller’s playbook',
        'lead' => 'How to photograph, price, write, post, reply, boost and close — so you attract serious buyers instead of endless “still available?” spam. Built around Tan Tan’s 4-step Post Ad flow and My Ads insights.',
        'image' => 'images/site/hero-sell.jpg',
        'actions' => '<a href="'.route('site.download').'" class="btn-primary">Post an ad</a><a href="'.route('site.packages').'" class="btn-secondary">Boost packages</a><a href="'.route('site.safety').'" class="btn-ghost">Safety</a>',
    ])

    <section class="section border-b border-line">
        <div class="site-container dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
            <h2 class="section-title text-ink-900">Before you tap Sell</h2>
            <p>Clean the item. Charge electronics. Find the bill/box if you have them. Check 3–5 similar <strong class="text-ink-800">active</strong> ads in your city for price reality. Decide your walk-away number before chats start — that keeps Make Offer counters calm and fast.</p>
            <p>Pick the right category path. Wrong category means wrong fields and wrong filters, which means the wrong buyers. If you are unsure, open <a href="{{ route('site.categories') }}" class="font-semibold text-primary-500">Categories</a> on this site for field examples.</p>
        </div>
    </section>

    <section class="section border-b border-line bg-surface">
        <div class="site-container">
            <h2 class="section-title">Photos that get taps</h2>
            <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Light', 'Shoot near a window. Avoid yellow indoor gloom and harsh flash glare on screens.'],
                    ['Background', 'Clear clutter. A plain floor/wall makes condition obvious.'],
                    ['Coverage', 'Hero angle + back + sides + ports/defects + accessories + scale (for furniture).'],
                    ['Honesty', 'Show scratches. Buyers who still come are ready to close; surprised buyers waste meetups.'],
                    ['Order', 'Best photo first — it is the Home grid cover.'],
                    ['Upload', 'Tan Tan compresses (~1600px). Still start from a sharp original.'],
                ] as $c)
                    <div class="rounded-lg bg-canvas p-3.5 ring-1 ring-line">
                        <h3 class="text-sm font-bold text-ink-900">{{ $c[0] }}</h3>
                        <p class="mt-1 text-xs text-ink-500 sm:text-sm">{{ $c[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section border-b border-line">
        <div class="site-container dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
            <h2 class="section-title text-ink-900">Titles, descriptions & fields</h2>
            <p>Good title pattern: <em>Brand Model — key fact — area</em>. Example: “iPhone 13 128GB — battery 87% — Andheri West”. Avoid ALL CAPS and emoji spam.</p>
            <p>Description should answer: why selling, what is included, any defects, meetup flexibility, and whether price is firm. Fill every category field you can — empty fields hide you from serious filters.</p>
            <p>Price: use Indian reality, not wishful thinking. Mark <strong class="text-ink-800">negotiable</strong> only if you have room; <strong class="text-ink-800">fixed</strong> if you do not. If you get many chats but low offers, drop price and bump — favourites may get a price-drop nudge.</p>
        </div>
    </section>

    <section class="section border-b border-line bg-surface">
        <div class="site-container dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
            <h2 class="section-title text-ink-900">After publish: My Ads & chat</h2>
            <p>Watch insights: views without chats often means weak photos/title; chats without close often means price. Edit freely. Renew before expiry. Mark sold the moment you hand over — leaving sold ads active hurts your reputation and wastes buyers’ time.</p>
            <p>Reply the same day. Counter Make Offer clearly (“I can do ₹45,000 this weekend in Pune”). Suggest two public meetup options. Keep Show phone off until you are comfortable. Never share OTPs or KYC images. Confirm payment in your bank app before handover.</p>
            <p>When you need speed and the listing is already strong, compare <a href="{{ route('site.packages') }}" class="font-semibold text-primary-500">Bump / Featured / Top Ad</a>. Boosting a weak listing mostly burns money.</p>
        </div>
    </section>

    <section class="section">
        <div class="site-container grid gap-2 sm:grid-cols-2">
            <div class="rounded-lg bg-trust-100/50 p-4 ring-1 ring-trust-100">
                <p class="text-xs font-bold uppercase tracking-wider text-trust-500">Do</p>
                <ul class="mt-2 space-y-1.5 text-sm text-ink-600">
                    <li>• Fill fields · honest photos · fair price</li>
                    <li>• Reply fast · public meetups · mark sold</li>
                    <li>• Use insights before buying boosts</li>
                    <li>• Report harassment; block when needed</li>
                </ul>
            </div>
            <div class="rounded-lg bg-danger-500/5 p-4 ring-1 ring-danger-500/20">
                <p class="text-xs font-bold uppercase tracking-wider text-danger-500">Don’t</p>
                <ul class="mt-2 space-y-1.5 text-sm text-ink-600">
                    <li>• Share OTP / Aadhaar / PAN in chat</li>
                    <li>• Accept courier advances for high value goods</li>
                    <li>• Leave sold ads active</li>
                    <li>• Overpromise condition — returns become fights</li>
                </ul>
            </div>
        </div>
    </section>

    @include('partials.cta-band', [
        'heading' => 'Post your first ad in four steps',
        'text' => 'Category → Photos → Details → Review. Free within limits.',
        'secondaryHref' => route('site.how'),
        'secondaryLabel' => 'Full walkthrough',
    ])
@endsection
