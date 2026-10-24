@extends('layouts.site')

@section('content')
    @include('partials.page-hero', [
        'heading' => 'Safety tips for buyers & sellers',
        'lead' => 'Most Tan Tan deals are ordinary neighbourhood trades. This guide is long on purpose — so you recognise scam patterns early, meet safely, and know how Tan Tan’s report/block tools fit into the flow. Tan Tan staff will never ask for your OTP in chat.',
        'image' => 'images/site/hero-safe.jpg',
        'actions' => '<a href="'.route('site.contact').'" class="btn-primary">Report a concern</a><a href="'.route('site.faq').'" class="btn-ghost">Safety FAQ</a><a href="'.route('site.how').'" class="btn-secondary">How chat works</a>',
        'meta' => '<span class="stat-pill">Phone masked by default</span><span class="stat-pill">Report + block</span><span class="stat-pill">Chat safety banner</span>',
    ])

    <section class="section-tight">
        <div class="site-container">
            <div class="rounded-lg bg-ink-900 px-4 py-4 text-white sm:px-5">
                <p class="text-[11px] font-bold uppercase tracking-wider text-warning-500">Golden rules</p>
                <ul class="mt-2 space-y-1.5 text-sm leading-relaxed text-white/85">
                    <li>1. Never share OTPs, UPI PINs, net-banking passwords, or “verify payment” links.</li>
                    <li>2. Prefer Tan Tan chat until you are ready to meet — do not rush off-platform.</li>
                    <li>3. Inspect high-value items in person before paying. No courier advances to strangers.</li>
                    <li>4. If you feel unsafe, leave immediately and contact local authorities; then report in-app.</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="section border-b border-line bg-surface">
        <div class="site-container grid gap-4 lg:grid-cols-2">
            <div class="panel">
                <h2 class="text-base font-bold text-ink-900">Buyer playbook (detailed)</h2>
                <div class="mt-3 space-y-3 text-sm leading-relaxed text-ink-500">
                    <p><strong class="text-ink-800">Research in the app first.</strong> Open the gallery and pinch-zoom. Read the full description and details grid. Check map distance. Open the seller profile for verified badge, ratings, reviews, member-since, and other active listings. If something feels inconsistent (luxury phone at absurd price, stock photos, brand-new “urgently leaving country”), slow down.</p>
                    <p><strong class="text-ink-800">Chat with intent.</strong> Ask for a short video, bill/invoice if relevant, and reason for selling. Use Make Offer with a realistic number and a suggested meetup window. Avoid sharing your home address in the first message.</p>
                    <p><strong class="text-ink-800">Meetup hygiene.</strong> Prefer daytime public places: mall atriums, metro exits, society gates, busy markets. Tell a friend where you are going. For vehicles and phones, bring a knowledgeable friend. Test the item before paying.</p>
                    <p><strong class="text-ink-800">Payment hygiene.</strong> Prefer UPI that you can confirm in your own banking app, or cash counted in person. Do not trust screenshots. Do not “refund an overpayment”. Do not pay booking fees to courier unknown items.</p>
                </div>
            </div>
            <div class="panel">
                <h2 class="text-base font-bold text-ink-900">Seller playbook (detailed)</h2>
                <div class="mt-3 space-y-3 text-sm leading-relaxed text-ink-500">
                    <p><strong class="text-ink-800">Keep phone private by default.</strong> Chat first. Enable Show phone only when you are comfortable with calls/WhatsApp. You can turn it off again anytime in Settings → Privacy.</p>
                    <p><strong class="text-ink-800">Control the meetup.</strong> Suggest public spots near you. Do not invite strangers home for a first meeting if you feel unsure. For high-value items, meet where CCTV is common.</p>
                    <p><strong class="text-ink-800">Confirm money before handover.</strong> Watch the credit land in your bank/UPI app. Fake SMS and edited screenshots are common. Beware buyers who insist on odd payment apps or gift cards.</p>
                    <p><strong class="text-ink-800">Protect identity documents.</strong> Never send Aadhaar/PAN photos “for verification”. Tan Tan will not ask for those in chat. After a sale, mark the ad Sold so you stop getting messages.</p>
                    <p><strong class="text-ink-800">Use Block + Report.</strong> Harassment, threats, phishing links, or pressure to move to unknown apps should be blocked and reported with a reason so moderators can act.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section border-b border-line">
        <div class="site-container">
            <h2 class="section-title">Scam patterns (learn the names)</h2>
            <p class="mt-2 text-sm text-ink-500">If a chat matches one of these, stop engaging and report.</p>
            <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Fake payment screenshots', 'Buyer shows a “successful” UPI image but money never arrives. Always verify in your own app.'],
                    ['Overpayment & refund', 'Buyer “accidentally” pays extra and asks you to return the difference. Refuse — classic fraud.'],
                    ['Phishing / fake verify pages', 'Links that look like Tan Tan or bank pages asking for OTP. Stay in the official app.'],
                    ['Courier + advance', 'Pay a booking amount for an item you have not seen, shipped by a “trusted courier”. Walk away.'],
                    ['Off-app pressure', 'Urgent “only Telegram / only this link” before inspection. Legitimate buyers can continue in Tan Tan chat.'],
                    ['Clone / stolen photos', 'Same images across cities. Ask for a timestamped photo with today’s handwritten note.'],
                    ['Remote job pivot', 'Listing chat turns into work-from-home or lottery fees. Report and block.'],
                    ['KYC harvesting', 'Requests for Aadhaar/PAN/selfie “to unlock payment”. Never send.'],
                    ['Too-good urgent sales', 'Flagship phones at absurd prices with “flight tonight” urgency. Treat as high risk.'],
                ] as $item)
                    <article class="rounded-lg bg-surface p-3.5 ring-1 ring-line">
                        <h3 class="text-sm font-bold text-ink-900">{{ $item[0] }}</h3>
                        <p class="mt-1.5 text-xs leading-relaxed text-ink-500 sm:text-sm">{{ $item[1] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section border-b border-line bg-surface">
        <div class="site-container dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
            <h2 class="section-title text-ink-900">Category-specific notes</h2>
            <p><strong class="text-ink-800">Vehicles:</strong> Check RC, insurance, service history; prefer a cold start; never transfer full amount before seeing the vehicle. Meet at RTO-adjacent or busy service areas when possible.</p>
            <p><strong class="text-ink-800">Phones & laptops:</strong> Verify IMEI/serial, activation locks, factory reset, and that chargers are original. Run call/camera/mic tests on the spot.</p>
            <p><strong class="text-ink-800">Property:</strong> Never pay token amounts to unknown people before visiting. Verify ownership/brokerage paperwork offline. Tan Tan does not hold rent deposits.</p>
            <p><strong class="text-ink-800">Jobs:</strong> Never pay a registration fee for a job offer. Interview in known offices or verified video calls. Report fee-for-job scams.</p>
            <p><strong class="text-ink-800">Pets:</strong> Follow local welfare laws; ask for vaccination records; report illegal wildlife trade.</p>
        </div>
    </section>

    <section class="section border-b border-line bg-surface">
        <div class="site-container grid gap-6 lg:grid-cols-2">
            <div class="dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
                <h2 class="section-title text-ink-900">Meetup checklist (print this in your head)</h2>
                <ul class="list-disc space-y-2 pl-5">
                    <li>Public place, daytime, tell a friend where you are going.</li>
                    <li>Inspect the item before unlocking UPI or handing cash.</li>
                    <li>For phones: IMEI, locks, call/camera test; for vehicles: RC + cold start.</li>
                    <li>For rentals: never pay deposit before viewing; verify who collects money.</li>
                    <li>Keep Tan Tan chat open with the agreed price written clearly.</li>
                    <li>Walk away from pressure, “courier COD”, or “pay advance for hold”.</li>
                    <li>If something feels wrong, leave — report later with screenshots.</li>
                </ul>
            </div>
            <div class="dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
                <h2 class="section-title text-ink-900">What Tan Tan will / will not do</h2>
                <p><strong class="text-ink-800">We will:</strong> mask phones by default, take reports seriously, hide repeatedly flagged ads, moderate prohibited items, and help with account access / package activation issues.</p>
                <p><strong class="text-ink-800">We will not:</strong> reverse cash or UPI you sent directly to another person, send a courier to inspect items for you, or guarantee that every verified-looking profile is perfect. Your judgement at meetup still matters.</p>
                <p>Read <a href="{{ route('site.buyers') }}" class="font-semibold text-primary-500">Buyer’s guide</a>, <a href="{{ route('site.sellers') }}" class="font-semibold text-primary-500">Seller’s playbook</a>, and <a href="{{ route('site.terms') }}" class="font-semibold text-primary-500">Terms</a> alongside this page.</p>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="site-container">
            <h2 class="section-title mb-3">How Tan Tan helps inside the product</h2>
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    'Safety tips card on listing detail',
                    'Safety banner at top of chat threads',
                    'Report ad / report user with reasons',
                    'Block user to stop further messages',
                    'Phone masking until seller opts in',
                    'Verified badge & seller reviews',
                    'Moderation queue for pending ads',
                    'Auto-hide after repeated reports',
                    'Public Delete Account page (store compliance)',
                ] as $line)
                    <div class="rounded-lg bg-trust-100/60 px-3 py-2.5 text-sm font-medium text-ink-800 ring-1 ring-trust-100">{{ $line }}</div>
                @endforeach
            </div>
            <p class="mt-4 text-sm leading-relaxed text-ink-500">
                Keep chat screenshots if something goes wrong. Email <a href="mailto:support@tantan.app" class="font-semibold text-primary-500">support@tantan.app</a> or use
                <a href="{{ route('site.contact') }}" class="font-semibold text-primary-500">Contact</a>.
                For emergencies, call local police / helplines first — then inform Tan Tan.
            </p>
        </div>
    </section>

    @include('partials.cta-band', [
        'heading' => 'Trade smart. Stay local.',
        'text' => 'Read the FAQ if you are unsure whether an item or payment pattern is allowed.',
        'primaryHref' => route('site.contact'),
        'primaryLabel' => 'Contact support',
        'secondaryHref' => route('site.faq'),
        'secondaryLabel' => 'FAQ',
    ])
@endsection
