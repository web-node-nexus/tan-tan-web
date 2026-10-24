@extends('layouts.site')

@section('content')
    @include('partials.page-hero', [
        'heading' => 'Privacy policy',
        'lead' => 'Last updated: 16 October 2026. What we collect, why we collect it, how long we keep it, who we share it with, and the choices you have — written for Tan Tan users in India.',
        'meta' => '<span class="stat-pill">Phone masked by default</span><span class="stat-pill">No data broker sales</span><span class="stat-pill">Delete anytime</span>',
    ])

    <section class="section-tight border-b border-line bg-surface">
        <div class="site-container max-w-3xl dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
            <p>We designed Tan Tan so local trade does not require publishing your phone number to the entire internet. Numbers stay masked unless you enable Show phone. Package payments go through a payment partner; we do not store full card numbers on Tan Tan servers.</p>
            <p>Quick links: <a href="{{ route('site.delete-account') }}" class="font-semibold text-primary-500">Delete account steps</a> · <a href="{{ route('site.terms') }}" class="font-semibold text-primary-500">Terms</a> · <a href="mailto:privacy@tantan.app" class="font-semibold text-primary-500">privacy@tantan.app</a> · <a href="{{ route('site.contact') }}" class="font-semibold text-primary-500">Contact form</a>.</p>
        </div>
    </section>

    <article class="site-container max-w-3xl py-8 sm:py-10">
        <div class="mb-6 grid gap-2 sm:grid-cols-3">
            @foreach ([
                ['Masked phone', 'Call/WhatsApp only if you opt in'],
                ['Purpose-limited', 'Data used to run marketplace + safety'],
                ['Your control', 'Edit, revoke location, delete account'],
            ] as $card)
                <div class="rounded-lg bg-trust-100/60 px-4 py-3 ring-1 ring-trust-100">
                    <h3 class="text-sm font-bold text-ink-900">{{ $card[0] }}</h3>
                    <p class="mt-1 text-xs text-ink-500">{{ $card[1] }}</p>
                </div>
            @endforeach
        </div>

        <div class="space-y-8 rounded-xl bg-surface p-6 shadow-[var(--shadow-e1)] ring-1 ring-line sm:p-10">
            @foreach (\App\Support\SiteCopy::privacySections() as $section)
                <section>
                    <h2 class="text-lg font-bold text-ink-900">{{ $section[0] }}</h2>
                    <div class="mt-3 space-y-3 text-sm leading-relaxed text-ink-500 sm:text-[15px]">
                        @foreach ($section[1] as $para)
                            <p>{{ $para }}</p>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </article>
@endsection
