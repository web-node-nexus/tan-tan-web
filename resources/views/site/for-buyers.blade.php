@extends('layouts.site')

@section('content')
    @include('partials.page-hero', [
        'heading' => 'The complete buyer’s guide',
        'lead' => 'How to search, filter, evaluate sellers, make offers, meet safely and avoid the usual classifieds traps — written as a practical guide you can follow on Tan Tan.',
        'image' => 'images/listings/macbook.jpg',
        'actions' => '<a href="'.route('site.download').'" class="btn-primary">Start browsing</a><a href="'.route('site.safety').'" class="btn-secondary">Safety tips</a><a href="'.route('site.categories').'" class="btn-ghost">Categories</a>',
    ])

    <section class="section border-b border-line">
        <div class="site-container dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
            <h2 class="section-title text-ink-900">Set up for good discovery</h2>
            <p>Before you hunt, set your <strong class="text-ink-800">location</strong> accurately (GPS or manual city/area). Wrong location means wrong “near you” results. Choose your language (English / हिन्दी) so filters and messages feel natural.</p>
            <p>On Home, skim banners for campaigns, the category row for a starting point, Fresh recommendations for new inventory, and Featured near you for promoted listings. Use the tall search bar when you already know the keyword (“iPhone 13”, “2 BHK Andheri”, “study table”).</p>
            <p>Save searches you care about. Recent searches come back automatically; saved searches are for campaigns like “Activa under ₹50,000 within 15 km” that you will reopen for days.</p>
        </div>
    </section>

    <section class="section border-b border-line bg-surface">
        <div class="site-container">
            <h2 class="section-title">Use filters like a pro</h2>
            <p class="mt-2 text-sm text-ink-500">Open the filter bottom sheet from Search. Typical controls:</p>
            <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Price range', 'Set min/max in ₹. Avoid sorting only by lowest price — scams love the bottom.'],
                    ['Distance / city', 'Tighten radius when you can meet this week; widen when researching.'],
                    ['Condition', 'New / like new / used — match your risk tolerance.'],
                    ['Posted date', 'Fresh ads often mean sellers still have the item.'],
                    ['Sort', 'Relevance, newest, price low/high — switch after filtering.'],
                    ['Category fields', 'Fuel, km, BHK, storage, furnished… this is where Tan Tan beats generic search.'],
                ] as $f)
                    <div class="rounded-lg bg-canvas p-3 ring-1 ring-line">
                        <h3 class="text-sm font-bold text-ink-900">{{ $f[0] }}</h3>
                        <p class="mt-1 text-xs text-ink-500 sm:text-sm">{{ $f[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section border-b border-line">
        <div class="site-container dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
            <h2 class="section-title text-ink-900">Evaluate a listing before you chat</h2>
            <p>Open the gallery and zoom. Count how many photos show defects. Read the description for what is included (box, bill, spare key) and what is missing. Check the details grid against the photos. Look at the map — is the area realistic for the price?</p>
            <p>Open the seller row: avatar, name, verified badge, rating count, member since. Tap through to their public profile and skim other ads. A seller with multiple coherent listings is usually lower risk than a brand-new account with one miraculous deal.</p>
            <p>Favourite items you are comparing. Hearts are optimistic — they react instantly while syncing. If price-drop notifications are enabled, you will hear when a favourite gets cheaper.</p>
        </div>
    </section>

    <section class="section border-b border-line bg-surface">
        <div class="site-container dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
            <h2 class="section-title text-ink-900">Chat & Make Offer</h2>
            <p>Start with a specific question and a realistic offer. Example: “Is the battery health above 85%? I can do ₹40,000 and meet Sunday afternoon in Andheri.” That beats “lowest?” ten times out of ten.</p>
            <p>Use Make Offer so the number is structured in the thread. Share a bit of scheduling flexibility. Ask for a short video if photos are ambiguous. Keep the conversation in Tan Tan until you agree to meet — safety banners exist for a reason.</p>
            <p>Call/WhatsApp buttons appear only if the seller enabled Show phone. Even then, you do not have to call; chat is enough for many deals.</p>
        </div>
    </section>

    <section class="section border-b border-line">
        <div class="site-container">
            <h2 class="section-title mb-3">Meetup checklist (print this in your head)</h2>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach ([
                    'Meet daytime in a public place; tell a friend',
                    'Inspect power-on, ports, scratches, documents',
                    'For phones: IMEI, lock status, call/camera test',
                    'For vehicles: RC, insurance, cold start, test ride norms',
                    'For furniture: measure lift/door; check pests/damage',
                    'Pay only after inspection; verify UPI in your bank app',
                    'Never “refund overpayment”; never courier advances',
                    'If anything feels wrong — leave and report',
                ] as $i => $line)
                    <div class="flex gap-2 rounded-lg bg-surface px-3 py-2.5 text-sm text-ink-600 ring-1 ring-line">
                        <span class="font-bold text-primary-500">{{ $i + 1 }}.</span> {{ $line }}
                    </div>
                @endforeach
            </div>
            <p class="mt-4 text-sm text-ink-500">Deep dive on scams: <a href="{{ route('site.safety') }}" class="font-semibold text-primary-500">Safety tips</a>. Category nuances: <a href="{{ route('site.categories') }}" class="font-semibold text-primary-500">Categories</a>.</p>
        </div>
    </section>

    @include('partials.cta-band', [
        'heading' => 'Start with a saved search',
        'text' => 'Download Tan Tan, set your area, and favourite the first three options worth comparing.',
        'secondaryHref' => route('site.faq'),
        'secondaryLabel' => 'Buyer FAQ',
    ])
@endsection
