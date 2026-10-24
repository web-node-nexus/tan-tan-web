@extends('layouts.site')

@section('content')
    @include('partials.page-hero', [
        'heading' => 'Terms of use',
        'lead' => 'Last updated: 16 October 2026. These Terms govern your use of the Tan Tan apps, website and related services. Please read them carefully before posting, chatting or buying packages.',
        'meta' => '<span class="stat-pill">India law</span><span class="stat-pill">Marketplace platform</span><span class="stat-pill">legal@tantan.app</span>',
    ])

    <section class="section-tight border-b border-line bg-surface">
        <div class="site-container max-w-3xl dense-prose space-y-3 text-sm leading-relaxed text-ink-500">
            <p>Tan Tan is a classifieds marketplace. We provide software so neighbours can list goods, discover nearby deals, chat, and optionally boost visibility. We are <strong class="text-ink-800">not</strong> the buyer or seller of user-listed goods, and we do not hold your inventory. Offline meetups and payments for goods are between users — follow our <a href="{{ route('site.safety') }}" class="font-semibold text-primary-500">Safety tips</a>.</p>
            <p>Related: <a href="{{ route('site.privacy') }}" class="font-semibold text-primary-500">Privacy policy</a> · <a href="{{ route('site.packages') }}" class="font-semibold text-primary-500">Packages</a> · <a href="{{ route('site.delete-account') }}" class="font-semibold text-primary-500">Delete account</a> · <a href="{{ route('site.faq') }}" class="font-semibold text-primary-500">FAQ</a>.</p>
        </div>
    </section>

    <article class="site-container max-w-3xl py-8 sm:py-10">
        <div class="space-y-8 rounded-xl bg-surface p-6 shadow-[var(--shadow-e1)] ring-1 ring-line sm:p-10">
            @foreach (\App\Support\SiteCopy::termsSections() as $section)
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
