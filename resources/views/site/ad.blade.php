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
                    @if ($listing->video_path)
                        <video class="aspect-video w-full bg-black" controls playsinline preload="metadata" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($listing->video_path) }}"></video>
                    @endif
                    @if ($cover)
                        <img src="{{ $cover->url() }}" alt="{{ $listing->title }}" class="aspect-square w-full object-cover sm:aspect-[4/3]">
                    @elseif (! $listing->video_path)
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
                    @if ($listing->user?->account_type === 'business' && $listing->user?->business_name)
                        <p class="text-sm font-semibold text-ink-700">Dealer · {{ $listing->user->business_name }}</p>
                    @endif
                    <div class="mt-2 flex flex-wrap gap-2">
                        @if ($listing->user?->phone_verified_at)
                            <span class="rounded-full bg-trust-50 px-2.5 py-1 text-xs font-bold text-trust-700">Phone verified</span>
                        @endif
                        @if ($listing->user?->is_verified)
                            <span class="rounded-full bg-primary-50 px-2.5 py-1 text-xs font-bold text-primary-600">ID verified</span>
                        @endif
                    </div>
                </div>
                <div class="mt-4 rounded-lg bg-canvas p-4 ring-1 ring-line">
                    <p class="text-sm font-bold text-ink-900">Meet in public</p>
                    <ul class="mt-2 list-disc space-y-1 pl-4 text-sm leading-relaxed text-ink-700">
                        <li>Meet in a busy public place, in daylight. Bring someone with you if you can.</li>
                        <li>Check the item in person before you pay anything.</li>
                        <li>Do not pay an advance, token, or delivery fee before you have the item.</li>
                        <li>Never share an OTP, UPI PIN, or bank details.</li>
                    </ul>
                </div>
                @if ($kind === 'job')
                    <div class="mt-4 grid gap-3 rounded-lg bg-surface p-4 ring-1 ring-line">
                        <p class="text-sm font-bold text-ink-900">Application</p>
                        <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Applied · Shortlisted · Interview · Hired</p>
                        <p id="job-status" class="text-sm text-ink-500"></p>
                        <form id="job-form" class="grid gap-2">
                            <select name="experience" class="h-11 rounded-md border border-line px-3">
                                <option value="fresher">Fresher</option>
                                <option value="1_2">1–2 years</option>
                                <option value="3_5">3–5 years</option>
                                <option value="5_plus">5+ years</option>
                            </select>
                            <select name="education" class="h-11 rounded-md border border-line px-3">
                                <option value="tenth">10th</option>
                                <option value="twelfth">12th</option>
                                <option value="graduate">Graduate</option>
                                <option value="any">Any</option>
                            </select>
                            <select name="can_join" class="h-11 rounded-md border border-line px-3">
                                <option value="immediate">Immediate</option>
                                <option value="15_days">15 days</option>
                                <option value="1_month">1 month</option>
                            </select>
                            <input name="expected_salary" type="number" min="1" required placeholder="Expected monthly pay" class="h-11 rounded-md border border-line px-3">
                            <textarea name="note" minlength="10" required placeholder="Why you fit this job" class="min-h-20 rounded-md border border-line px-3 py-2"></textarea>
                            <button class="h-11 rounded-md bg-primary-500 font-bold text-white" type="submit">Apply</button>
                        </form>
                    </div>
                @elseif ($kind === 'service')
                    <div class="mt-4 grid gap-3 rounded-lg bg-surface p-4 ring-1 ring-line">
                        <p class="text-sm font-bold text-ink-900">Booking</p>
                        <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Requested · Accepted · Done</p>
                        <p id="booking-status" class="text-sm text-ink-500"></p>
                        <form id="booking-form" class="grid gap-2">
                            <input name="visit_on" type="date" required class="h-11 rounded-md border border-line px-3">
                            <select name="slot" class="h-11 rounded-md border border-line px-3">
                                <option value="morning">Morning</option>
                                <option value="afternoon">Afternoon</option>
                                <option value="evening">Evening</option>
                                <option value="full_day">Full day</option>
                            </select>
                            @foreach (\App\Support\BookingDetails::form($listing->category) as $field)
                                @if (($field['type'] ?? '') === 'select')
                                    <select name="detail_{{ $field['key'] }}" class="h-11 rounded-md border border-line px-3">
                                        @foreach ($field['options'] as $option)
                                            <option value="{{ $option }}">{{ str_replace('_', ' ', $option) }}</option>
                                        @endforeach
                                    </select>
                                @elseif (($field['type'] ?? '') === 'checkbox')
                                    <label class="text-sm font-semibold"><input type="checkbox" name="detail_{{ $field['key'] }}"> {{ str_replace('_', ' ', $field['key']) }}</label>
                                @else
                                    <input name="detail_{{ $field['key'] }}" @if(($field['type'] ?? '') === 'number') type="number" @endif required placeholder="{{ str_replace('_', ' ', $field['key']) }}" class="h-11 rounded-md border border-line px-3">
                                @endif
                            @endforeach
                            <textarea name="note" minlength="10" required placeholder="What do you need done?" class="min-h-20 rounded-md border border-line px-3 py-2"></textarea>
                            <button class="h-11 rounded-md bg-primary-500 font-bold text-white" type="submit">Request booking</button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('site.download') }}" class="mt-4 inline-flex h-12 w-full items-center justify-center rounded-md bg-primary-500 text-base font-bold text-white">Chat in the app</a>
                @endif
                <a href="{{ route('site.work') }}" class="mt-3 inline-flex text-sm font-bold text-primary-500">My applications and bookings</a>
                <p class="mt-3 text-xs leading-relaxed text-ink-500">Chat in the app after you have seen the item. Reporting is available below if you are signed in.</p>
                <div id="ad-tools" class="mt-4 hidden grid gap-3" data-listing="{{ $listing->id }}" data-seller="{{ $listing->user_id }}">
                    <form id="review-form" class="hidden grid gap-2">
                        <p class="text-sm font-bold text-ink-900">Rate this seller</p>
                        <select id="review-rating" class="h-11 rounded-md border border-line px-3">
                            @foreach ([5, 4, 3, 2, 1] as $star)
                                <option value="{{ $star }}">{{ $star }} stars</option>
                            @endforeach
                        </select>
                        <textarea id="review-comment" class="min-h-20 rounded-md border border-line px-3 py-2" placeholder="How did the deal go?"></textarea>
                        <button class="h-11 rounded-md bg-ink-900 font-bold text-white" type="submit">Submit rating</button>
                    </form>
                    <form id="report-form" class="grid gap-2">
                        <p class="text-sm font-bold text-ink-900">Report this ad</p>
                        <select id="report-reason" class="h-11 rounded-md border border-line px-3">
                            <option value="fraud">Suspicious / fraud</option>
                            <option value="spam">Spam or duplicate</option>
                            <option value="wrong_category">Wrong category</option>
                            <option value="sold_elsewhere">Already sold</option>
                            <option value="other">Other</option>
                        </select>
                        <button class="h-11 rounded-md border border-line font-bold text-ink-900" type="submit">Send report</button>
                    </form>
                    <p id="ad-tools-note" class="text-sm font-semibold text-ink-700"></p>
                </div>
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
    <script>
        const tools = document.getElementById('ad-tools');
        const token = localStorage.getItem('tantan_token');
        const listingId = tools?.dataset.listing;
        const sellerId = tools?.dataset.seller;
        const note = document.getElementById('ad-tools-note');
        const headers = { Accept: 'application/json' };
        if (token) headers.Authorization = 'Bearer ' + token;
        if (tools && token) {
            tools.classList.remove('hidden');
            fetch('/api/v1/listings/' + listingId, { headers }).then((response) => response.json()).then((body) => {
                const workflow = body.data?.workflow;
                if (workflow?.status) {
                    const slot = document.getElementById(workflow.kind === 'job' ? 'job-status' : 'booking-status');
                    if (slot) slot.textContent = 'Your status: ' + workflow.status;
                }
                if (body.data && body.data.can_review) document.getElementById('review-form').classList.remove('hidden');
            }).catch(() => {});
            document.getElementById('review-form').onsubmit = async (event) => {
                event.preventDefault();
                const response = await fetch('/api/v1/reviews', {
                    method: 'POST',
                    headers: { ...headers, 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        reviewee_id: Number(sellerId),
                        listing_id: Number(listingId),
                        rating: Number(document.getElementById('review-rating').value),
                        comment: document.getElementById('review-comment').value,
                    }),
                });
                const body = await response.json().catch(() => ({}));
                note.textContent = body.message || 'Could not save the rating.';
            };
            document.getElementById('report-form').onsubmit = async (event) => {
                event.preventDefault();
                const response = await fetch('/api/v1/reports', {
                    method: 'POST',
                    headers: { ...headers, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ listing_id: Number(listingId), reason: document.getElementById('report-reason').value }),
                });
                const body = await response.json().catch(() => ({}));
                note.textContent = body.data && body.data.hidden ? 'This ad was hidden after enough reports.' : (body.message || 'Could not send the report.');
            };
        }
        async function signed(path, payload) {
            if (!token) {
                if (note) note.textContent = 'Sign in on the Work page first, then come back.';
                return;
            }
            const response = await fetch(path, { method: 'POST', headers: { ...headers, 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            const body = await response.json().catch(() => ({}));
            if (note) note.textContent = body.message || 'Request failed';
            if (response.ok) window.location.href = '{{ route('site.work') }}';
        }
        const jobForm = document.getElementById('job-form');
        if (jobForm) {
            jobForm.onsubmit = (event) => {
                event.preventDefault();
                const data = new FormData(jobForm);
                signed('/api/v1/listings/' + listingId + '/applications', {
                    note: data.get('note'),
                    experience: data.get('experience'),
                    education: data.get('education'),
                    can_join: data.get('can_join'),
                    expected_salary: Number(data.get('expected_salary')),
                });
            };
        }
        const bookingForm = document.getElementById('booking-form');
        if (bookingForm) {
            bookingForm.onsubmit = (event) => {
                event.preventDefault();
                const data = new FormData(bookingForm);
                const details = {};
                for (const [key, value] of data.entries()) {
                    if (String(key).startsWith('detail_')) details[String(key).slice(7)] = value === 'on' ? true : value;
                }
                signed('/api/v1/listings/' + listingId + '/bookings', {
                    visit_on: data.get('visit_on'),
                    slot: data.get('slot'),
                    note: data.get('note'),
                    details,
                });
            };
        }
    </script>
@endsection
