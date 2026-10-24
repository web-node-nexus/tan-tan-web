@extends('layouts.site')

@section('content')
    @include('partials.page-hero', [
        'heading' => 'Delete your Tan Tan account',
        'lead' => 'Permanent deletion steps for store compliance and your control — from the app (recommended) or by email if you cannot sign in. Export invoices before you confirm.',
        'meta' => '<span class="stat-pill">OTP confirm in-app</span><span class="stat-pill">~30 day purge target</span><span class="stat-pill">support@tantan.app</span>',
    ])

    <section class="site-container py-8 sm:py-10">
        <div class="rounded-xl bg-ink-900 px-6 py-6 text-white shadow-[var(--shadow-e3)] sm:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-danger-500">Important</p>
            <p class="mt-2 max-w-3xl text-base leading-relaxed text-white/85">Deletion is permanent. Your profile, listings, favourites, saved searches and chat access from that account are removed. Some billing, tax and fraud-prevention logs may remain as described in the Privacy Policy. You cannot undo deletion to “get the same chats back”.</p>
        </div>

        <div class="mt-6 dense-prose max-w-3xl space-y-3 text-sm leading-relaxed text-ink-500">
            <h2 class="section-title text-ink-900">Before you delete</h2>
            <ul class="list-disc space-y-2 pl-5">
                <li>Download any PDF invoices you need from Profile → Packages / Payments.</li>
                <li>Mark active ads as Sold or delete them if you want buyers to stop messaging (deletion will remove them anyway).</li>
                <li>Note that other users keep their own copy of messages they already received.</li>
                <li>If you only want fewer notifications, turn them off in Settings instead of deleting.</li>
                <li>If you lost access to your phone number, use Method B and be ready for identity checks.</li>
            </ul>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl bg-surface p-7 shadow-[var(--shadow-e1)] ring-1 ring-line">
                <span class="inline-flex rounded-full bg-primary-100 px-3 py-1 text-xs font-semibold text-primary-600">Recommended</span>
                <h2 class="mt-4 text-xl font-bold text-ink-900">Method A — In the app</h2>
                <ol class="mt-5 list-decimal space-y-3 pl-5 text-sm leading-relaxed text-ink-500">
                    <li>Sign in to Tan Tan with the account you want to delete.</li>
                    <li>Open Profile → Settings → Delete account.</li>
                    <li>Read the warning about permanent loss of listings and chat access.</li>
                    <li>Confirm with the OTP sent to your registered phone (or the in-app confirmation step shown).</li>
                    <li>You are signed out. Primary deletion runs promptly; residual backups expire on the backup cycle (target within ~30 days for personal stores).</li>
                </ol>
            </div>
            <div class="rounded-xl bg-surface p-7 shadow-[var(--shadow-e1)] ring-1 ring-line">
                <span class="inline-flex rounded-full bg-canvas px-3 py-1 text-xs font-semibold text-ink-500 ring-1 ring-line">Fallback</span>
                <h2 class="mt-4 text-xl font-bold text-ink-900">Method B — By email / web</h2>
                <ol class="mt-5 list-decimal space-y-3 pl-5 text-sm leading-relaxed text-ink-500">
                    <li>Email <a href="mailto:support@tantan.app" class="font-semibold text-primary-500">support@tantan.app</a> or mention deletion on the <a href="{{ route('site.contact') }}" class="font-semibold text-primary-500">contact form</a>.</li>
                    <li>Subject line: <strong class="text-ink-900">Delete account</strong>.</li>
                    <li>Include: full name on the profile, registered +91 phone, email if linked, and city you used most.</li>
                    <li>We verify ownership (we may send an OTP or ask clarifying questions) before deleting.</li>
                    <li>You receive a confirmation email when the request is completed or if we need more proof.</li>
                </ol>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl bg-trust-100/50 p-6 ring-1 ring-trust-100">
                <h3 class="font-semibold text-ink-900">Removed from your account</h3>
                <ul class="mt-3 space-y-1.5 text-sm text-ink-500">
                    <li>Profile name, photo, locale prefs</li>
                    <li>Listings, drafts, favourites</li>
                    <li>Saved / recent searches</li>
                    <li>Push device tokens</li>
                    <li>Chat access from that login</li>
                </ul>
            </div>
            <div class="rounded-xl bg-canvas p-6 ring-1 ring-line">
                <h3 class="font-semibold text-ink-900">May be retained</h3>
                <ul class="mt-3 space-y-1.5 text-sm text-ink-500">
                    <li>Payment invoices (tax / accounting)</li>
                    <li>Fraud & abuse investigation logs</li>
                    <li>Records required by law or dispute</li>
                    <li>Anonymised product analytics</li>
                </ul>
            </div>
            <div class="rounded-xl bg-surface p-6 ring-1 ring-line">
                <h3 class="font-semibold text-ink-900">Store / compliance note</h3>
                <p class="mt-3 text-sm leading-relaxed text-ink-500">Google Play and App Store require a clear path to delete accounts created in-app. This page exists so users (and reviewers) can find those steps without opening the binary. In-app deletion remains the fastest path when you can still sign in.</p>
            </div>
        </div>

        <div class="mt-6 max-w-3xl space-y-3 text-sm leading-relaxed text-ink-500">
            <h2 class="section-title text-ink-900">After deletion</h2>
            <p>You may create a new account later with the same phone number only if our systems allow re-registration and you are not under a ban. Previous chat history and ratings do not transfer. If you deleted by mistake, contact support immediately — restoration is not guaranteed once purge jobs run.</p>
            <p>
                Full detail: <a href="{{ route('site.privacy') }}" class="font-semibold text-primary-500">Privacy policy</a>
                · <a href="{{ route('site.terms') }}" class="font-semibold text-primary-500">Terms</a>
                · <a href="{{ route('site.faq') }}" class="font-semibold text-primary-500">FAQ — Privacy & account deletion</a>
                · <a href="{{ route('site.contact') }}" class="font-semibold text-primary-500">Contact</a>
            </p>
        </div>
    </section>
@endsection
