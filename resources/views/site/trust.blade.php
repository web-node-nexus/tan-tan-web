@extends('layouts.site')

@section('content')
    <section class="section">
        <div class="site-container max-w-2xl">
            <h1 class="text-3xl font-extrabold text-ink-900">Trust and contact</h1>
            <p class="mt-2 text-sm leading-relaxed text-ink-500">Verified badge means an admin checked an Aadhaar, PAN or driving licence plus a selfie. Dealer accounts show a business name on ads. Ads that ask for an OTP, registration fee, gift card or similar are rejected before they go to review.</p>
            <p id="trust-error" class="mt-4 text-sm font-semibold text-red-600"></p>
            <div id="trust-auth" class="mt-6 grid gap-3 rounded-lg bg-surface p-4 ring-1 ring-line">
                <label class="text-sm font-semibold">Mobile number
                    <input id="phone" class="mt-1 h-11 w-full rounded-md border border-line px-3" placeholder="10-digit mobile">
                </label>
                <button id="send-otp" type="button" class="h-11 rounded-md bg-ink-900 font-bold text-white">Send OTP</button>
                <label class="text-sm font-semibold">OTP
                    <input id="otp" class="mt-1 h-11 w-full rounded-md border border-line px-3" inputmode="numeric">
                </label>
                <button id="verify-otp" type="button" class="h-11 rounded-md bg-primary-500 font-bold text-white">Verify</button>
            </div>
            <div id="trust-forms" class="mt-6 hidden grid gap-8">
                <form id="kyc-form" class="grid gap-3 rounded-lg bg-surface p-4 ring-1 ring-line">
                    <h2 class="text-lg font-bold">KYC</h2>
                    <p id="kyc-status" class="text-sm text-ink-500"></p>
                    <label class="text-sm font-semibold">Name on the ID
                        <input name="legal_name" required minlength="3" class="mt-1 h-11 w-full rounded-md border border-line px-3">
                    </label>
                    <label class="text-sm font-semibold">Document
                        <select name="document_type" class="mt-1 h-11 w-full rounded-md border border-line px-3">
                            <option value="aadhaar">Aadhaar</option>
                            <option value="pan">PAN</option>
                            <option value="dl">Driving licence</option>
                        </select>
                    </label>
                    <label class="text-sm font-semibold">Document number
                        <input name="document_number" required class="mt-1 h-11 w-full rounded-md border border-line px-3">
                    </label>
                    <label class="text-sm font-semibold">Photo of the document
                        <input name="document" type="file" accept="image/*" capture="environment" required class="mt-1 block w-full text-sm">
                    </label>
                    <label class="text-sm font-semibold">Selfie
                        <input name="selfie" type="file" accept="image/*" capture="user" required class="mt-1 block w-full text-sm">
                    </label>
                    <button class="h-11 rounded-md bg-primary-500 font-bold text-white" type="submit">Submit for review</button>
                </form>
                <form id="business-form" class="grid gap-3 rounded-lg bg-surface p-4 ring-1 ring-line">
                    <h2 class="text-lg font-bold">Dealer account</h2>
                    <label class="text-sm font-semibold">Account type
                        <select name="account_type" class="mt-1 h-11 w-full rounded-md border border-line px-3">
                            <option value="personal">Personal</option>
                            <option value="business">Dealer / business</option>
                        </select>
                    </label>
                    <label class="text-sm font-semibold">Business name
                        <input name="business_name" class="mt-1 h-11 w-full rounded-md border border-line px-3">
                    </label>
                    <label class="text-sm font-semibold">GSTIN (optional)
                        <input name="gstin" class="mt-1 h-11 w-full rounded-md border border-line px-3" maxlength="15">
                    </label>
                    <button class="h-11 rounded-md bg-ink-900 font-bold text-white" type="submit">Save account</button>
                </form>
            </div>
        </div>
    </section>
    <script>
        const tokenKey = 'tantan_token';
        const errorBox = document.getElementById('trust-error');
        const auth = document.getElementById('trust-auth');
        const forms = document.getElementById('trust-forms');
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
        async function refreshKyc() {
            const body = await api('/me/kyc');
            const kyc = body.data;
            document.getElementById('kyc-status').textContent = 'Status: ' + kyc.kyc_status + (kyc.document_last4 ? ' · ••••' + kyc.document_last4 : '') + (kyc.note ? ' · ' + kyc.note : '');
        }
        function signedIn() {
            auth.classList.add('hidden');
            forms.classList.remove('hidden');
            refreshKyc().catch((error) => { errorBox.textContent = error.message; });
        }
        if (localStorage.getItem(tokenKey)) signedIn();
        document.getElementById('send-otp').onclick = async () => {
            errorBox.textContent = '';
            try { await api('/auth/otp/send', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ phone: document.getElementById('phone').value }) }); }
            catch (error) { errorBox.textContent = error.message; }
        };
        document.getElementById('verify-otp').onclick = async () => {
            try {
                const body = await api('/auth/otp/verify', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ phone: document.getElementById('phone').value, otp: document.getElementById('otp').value }) });
                localStorage.setItem(tokenKey, body.data.token);
                signedIn();
            } catch (error) { errorBox.textContent = error.message; }
        };
        document.getElementById('kyc-form').onsubmit = async (event) => {
            event.preventDefault();
            errorBox.textContent = '';
            try {
                await api('/me/kyc', { method: 'POST', body: new FormData(event.target) });
                await refreshKyc();
            } catch (error) { errorBox.textContent = error.message; }
        };
        document.getElementById('business-form').onsubmit = async (event) => {
            event.preventDefault();
            errorBox.textContent = '';
            const data = new FormData(event.target);
            try {
                await api('/me/business', { method: 'PATCH', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ account_type: data.get('account_type'), business_name: data.get('business_name'), gstin: data.get('gstin') }) });
                errorBox.textContent = '';
                event.target.querySelector('button').textContent = 'Saved';
            } catch (error) { errorBox.textContent = error.message; }
        };
    </script>
@endsection
