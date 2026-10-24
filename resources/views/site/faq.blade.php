@php
    $groups = \App\Support\FaqCopy::groups();
    $popular = \App\Support\FaqCopy::popular();
    $answerCount = \App\Support\FaqCopy::answerCount();
    $topicCount = count($groups);
@endphp

@extends('layouts.site')

@section('content')
    @include('partials.page-hero', [
        'heading' => 'Frequently asked questions',
        'lead' => 'Deep answers for real Tan Tan usage in Indian cities — accounts, posting, photos, chat, offers, safety, packages, privacy and troubleshooting. Not one-line stubs.',
        'image' => 'images/site/hero-chat.jpg',
        'imageCaption' => 'Ask here first — then Contact with listing ID if you still need a human',
        'actions' => '<a href="'.route('site.contact').'" class="btn-primary">Contact support</a><a href="'.route('site.safety').'" class="btn-ghost">Safety tips</a><a href="'.route('site.how').'" class="btn-secondary">How it works</a>',
        'meta' => '<span class="stat-pill">'.$answerCount.' answers</span><span class="stat-pill">'.$topicCount.' topics</span><span class="stat-pill">EN guides</span><span class="stat-pill">India-first</span>',
    ])

    @include('partials.stats-strip', ['items' => [
        [(string) $answerCount, 'Long-form answers'],
        [(string) $topicCount, 'Topic groups'],
        ['2', 'Languages in app'],
        ['0', '₹ to browse & chat'],
    ]])

    <section class="section border-b border-line">
        <div class="site-container grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <p class="eyebrow">How to use this page</p>
                <h2 class="section-title mt-1">Search, jump, then open</h2>
            </div>
            <div class="dense-prose space-y-3 text-sm leading-relaxed text-ink-500 lg:col-span-8 sm:text-[15px]">
                <p>Use the search box to filter questions instantly. Or jump via the topic chips / sticky sidebar. Open any question for multi-paragraph answers and bullet checklists where they help.</p>
                <p>If your issue is about a <strong class="text-ink-800">specific listing or payment</strong>, Contact support with listing ID, registered phone and screenshots — FAQ cannot see your account. For scams after an offline meetup, read <a href="{{ route('site.safety') }}" class="font-semibold text-primary-500">Safety tips</a> and involve local police when money or goods were stolen.</p>
            </div>
        </div>
    </section>

    <section class="section-tight border-b border-line bg-surface">
        <div class="site-container">
            <div class="mb-3 flex items-end justify-between gap-3">
                <div>
                    <p class="eyebrow">Start here</p>
                    <h2 class="section-title mt-1">Most asked</h2>
                </div>
                <p class="text-[11px] font-medium text-ink-400">Tap a card → jump to full answer</p>
            </div>
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($popular as $pop)
                    @php
                        $anchor = null;
                        foreach ($groups as $gName => $gData) {
                            foreach ($gData['items'] as $idx => $item) {
                                if ($item['q'] === $pop['q']) {
                                    $gi = array_search($gName, array_keys($groups), true);
                                    $anchor = 'faq-'.$gi.'-'.$idx;
                                    break 2;
                                }
                            }
                        }
                    @endphp
                    <a href="#{{ $anchor ?? 'faq-0' }}" class="tile !p-3.5 transition duration-300 hover:-translate-y-0.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-primary-500">{{ $pop['topic'] }}</p>
                        <h3 class="mt-1.5 text-sm font-bold leading-snug text-ink-900">{{ $pop['q'] }}</h3>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section
        class="section"
        x-data="{
            q: '',
            match(text) {
                const needle = this.q.trim().toLowerCase();
                if (!needle) return true;
                return String(text || '').toLowerCase().includes(needle);
            },
            groupVisible(name, blob) {
                const needle = this.q.trim().toLowerCase();
                if (!needle) return true;
                return String(name || '').toLowerCase().includes(needle)
                    || String(blob || '').toLowerCase().includes(needle);
            }
        }"
    >
        <div class="site-container">
            <div class="faq-search panel mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                <label for="faq-q" class="sr-only">Search FAQ</label>
                <div class="relative min-w-0 flex-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z"/></svg>
                    <input
                        id="faq-q"
                        type="search"
                        x-model="q"
                        placeholder="Search questions — e.g. OTP, Featured, delete account, IMEI…"
                        class="min-h-12 w-full rounded-sm border border-line bg-canvas py-2 pl-11 pr-3 text-sm outline-none transition duration-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-100"
                    />
                </div>
                <p class="shrink-0 text-xs text-ink-400" x-text="q.trim() ? 'Filtering results…' : '{{ $answerCount }} answers · {{ $topicCount }} topics'"></p>
            </div>

            <div class="grid gap-8 lg:grid-cols-12">
                <aside class="hidden lg:col-span-3 lg:block">
                    <div class="faq-toc sticky top-24 space-y-1 rounded-xl bg-surface p-4 ring-1 ring-line">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-ink-400">Topics</p>
                        <nav class="mt-2 space-y-0.5" aria-label="FAQ topics">
                            @foreach ($groups as $i => $group)
                                <a href="#faq-{{ $loop->index }}" class="block rounded-md px-2.5 py-2 text-xs font-semibold text-ink-600 transition duration-300 hover:bg-primary-50 hover:text-primary-600">
                                    {{ $i }}
                                    <span class="ml-1 font-medium text-ink-400">{{ count($group['items']) }}</span>
                                </a>
                            @endforeach
                        </nav>
                        <div class="mt-4 border-t border-line pt-3 text-[11px] leading-relaxed text-ink-400">
                            Still stuck?
                            <a href="{{ route('site.contact') }}" class="font-semibold text-primary-500">Contact</a>
                            with listing ID.
                        </div>
                    </div>
                </aside>

                <div class="min-w-0 space-y-10 lg:col-span-9">
                    <div class="flex flex-wrap gap-1.5 lg:hidden">
                        @foreach ($groups as $i => $group)
                            <a href="#faq-{{ $loop->index }}" class="stat-pill hover:border-primary-500 hover:text-primary-600">{{ $i }}</a>
                        @endforeach
                    </div>

                    @foreach ($groups as $groupName => $group)
                        @php
                            $gi = $loop->index;
                            $blob = $group['intro'].' ';
                            foreach ($group['items'] as $it) {
                                $blob .= $it['q'].' '.implode(' ', $it['a']).' '.implode(' ', $it['bullets'] ?? []).' ';
                            }
                        @endphp
                        <div
                            id="faq-{{ $gi }}"
                            class="scroll-mt-28"
                            x-show="groupVisible({{ \Illuminate\Support\Js::from($groupName) }}, {{ \Illuminate\Support\Js::from($blob) }})"
                        >
                            <div class="mb-3 border-b border-line pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-md bg-primary-100 text-xs font-bold text-primary-600">{{ str_pad((string) ($gi + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                    <h2 class="text-xl font-bold text-ink-900">{{ $groupName }}</h2>
                                    <span class="ml-auto text-[11px] font-medium text-ink-400">{{ count($group['items']) }} answers</span>
                                </div>
                                <p class="mt-2 text-sm leading-relaxed text-ink-500">{{ $group['intro'] }}</p>
                            </div>

                            <div class="space-y-2.5">
                                @foreach ($group['items'] as $item)
                                    @php
                                        $itemBlob = $item['q'].' '.implode(' ', $item['a']).' '.implode(' ', $item['bullets'] ?? []);
                                    @endphp
                                    <details
                                        id="faq-{{ $gi }}-{{ $loop->index }}"
                                        class="faq-item group scroll-mt-28 rounded-xl bg-surface ring-1 ring-line open:shadow-[var(--shadow-e1)] open:ring-primary-100"
                                        x-show="match({{ \Illuminate\Support\Js::from($itemBlob) }})"
                                    >
                                        <summary class="flex cursor-pointer list-none items-start justify-between gap-3 px-4 py-4 text-left sm:px-5 [&::-webkit-details-marker]:hidden">
                                            <span class="flex min-w-0 gap-3">
                                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-canvas text-[10px] font-bold text-ink-500 ring-1 ring-line group-open:bg-primary-500 group-open:text-white group-open:ring-primary-500">Q</span>
                                                <span class="text-sm font-semibold leading-snug text-ink-900 sm:text-[15px]">{{ $item['q'] }}</span>
                                            </span>
                                            <svg class="mt-1 h-5 w-5 shrink-0 text-ink-400 transition duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                        </summary>
                                        <div class="faq-answer border-t border-line px-4 py-4 sm:px-5 sm:pl-[3.25rem]">
                                            <div class="space-y-3 text-sm leading-relaxed text-ink-500 sm:text-[15px]">
                                                @foreach ($item['a'] as $para)
                                                    <p>{{ $para }}</p>
                                                @endforeach
                                            </div>
                                            @if (! empty($item['bullets']))
                                                <ul class="mt-4 space-y-2 rounded-lg bg-canvas px-4 py-3 ring-1 ring-line">
                                                    @foreach ($item['bullets'] as $bullet)
                                                        <li class="flex gap-2 text-sm text-ink-700">
                                                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-primary-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                                            <span>{{ $bullet }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                            <p class="mt-4 text-[11px] text-ink-400">
                                                Related:
                                                <a href="{{ route('site.how') }}" class="font-semibold text-primary-500">How it works</a>
                                                ·
                                                <a href="{{ route('site.safety') }}" class="font-semibold text-primary-500">Safety</a>
                                                ·
                                                <a href="{{ route('site.contact') }}" class="font-semibold text-primary-500">Contact</a>
                                            </p>
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="section border-t border-line bg-surface">
        <div class="site-container">
            <p class="eyebrow">Guides</p>
            <h2 class="section-title mt-1">FAQ ke baad yeh pages padho</h2>
            <p class="mt-1 max-w-2xl text-sm text-ink-500">Full walkthroughs — better than stuffing every detail into one accordion.</p>
            <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['How it works', 'Onboarding → Home → Post → Chat → Boosts', route('site.how')],
                    ['Buyer’s guide', 'Filters, offers, meetup checklist', route('site.buyers')],
                    ['Seller’s playbook', 'Photos, pricing, My Ads, boosts', route('site.sellers')],
                    ['Safety tips', 'Scam patterns for phones, rentals, jobs', route('site.safety')],
                    ['Categories', 'Fields & tips per category tree', route('site.categories')],
                    ['Packages', 'Bump, Featured, Top Ad + invoices', route('site.packages')],
                    ['Privacy', 'What we store & your choices', route('site.privacy')],
                    ['Terms', 'Marketplace rules & packages billing', route('site.terms')],
                    ['Delete account', 'In-app OTP or email fallback', route('site.delete-account')],
                ] as $g)
                    <a href="{{ $g[2] }}" class="tile !p-3.5 transition duration-300 hover:-translate-y-0.5">
                        <h3 class="text-sm font-bold text-ink-900">{{ $g[0] }}</h3>
                        <p class="mt-1 text-xs text-ink-500">{{ $g[1] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section border-t border-line">
        <div class="site-container grid gap-4 lg:grid-cols-2">
            <div class="panel">
                <h2 class="text-base font-bold text-ink-900">Still need a human?</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink-500">
                    <a href="mailto:support@tantan.app" class="font-semibold text-primary-500">support@tantan.app</a>
                    · <a href="{{ route('site.contact') }}" class="font-semibold text-primary-500">Contact form</a>
                    · Partners/press: <a href="mailto:hello@tantan.app" class="font-semibold text-primary-500">hello@tantan.app</a>
                </p>
                <p class="mt-2 text-sm text-ink-500">Mon–Sat, 10:00–19:00 IST. Safety reports are prioritised. Include phone + listing ID + screenshots.</p>
            </div>
            <div class="panel">
                <h2 class="text-base font-bold text-ink-900">Prefer the app?</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink-500">
                    Profile → Help & Support keeps your account context attached. Download Tan Tan for posting, chat and payments — this site stays the long-form knowledge base.
                </p>
                <a href="{{ route('site.download') }}" class="mt-3 inline-flex text-sm font-semibold text-primary-500">Get the app →</a>
            </div>
        </div>
    </section>

    @include('partials.cta-band', [
        'heading' => 'Question solved? Go trade.',
        'text' => 'Browse near you or post a free ad — same account for buy and sell.',
        'primaryHref' => route('site.download'),
        'primaryLabel' => 'Get the app',
        'secondaryHref' => route('site.contact'),
        'secondaryLabel' => 'Contact form',
    ])
@endsection
