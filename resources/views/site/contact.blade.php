@extends('layouts.site')

@section('content')
    @include('partials.page-hero', [
        'heading' => 'Contact Tan Tan',
        'lead' => 'Account help, listing issues, safety reports, payments, partnerships or press. Include phone, listing ID and screenshots for faster replies. Mon–Sat 10:00–19:00 IST; safety reports are prioritised.',
        'meta' => '<span class="stat-pill">support@tantan.app</span><span class="stat-pill">hello@tantan.app</span><span class="stat-pill">Mon–Sat 10–19 IST</span>',
    ])

    <section class="section-tight border-b border-line bg-surface">
        <div class="site-container grid gap-4 lg:grid-cols-3">
            @foreach ([
                ['Faster replies', 'Send registered +91 phone, listing ID, payment ID (if any), and screenshots of the error or chat. Vague “app not working” messages take longer.'],
                ['Safety first', 'Use Report in-app for scam listings, then email if urgent. Contact local police for theft or threats — Tan Tan cannot reverse cash/UPI sent to another person.'],
                ['Not for negotiation', 'Support will not haggle prices on your behalf or mediate every offline disagreement. We can review policy abuse, payment activation failures and account access.'],
            ] as $tip)
                <div class="rounded-lg bg-canvas p-4 ring-1 ring-line">
                    <h2 class="text-sm font-bold text-ink-900">{{ $tip[0] }}</h2>
                    <p class="mt-2 text-xs leading-relaxed text-ink-500">{{ $tip[1] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="section">
        <div class="site-container grid gap-4 lg:grid-cols-12">
            <aside class="space-y-2.5 lg:col-span-4">
                <div class="panel text-xs text-ink-500">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-ink-400">Channels</p>
                    <ul class="mt-2 space-y-2.5">
                        <li><strong class="text-ink-900">In-app</strong> — Profile → Help & Support (best when you are signed in)</li>
                        <li><strong class="text-ink-900">Email</strong> — <a class="text-primary-500" href="mailto:support@tantan.app">support@tantan.app</a></li>
                        <li><strong class="text-ink-900">Privacy</strong> — <a class="text-primary-500" href="mailto:privacy@tantan.app">privacy@tantan.app</a></li>
                        <li><strong class="text-ink-900">Legal</strong> — <a class="text-primary-500" href="mailto:legal@tantan.app">legal@tantan.app</a></li>
                        <li><strong class="text-ink-900">Partners / press</strong> — <a class="text-primary-500" href="mailto:hello@tantan.app">hello@tantan.app</a></li>
                    </ul>
                </div>
                <div class="rounded-lg bg-primary-50 p-3.5 text-xs leading-relaxed text-ink-600 ring-1 ring-primary-100">
                    Urgent safety issues: use Report in-app and contact local authorities if you are in danger or lost money to a crime. Do not share OTPs with anyone claiming to be Tan Tan support.
                </div>
                <div class="panel text-xs text-ink-500">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-ink-400">Self-serve first</p>
                    <ul class="mt-2 space-y-1.5">
                        <li><a class="font-semibold text-primary-500" href="{{ route('site.faq') }}">FAQ</a> — accounts, ads, packages, privacy</li>
                        <li><a class="font-semibold text-primary-500" href="{{ route('site.safety') }}">Safety tips</a> — scam patterns</li>
                        <li><a class="font-semibold text-primary-500" href="{{ route('site.delete-account') }}">Delete account</a> — store-compliant steps</li>
                        <li><a class="font-semibold text-primary-500" href="{{ route('site.packages') }}">Packages</a> — boost behaviour</li>
                    </ul>
                </div>
            </aside>

            <div class="lg:col-span-8">
                @if (session('success'))
                    <div class="mb-3 rounded-lg bg-trust-100 px-3 py-2.5 text-xs text-ink-900 ring-1 ring-trust-500/30">{{ session('success') }}</div>
                @endif
                <form method="post" action="{{ route('site.contact.submit') }}" class="panel space-y-3">
                    @csrf
                    <p class="text-xs text-ink-500">Fields marked required help us route your ticket. We do not use this form for marketing spam.</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="name" class="mb-1 block text-xs font-medium text-ink-700">Name</label>
                            <input id="name" name="name" value="{{ old('name') }}" required class="min-h-11 w-full rounded-sm border border-line bg-canvas px-3 text-sm outline-none transition duration-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-100" />
                            @error('name')<p class="mt-1 text-[11px] text-danger-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="mb-1 block text-xs font-medium text-ink-700">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required class="min-h-11 w-full rounded-sm border border-line bg-canvas px-3 text-sm outline-none transition duration-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-100" />
                            @error('email')<p class="mt-1 text-[11px] text-danger-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="phone" class="mb-1 block text-xs font-medium text-ink-700">Phone (+91)</label>
                            <input id="phone" name="phone" value="{{ old('phone') }}" placeholder="10-digit mobile" class="min-h-11 w-full rounded-sm border border-line bg-canvas px-3 text-sm outline-none transition duration-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-100" />
                        </div>
                        <div>
                            <label for="topic" class="mb-1 block text-xs font-medium text-ink-700">Topic</label>
                            <select id="topic" name="topic" required class="min-h-11 w-full rounded-sm border border-line bg-canvas px-3 text-sm outline-none transition duration-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-100">
                                @foreach (['Account & login', 'Listing / moderation', 'Chat & safety', 'Payments & packages', 'Privacy / delete account', 'Partnership / press', 'Other'] as $topic)
                                    <option @selected(old('topic') === $topic)>{{ $topic }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="message" class="mb-1 block text-xs font-medium text-ink-700">Message</label>
                        <textarea id="message" name="message" rows="7" required placeholder="What happened? What did you expect? Listing ID / payment ID if relevant." class="w-full rounded-sm border border-line bg-canvas px-3 py-2 text-sm outline-none transition duration-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-100">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1 text-[11px] text-danger-500">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="btn-primary">Send message</button>
                </form>
            </div>
        </div>
    </section>
@endsection
