@extends('layouts.site')

@section('content')
    @include('partials.page-hero', [
        'heading' => 'How Tan Tan works — full walkthrough',
        'lead' => 'This page is the long version: onboarding, location, home discovery, search & filters, posting, My Ads, chat/offers, boosts, notifications, and account controls. Use it as a product guide before you download — or keep it open while you try the app.',
        'image' => 'images/site/hero-chat.jpg',
        'imageCaption' => 'Realtime chat with offers, images and safety tips',
        'actions' => '<a href="'.route('site.download').'" class="btn-primary">Get the app</a><a href="'.route('site.sellers').'" class="btn-secondary">Seller guide</a><a href="'.route('site.buyers').'" class="btn-ghost">Buyer guide</a>',
    ])

    @include('partials.stats-strip', ['items' => [
        ['3', 'Onboarding slides'],
        ['4', 'Post-ad steps'],
        ['5', 'Bottom tabs'],
        ['∞', 'Browse free'],
    ]])

    <section class="section border-b border-line">
        <div class="site-container dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
            <h2 class="section-title text-ink-900">1) Onboarding & account</h2>
            <p>Open Tan Tan and you will see a short animated splash, then a 3-slide onboarding. Slide one includes language selection: <strong class="text-ink-800">English / हिन्दी</strong>. You can change language later in Settings.</p>
            <p>Sign-in is mobile OTP first. Enter your +91 number, receive a 6-digit code, and verify. On Android we aim to support OTP auto-read; on iOS we use one-time-code autofill where available. A resend timer prevents abuse (rate limited on the API). Email and Google are secondary options for people who prefer them, but phone remains the identity backbone for classifieds in India.</p>
            <p>Your profile can include photo, name, bio, contact preferences, and later a verified badge when Tan Tan’s checks say so. There is no separate “seller registration” form — posting is just another action on the same account.</p>
        </div>
    </section>

    <section class="section border-b border-line bg-surface">
        <div class="site-container dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
            <h2 class="section-title text-ink-900">2) Location: GPS or manual</h2>
            <p>After login (or when you first need it), Tan Tan asks for location with a friendly permission screen — not a sudden system popup with no context. You can <strong class="text-ink-800">Use current location</strong> or <strong class="text-ink-800">Choose manually</strong> via city/area search.</p>
            <p>Location powers: Home “near you” recommendations, Featured nearby carousels, distance filters, and map previews on listing detail. You can change it anytime from the location pill on Home. If you deny GPS, manual city selection still works.</p>
        </div>
    </section>

    <section class="section border-b border-line">
        <div class="site-container">
            <h2 class="section-title">3) Home, search & filters</h2>
            <p class="mt-2 text-sm leading-relaxed text-ink-500">The bottom tabs are Home · Search/Categories · <strong class="text-ink-800">Sell</strong> (raised centre) · Chats · Profile.</p>
            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                @foreach ([
                    ['Home', 'Greeting + location pill + notification bell; tall search bar with rotating placeholders; banner carousel; category row; Fresh recommendations infinite grid; Featured near you horizontal list.'],
                    ['Search', 'Recent searches, trending chips, live suggestions as you type, and a filter bar that opens a bottom sheet — not a clunky modal page.'],
                    ['Filters', 'Price range, location/distance, category fields, condition, posted date, sort (relevance / newest / price). Apply and the list updates.'],
                    ['Listing detail', 'Full-bleed gallery with zoom, price + title, chips, expandable description, details grid, map, seller card, safety tips, similar/nearby ads, sticky Chat / Call / WhatsApp bar, share/report/favourite in header.'],
                ] as $block)
                    <div class="rounded-lg bg-surface p-3.5 ring-1 ring-line">
                        <h3 class="text-sm font-bold text-ink-900">{{ $block[0] }}</h3>
                        <p class="mt-1.5 text-xs leading-relaxed text-ink-500 sm:text-sm">{{ $block[1] }}</p>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-sm text-ink-500">Favourites are optimistic (heart reacts instantly). Similar and nearby ads help you compare without starting a new search from scratch. Share uses deep links such as <code class="rounded bg-canvas px-1.5 py-0.5 text-xs">tantan://listing/:id</code>.</p>
        </div>
    </section>

    <section class="section border-b border-line bg-surface">
        <div class="site-container">
            <h2 class="section-title">4) Selling: Post Ad + My Ads</h2>
            <p class="mt-2 text-sm leading-relaxed text-ink-500">Tap the raised Sell button to open the Post Ad stepper (bottom-to-top). Progress shows on top.</p>
            <div class="mt-4 space-y-2">
                @foreach ([
                    ['Step 1 — Category', 'Choose a main category and subcategory. Dynamic fields load for that path (example Cars: brand, model, year, km, fuel, transmission, owners).'],
                    ['Step 2 — Photos', 'Add multiple photos from camera or gallery. Drag to reorder. Tan Tan compresses before upload (max ~1600px, quality ~0.7) to respect mobile data.'],
                    ['Step 3 — Details & price', 'Title, description, price, negotiable/fixed, condition, location, and all category fields. Validation prevents empty required fields.'],
                    ['Step 4 — Review', 'Preview exactly what buyers will see. Submit. You may land in Pending if moderation is on. Success screen can upsell a boost.'],
                ] as $step)
                    <div class="rounded-lg bg-canvas p-3.5 ring-1 ring-line">
                        <h3 class="text-sm font-bold text-ink-900">{{ $step[0] }}</h3>
                        <p class="mt-1 text-sm text-ink-500">{{ $step[1] }}</p>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-sm leading-relaxed text-ink-500"><strong class="text-ink-800">My Ads</strong> tabs: Active, Pending, Sold, Expired, Rejected. Actions include edit, delete, mark sold, renew, repost. Insights show views, chats and favourites so you know whether to improve the listing or promote it. Public seller profiles show listings, ratings and reviews.</p>
        </div>
    </section>

    <section class="section border-b border-line">
        <div class="site-container dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
            <h2 class="section-title text-ink-900">5) Chat, offers, calls & trust</h2>
            <p>Chats list shows unread badges. Inside a thread you get bubble UI, typing indicator, image send, quick-reply templates, Make Offer chips, and a safety banner at the top. Call Seller / WhatsApp appear only when the seller’s privacy setting allows phone visibility.</p>
            <p>You can report an ad, report a user, or block a user. Ratings and reviews build reputation over time. Anti-scam warnings also appear on listing detail. Push notifications cover new messages, offers, ad approval/rejection, price drops on favourites, and expiry reminders. A notification centre and settings let you tune what you receive.</p>
        </div>
    </section>

    <section class="section border-b border-line bg-surface">
        <div class="site-container dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
            <h2 class="section-title text-ink-900">6) Packages, referrals, help</h2>
            <p>From Profile you reach Packages/Boosts: Featured, Top Ad, Bump-up, extra ad limits. Checkout uses Razorpay; webhooks verify payment before badges apply. Transaction history and PDF invoices live in the same area.</p>
            <p>Refer & earn / invite friends uses a referral code and deep link. Help includes FAQs, contact, Terms and Privacy. Settings cover notifications, language, theme (light/dark/system), privacy (show phone), and delete account.</p>
            <p>For more depth see <a href="{{ route('site.packages') }}" class="font-semibold text-primary-500">Packages</a>, <a href="{{ route('site.safety') }}" class="font-semibold text-primary-500">Safety</a>, <a href="{{ route('site.faq') }}" class="font-semibold text-primary-500">FAQ</a>, and <a href="{{ route('site.categories') }}" class="font-semibold text-primary-500">Categories</a>.</p>
        </div>
    </section>

    <section class="section border-b border-line">
        <div class="site-container grid gap-6 lg:grid-cols-2">
            <div class="dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
                <h2 class="section-title text-ink-900">7) Lifecycle of a listing</h2>
                <p>Draft/publish → optional Pending moderation → Active (searchable) → chats & offers → Sold / Expired / Rejected. Expiry reminders nudge you to renew. Insights (views, chats, favourites) tell you whether to fix photos, cut price, or buy a bump.</p>
                <p>Rejected ads always include a reason. Fix the issue and resubmit; do not create a second spam account. Sold ads should be marked promptly so buyers stop travelling for inventory that is gone.</p>
            </div>
            <div class="dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
                <h2 class="section-title text-ink-900">8) What stays on the website vs app</h2>
                <p>This website: About, How it works, Buyers/Sellers guides, Safety, Categories, Packages explanation, FAQ, Terms, Privacy, Delete account, Contact, Download.</p>
                <p>The mobile app: auth, Home feed, search/filters, post ad, My Ads, chat/offers, payments, notifications, settings. Use both — read here, act in the app.</p>
            </div>
        </div>
    </section>

    @include('partials.cta-band', [
        'heading' => 'Ready to try the loop?',
        'text' => 'Download Tan Tan and post or browse within minutes.',
        'primaryHref' => route('site.download'),
        'primaryLabel' => 'Get the app',
        'secondaryHref' => route('site.faq'),
        'secondaryLabel' => 'FAQ',
    ])
@endsection
