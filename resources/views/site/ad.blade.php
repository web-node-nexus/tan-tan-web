@extends('layouts.site')

@section('content')
    @php
        $images = $listing->images;
        $cover = $images->firstWhere('is_cover', true) ?? $images->first();
        $kind = $listing->category?->kind ?? 'product';
    @endphp
    <article class="section">
        <div class="site-container grid gap-8 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <a href="{{ route('site.browse', ['kind' => $kind === 'product' ? '' : $kind]) }}" class="text-sm font-semibold text-primary-500">← Back to browse</a>
                <div class="mt-4 overflow-hidden rounded-lg bg-canvas ring-1 ring-line">
                    @if ($cover)
                        <img src="{{ $cover->url() }}" alt="{{ $listing->title }}" class="aspect-square w-full object-cover sm:aspect-[4/3]">
                    @else
                        <div class="aspect-square w-full bg-line sm:aspect-[4/3]"></div>
                    @endif
                </div>
                @if ($images->count() > 1)
                    <div class="mt-3 flex gap-2 overflow-x-auto">
                        @foreach ($images as $image)
                            <img src="{{ $image->url() }}" alt="" class="h-16 w-16 rounded-md object-cover ring-1 ring-line">
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="lg:col-span-5">
                <p class="text-3xl font-extrabold text-ink-900">{{ $listing->priceLabel() }}</p>
                <h1 class="mt-2 text-2xl font-bold text-ink-900">{{ $listing->title }}</h1>
                <p class="mt-2 text-sm text-ink-500">{{ $listing->city?->name ?? $listing->location_label }} · {{ $listing->views_count }} views</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if ($listing->category)
                        <span class="rounded-full px-3 py-1 text-xs font-semibold" style="background: {{ $listing->category->tint }}">{{ $listing->category->localizedName() }}</span>
                    @endif
                    @if ($kind === 'product')
                        <span class="rounded-full bg-canvas px-3 py-1 text-xs font-semibold text-ink-700 ring-1 ring-line">{{ str_replace('_', ' ', $listing->condition) }}</span>
                    @endif
                </div>
                @if (is_array($listing->attributes) && $listing->attributes !== [])
                    <dl class="mt-4 grid grid-cols-2 gap-2">
                        @foreach ($listing->attributes as $key => $value)
                            <div class="rounded-md bg-canvas px-3 py-2 ring-1 ring-line">
                                <dt class="text-[11px] font-bold uppercase tracking-wider text-ink-400">{{ str_replace('_', ' ', $key) }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-ink-900">
                                    {{ is_array($value) ? implode(', ', $value) : (is_bool($value) ? ($value ? 'Yes' : 'No') : $value) }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
                <div class="mt-6 rounded-lg bg-surface p-4 ring-1 ring-line">
                    <p class="text-xs font-bold uppercase tracking-wider text-ink-400">{{ $kind === 'job' ? 'Employer' : ($kind === 'service' ? 'Provider' : 'Seller') }}</p>
                    <p class="mt-1 font-bold text-ink-900">{{ $listing->user?->name }}</p>
                    @if ($listing->user?->is_verified)
                        <p class="text-sm text-trust-500">Verified</p>
                    @endif
                </div>
                <a href="{{ route('site.download') }}" class="mt-4 inline-flex h-12 w-full items-center justify-center rounded-md bg-primary-500 text-base font-bold text-white">
                    {{ $kind === 'job' ? 'Apply in the app' : ($kind === 'service' ? 'Contact in the app' : 'Chat in the app') }}
                </a>
                <p class="mt-3 text-xs leading-relaxed text-ink-500">Meet in a public place. Never pay a registration fee or share an OTP.</p>
            </div>
            <div class="lg:col-span-12">
                <h2 class="text-lg font-bold text-ink-900">Description</h2>
                <p class="mt-2 max-w-3xl whitespace-pre-line text-sm leading-relaxed text-ink-700">{{ $listing->description }}</p>
            </div>
        </div>
    </article>
    @if ($similar->isNotEmpty())
        <section class="section border-t border-line">
            <div class="site-container">
                <h2 class="text-lg font-bold text-ink-900">Similar ads</h2>
                <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                    @foreach ($similar as $item)
                        @include('partials.listing-card', ['listing' => $item])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
