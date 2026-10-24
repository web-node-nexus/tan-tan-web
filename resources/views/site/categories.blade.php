@extends('layouts.site')

@section('content')
    @include('partials.page-hero', [
        'heading' => 'Categories, subcategories & fields',
        'lead' => 'Tan Tan organises India’s everyday trade into clear categories. Each one unlocks dynamic fields so a car search feels nothing like a sofa search — and serious buyers can filter instead of doom-scrolling.',
        'image' => 'images/listings/scooter.jpg',
        'actions' => '<a href="'.route('site.download').'" class="btn-primary">Browse in app</a><a href="'.route('site.sellers').'" class="btn-secondary">Seller tips</a><a href="'.route('site.buyers').'" class="btn-ghost">Buyer tips</a>',
    ])

    <section class="section-tight border-b border-line bg-surface">
        <div class="site-container dense-prose text-sm leading-relaxed text-ink-500">
            <p>
                Categories are managed from the Tan Tan admin and can grow over time. The list below matches the product direction for launch: vehicles, property, mobiles, electronics, furniture, fashion, jobs, services, pets, books & hobbies, kids, and other. Subcategories and fields shown are examples — exact options ship with seed data and can be edited without an app release.
            </p>
            <p>
                <strong class="text-ink-800">Why this matters:</strong> when sellers fill structured fields, buyers can run precise filters (“Petrol hatchback under ₹4 L within 10 km”, “2 BHK furnished under ₹25,000”). That keeps the feed useful as inventory grows in your city.
            </p>
        </div>
    </section>

    <section class="section">
        <div class="site-container space-y-4">
            @foreach (\App\Support\SiteCopy::categoriesDetailed() as $cat)
                <article class="overflow-hidden rounded-lg bg-surface ring-1 ring-line">
                    <div class="grid lg:grid-cols-12">
                        <div class="border-b border-line bg-canvas p-4 lg:col-span-3 lg:border-b-0 lg:border-r">
                            <h2 class="text-lg font-bold text-ink-900">{{ $cat['name'] }}</h2>
                            <p class="mt-2 text-xs font-semibold uppercase tracking-wider text-ink-400">Subcategories</p>
                            <p class="mt-1 text-sm text-ink-600">{{ $cat['subs'] }}</p>
                        </div>
                        <div class="space-y-3 p-4 lg:col-span-9">
                            <p class="text-sm leading-relaxed text-ink-500">{{ $cat['blurb'] }}</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-md bg-canvas p-3 ring-1 ring-line">
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-primary-500">Example fields</p>
                                    <p class="mt-1 text-sm text-ink-700">{{ $cat['fields'] }}</p>
                                </div>
                                <div class="rounded-md bg-primary-50/80 p-3 ring-1 ring-primary-100">
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-primary-600">Meetup tips</p>
                                    <p class="mt-1 text-sm text-ink-700">{{ $cat['tips'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="section border-t border-line bg-surface">
        <div class="site-container">
            <h2 class="section-title">Posting checklist that works in every category</h2>
            <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    'Use a clear title with brand/model when relevant (avoid ALL CAPS spam).',
                    'Add at least 4 photos: hero angle + defects + accessories + scale/context.',
                    'Fill every category field you can — empty fields hide you from filters.',
                    'Set an honest price; check 3–5 similar active ads in your city first.',
                    'Choose the right area; wrong locality wastes everyone’s time.',
                    'Write what is included (charger, bill, spare key) and what is not.',
                    'Prefer negotiable only if you truly have room; fixed if you do not.',
                    'Reply to chats the same day while interest is hot.',
                    'Mark as sold the moment the deal closes.',
                ] as $line)
                    <div class="rounded-lg bg-canvas px-3 py-2.5 text-sm text-ink-600 ring-1 ring-line">{{ $line }}</div>
                @endforeach
            </div>
            <p class="mt-4 text-sm text-ink-500">
                Need visibility after the listing is solid?
                <a href="{{ route('site.packages') }}" class="font-semibold text-primary-500">Compare boost packages</a>.
            </p>
        </div>
    </section>

    @include('partials.cta-band', [
        'heading' => 'See categories come alive in the app',
        'text' => 'Home row + full category grid + field-aware filters.',
    ])
@endsection
