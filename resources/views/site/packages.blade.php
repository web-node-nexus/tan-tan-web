@extends('layouts.site')

@section('content')
    @include('partials.page-hero', [
        'heading' => 'Boost packages & pricing',
        'lead' => 'Free posting is the default. Buy visibility only when you want more views this week — Bump, Featured (best value) or Top Ad. Paid via Razorpay (UPI/cards), webhook-verified before badges apply.',
        'image' => 'images/site/hero-sell.jpg',
        'actions' => '<a href="'.route('site.download').'" class="btn-primary">Buy in the app</a><a href="'.route('site.sellers').'" class="btn-secondary">Seller playbook</a>',
    ])

    @include('partials.stats-strip', ['items' => [
        ['₹49', 'Bump up'],
        ['₹149', 'Featured / 7d'],
        ['₹299', 'Top Ad / 14d'],
        ['PDF', 'Invoices'],
    ]])

    <section class="section">
        <div class="site-container grid gap-3 lg:grid-cols-3">
            @foreach ([
                ['Bump Up', '₹49', 'per bump', 'Quick refresh', false, ['Jumps active ad to top once', 'Best after price drop / new photos', 'Great for local furniture & electronics', 'Effect lasts until newer ads appear', 'UPI / cards via Razorpay']],
                ['Featured', '₹149', '7 days', 'Best value', true, ['Gold Featured ribbon on cards', 'Home “Featured near you” eligibility', 'Higher category browse placement', 'Ideal for phones & bikes', 'Invoice in transaction history']],
                ['Top Ad', '₹299', '14 days', 'Max reach', false, ['Priority in category/search mix', 'Longer window for vehicles/property', 'Pairs with strong photos + fair price', 'For sellers who need speed', 'Non-transferable to other accounts']],
            ] as $pack)
                <article @class([
                    'relative flex flex-col rounded-lg p-4 transition duration-300 sm:p-5',
                    'bg-gradient-to-b from-primary-50 to-surface shadow-[var(--shadow-e3)] ring-2 ring-gold-500' => $pack[4],
                    'bg-surface shadow-[var(--shadow-e1)] ring-1 ring-line' => ! $pack[4],
                ])>
                    @if ($pack[4])
                        <span class="absolute -top-2.5 left-4 rounded-full bg-gold-500 px-2.5 py-0.5 text-[10px] font-bold text-ink-900">Best value</span>
                    @endif
                    <p class="text-[11px] font-medium text-ink-400">{{ $pack[3] }}</p>
                    <h2 class="mt-0.5 text-lg font-bold text-ink-900">{{ $pack[0] }}</h2>
                    <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink-900">{{ $pack[1] }}</p>
                    <p class="text-xs text-ink-500">{{ $pack[2] }}</p>
                    <ul class="mt-4 flex-1 space-y-1.5 text-xs text-ink-700">
                        @foreach ($pack[5] as $perk)
                            <li class="flex gap-2"><span class="text-trust-500">✓</span>{{ $perk }}</li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>
    </section>

    <section class="section border-t border-line bg-surface">
        <div class="site-container grid gap-6 lg:grid-cols-2">
            <div class="dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
                <h2 class="section-title text-ink-900">When a boost is worth it</h2>
                <p>Boost after the listing is strong: sharp photos, complete fields, fair price. Promoting a blurry ad mostly burns money. Use insights in My Ads — if views are low, Featured/Top can help; if views are high but chats are low, fix title/price first, then bump.</p>
                <p>Bump is ideal right after a price drop or new photos. Featured (7 days) is the usual best value for phones and bikes. Top Ad (14 days) fits higher-ticket vehicles and property when you need sustained priority.</p>
                <p>Free monthly ad limits still apply to how many ads you can keep active; extra-ad packs help side-hustles without waiting for the next cycle. Seasonal bundles may appear as Home banners.</p>
            </div>
            <div class="dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
                <h2 class="section-title text-ink-900">Payments & fairness</h2>
                <p>Checkout is inside the app via Razorpay (UPI/cards and other enabled methods). Tan Tan does not store full card numbers. Webhooks verify payment before any Featured/Top badge is applied — screenshots alone never unlock promotions.</p>
                <p>Transaction history and PDF invoices live under Profile → Packages / Payments. Packages are non-transferable between accounts and apply only to the listing you select. Rejected or blocked ads cannot remain promoted.</p>
                <p>Boosts improve visibility; they do not guarantee a sale. Refunds for consumed promotion time are generally not available; if payment succeeded but activation failed technically, contact support with your payment ID. See <a href="{{ route('site.terms') }}" class="font-semibold text-primary-500">Terms</a> and <a href="{{ route('site.faq') }}" class="font-semibold text-primary-500">FAQ</a>.</p>
            </div>
        </div>
        <div class="site-container mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['Extra ad limits', 'For side-hustles that outgrow free monthly posts.'],
                ['Festive bundles', 'Bump + Featured combos appear as in-app banners.'],
                ['Webhook truth', 'No screenshot “proof” — payment must verify first.'],
                ['No sale guarantee', 'Boosts raise visibility; photos & price still win.'],
            ] as $x)
                <div class="rounded-lg bg-canvas p-3.5 ring-1 ring-line">
                    <h3 class="text-sm font-bold text-ink-900">{{ $x[0] }}</h3>
                    <p class="mt-1 text-xs text-ink-500">{{ $x[1] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    @include('partials.cta-band', [
        'heading' => 'Promote from Profile → Packages',
        'text' => 'Pick a listing, choose a plan, pay with UPI.',
    ])
@endsection
