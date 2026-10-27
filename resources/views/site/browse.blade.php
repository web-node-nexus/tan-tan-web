@extends('layouts.site')

@section('content')
    <section class="border-b border-line bg-surface">
        <div class="site-container py-8">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-primary-500">Marketplace</p>
            <h1 class="mt-1 text-3xl font-extrabold text-ink-900">Browse ads, jobs and services</h1>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-ink-500">Same listings as the Tan Tan app. Chat, apply and post from the app. Search here before you open it.</p>

            <form method="get" action="{{ route('site.browse') }}" class="mt-6 grid gap-3 rounded-lg bg-canvas p-4 ring-1 ring-line md:grid-cols-12">
                <label class="md:col-span-4">
                    <span class="text-xs font-semibold text-ink-500">Search</span>
                    <input name="q" value="{{ $filters['q'] }}" placeholder="iPhone, electrician, sales job" class="mt-1 h-11 w-full rounded-md border border-line bg-surface px-3 text-sm text-ink-900">
                </label>
                <label class="md:col-span-3">
                    <span class="text-xs font-semibold text-ink-500">Category</span>
                    <select name="category_id" class="mt-1 h-11 w-full rounded-md border border-line bg-surface px-3 text-sm text-ink-900">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) $filters['category_id'] === $category->id)>{{ $category->name_en }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="md:col-span-3">
                    <span class="text-xs font-semibold text-ink-500">City</span>
                    <select name="city_id" class="mt-1 h-11 w-full rounded-md border border-line bg-surface px-3 text-sm text-ink-900">
                        <option value="">All cities</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}" @selected((int) $filters['city_id'] === $city->id)>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="md:col-span-2">
                    <span class="text-xs font-semibold text-ink-500">Sort</span>
                    <select name="sort" class="mt-1 h-11 w-full rounded-md border border-line bg-surface px-3 text-sm text-ink-900">
                        @foreach (['newest' => 'Newest', 'price_asc' => 'Price ↑', 'price_desc' => 'Price ↓', 'featured' => 'Featured'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="flex flex-wrap items-center gap-2 md:col-span-8">
                    @foreach (['' => 'All', 'product' => 'Products', 'job' => 'Jobs', 'service' => 'Services'] as $value => $label)
                        <label class="inline-flex h-10 cursor-pointer items-center rounded-full border px-4 text-sm font-semibold {{ $filters['kind'] === $value ? 'border-primary-500 bg-primary-50 text-primary-600' : 'border-line bg-surface text-ink-700' }}">
                            <input type="radio" name="kind" value="{{ $value }}" class="sr-only" @checked($filters['kind'] === $value)>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <div class="flex gap-2 md:col-span-4">
                    <input name="min_price" value="{{ $filters['min_price'] }}" inputmode="numeric" placeholder="Min ₹" class="h-11 w-full rounded-md border border-line bg-surface px-3 text-sm">
                    <input name="max_price" value="{{ $filters['max_price'] }}" inputmode="numeric" placeholder="Max ₹" class="h-11 w-full rounded-md border border-line bg-surface px-3 text-sm">
                </div>
                <div class="flex flex-wrap items-center gap-4 md:col-span-12">
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-ink-700">
                        <input type="checkbox" name="negotiable" value="1" @checked($filters['negotiable'] === '1') class="h-4 w-4 accent-[#ff5a1f]">
                        Negotiable only
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-ink-700">
                        <input type="checkbox" name="price_drop" value="1" @checked($filters['price_drop'] === '1') class="h-4 w-4 accent-[#ff5a1f]">
                        Price dropped
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-ink-700">
                        <input type="checkbox" name="same_day" value="1" @checked($filters['same_day'] === '1') class="h-4 w-4 accent-[#ff5a1f]">
                        Same-day visit
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-ink-700">
                        <input type="checkbox" name="brings_material" value="1" @checked($filters['brings_material'] === '1') class="h-4 w-4 accent-[#ff5a1f]">
                        Brings material
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-ink-700">
                        <input type="checkbox" name="home_visit" value="1" @checked($filters['home_visit'] === '1') class="h-4 w-4 accent-[#ff5a1f]">
                        Home visit
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-ink-700">
                        <input type="checkbox" name="available_today" value="1" @checked($filters['available_today'] === '1') class="h-4 w-4 accent-[#ff5a1f]">
                        Available today
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-ink-700">
                        <input type="checkbox" name="verified" value="1" @checked($filters['verified'] === '1') class="h-4 w-4 accent-[#ff5a1f]">
                        Verified only
                    </label>
                    <select name="move_type" class="h-11 rounded-md border border-line bg-surface px-3 text-sm text-ink-900">
                        <option value="">Any move</option>
                        @foreach (['local' => 'Local move', 'intercity' => 'Intercity move'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['move_type'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="work_mode" class="h-11 rounded-md border border-line bg-surface px-3 text-sm text-ink-900">
                        <option value="">Any work mode</option>
                        @foreach (['office' => 'Office', 'remote' => 'Remote', 'hybrid' => 'Hybrid', 'home' => 'Work from home'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['work_mode'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="experience" class="h-11 rounded-md border border-line bg-surface px-3 text-sm text-ink-900">
                        <option value="">Any experience</option>
                        @foreach (['fresher' => 'Fresher', '1_2' => '1–2 years', '3_5' => '3–5 years', '5_plus' => '5+ years', 'experienced' => 'Experienced'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['experience'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2 md:col-span-12">
                    <button type="submit" class="inline-flex h-11 items-center rounded-md bg-primary-500 px-5 text-sm font-bold text-white">Show results</button>
                    <a href="{{ route('site.browse') }}" class="inline-flex h-11 items-center rounded-md border border-line bg-surface px-5 text-sm font-semibold text-ink-700">Reset</a>
                </div>
            </form>
        </div>
    </section>

    <section class="section">
        <div class="site-container">
            @if ($listings->isEmpty())
                <div class="rounded-lg bg-surface p-8 text-center ring-1 ring-line">
                    <p class="text-lg font-bold text-ink-900">No ads match these filters</p>
                    <p class="mt-2 text-sm text-ink-500">Try another city or category, or post the first ad from the app.</p>
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($listings as $listing)
                        @include('partials.listing-card', ['listing' => $listing])
                    @endforeach
                </div>
                <div class="mt-8">{{ $listings->links() }}</div>
            @endif
        </div>
    </section>
@endsection
