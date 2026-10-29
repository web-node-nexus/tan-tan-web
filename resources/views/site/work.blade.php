@extends('layouts.site')

@section('content')
    <section class="section">
        <div class="site-container max-w-2xl">
            <h1 class="text-3xl font-extrabold text-ink-900">Applications and bookings</h1>
            <p class="mt-2 text-sm text-ink-500">Jobs move through Applied, Shortlisted, Interview and Hired. Services move through Requested, Accepted and Done. A price drop or a new post from someone you follow also shows up in alerts.</p>
            <p id="work-error" class="mt-4 text-sm font-semibold text-red-600"></p>
            <div id="work-auth" class="mt-6 grid gap-3 rounded-lg bg-surface p-4 ring-1 ring-line">
                <label class="text-sm font-semibold">Mobile number
                    <input id="phone" class="mt-1 h-11 w-full rounded-md border border-line px-3" placeholder="10-digit mobile">
                </label>
                <button id="send-otp" type="button" class="h-11 rounded-md bg-ink-900 font-bold text-white">Send OTP</button>
                <label class="text-sm font-semibold">OTP
                    <input id="otp" class="mt-1 h-11 w-full rounded-md border border-line px-3" inputmode="numeric">
                </label>
                <button id="verify-otp" type="button" class="h-11 rounded-md bg-primary-500 font-bold text-white">Verify</button>
            </div>
            <div id="work-lists" class="mt-6 hidden grid gap-8">
                <div>
                    <h2 class="text-lg font-bold">Job applications</h2>
                    <ul id="applications" class="mt-3 grid gap-2 text-sm"></ul>
                </div>
                <div>
                    <h2 class="text-lg font-bold">Service bookings</h2>
                    <ul id="bookings" class="mt-3 grid gap-2 text-sm"></ul>
                </div>
                <div>
                    <h2 class="text-lg font-bold">Alerts</h2>
                    <ul id="alerts" class="mt-3 grid gap-2 text-sm"></ul>
                </div>
            </div>
        </div>
    </section>
    <script>
        const tokenKey = 'tantan_token';
        const errorBox = document.getElementById('work-error');
        async function api(path, options = {}) {
            const headers = options.headers || {};
            const token = localStorage.getItem(tokenKey);
            if (token) headers.Authorization = 'Bearer ' + token;
            headers.Accept = 'application/json';
            const response = await fetch('/api/v1' + path, { ...options, headers });
            const body = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(body.message || 'Request failed');
            return body;
        }
        function row(title, status) {
            const item = document.createElement('li');
            item.className = 'rounded-md bg-surface px-3 py-2 ring-1 ring-line';
            item.textContent = title + ' · ' + status;
            return item;
        }
        async function load() {
            document.getElementById('work-auth').classList.add('hidden');
            document.getElementById('work-lists').classList.remove('hidden');
            const [applications, bookings, alerts] = await Promise.all([
                api('/me/applications'),
                api('/me/bookings'),
                api('/me/alerts'),
            ]);
            const jobs = document.getElementById('applications');
            const visits = document.getElementById('bookings');
            const notices = document.getElementById('alerts');
            jobs.replaceChildren(...(applications.data.length ? applications.data.map((item) => row(item.listing?.title || 'Job', item.status)) : [row('None yet', '—')]));
            visits.replaceChildren(...(bookings.data.length ? bookings.data.map((item) => row(item.listing?.title || 'Service', item.status)) : [row('None yet', '—')]));
            notices.replaceChildren(...(alerts.data.alerts.length ? alerts.data.alerts.map((item) => row(item.title, item.body)) : [row('None yet', '—')]));
        }
        if (localStorage.getItem(tokenKey)) load().catch((error) => { errorBox.textContent = error.message; });
        document.getElementById('send-otp').onclick = async () => {
            errorBox.textContent = '';
            try { await api('/auth/otp/send', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ phone: document.getElementById('phone').value }) }); }
            catch (error) { errorBox.textContent = error.message; }
        };
        document.getElementById('verify-otp').onclick = async () => {
            try {
                const body = await api('/auth/otp/verify', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ phone: document.getElementById('phone').value, otp: document.getElementById('otp').value }) });
                localStorage.setItem(tokenKey, body.data.token);
                await load();
            } catch (error) { errorBox.textContent = error.message; }
        };
    </script>
@endsection
