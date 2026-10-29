@extends('layouts.site')

@section('content')
    <section class="section">
        <div class="site-container max-w-2xl">
            <h1 class="text-3xl font-extrabold text-ink-900">Post an ad</h1>
            <p class="mt-2 text-sm text-ink-500">Photos and video go to review before the ad is visible. Camera or gallery both work. Drag the order with the arrows.</p>
            <p id="sell-error" class="mt-4 text-sm font-semibold text-red-600"></p>
            <div id="sell-auth" class="mt-6 grid gap-3 rounded-lg bg-surface p-4 ring-1 ring-line">
                <label class="text-sm font-semibold">Mobile number
                    <input id="phone" class="mt-1 h-11 w-full rounded-md border border-line px-3" placeholder="10-digit mobile">
                </label>
                <button id="send-otp" type="button" class="h-11 rounded-md bg-ink-900 font-bold text-white">Send OTP</button>
                <label class="text-sm font-semibold">OTP
                    <input id="otp" class="mt-1 h-11 w-full rounded-md border border-line px-3" inputmode="numeric">
                </label>
                <button id="verify-otp" type="button" class="h-11 rounded-md bg-primary-500 font-bold text-white">Verify</button>
            </div>
            <form id="sell-form" class="mt-6 hidden grid gap-3" enctype="multipart/form-data">
                <label class="text-sm font-semibold">Category
                    <select id="category" name="category_id" class="mt-1 h-11 w-full rounded-md border border-line px-3" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" data-slug="{{ $category->slug }}">{{ $category->name_en }}</option>
                        @endforeach
                    </select>
                </label>
                <div id="attrs" class="grid gap-3"></div>
                <label class="text-sm font-semibold">Title
                    <input name="title" minlength="8" required class="mt-1 h-11 w-full rounded-md border border-line px-3">
                </label>
                <label class="text-sm font-semibold">Description
                    <textarea name="description" minlength="20" required class="mt-1 min-h-28 w-full rounded-md border border-line px-3 py-2"></textarea>
                </label>
                <label class="text-sm font-semibold">Price
                    <input name="price" type="number" min="1" required class="mt-1 h-11 w-full rounded-md border border-line px-3">
                </label>
                <label class="text-sm font-semibold">City
                    <select name="city_id" class="mt-1 h-11 w-full rounded-md border border-line px-3">
                        <option value="">Select</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="grid gap-2 sm:grid-cols-2">
                    <label class="text-sm font-semibold">Camera photo
                        <input id="camera" type="file" accept="image/*" capture="environment" class="mt-1 block w-full text-sm">
                    </label>
                    <label class="text-sm font-semibold">Gallery photos
                        <input id="library" type="file" accept="image/*" multiple class="mt-1 block w-full text-sm">
                    </label>
                    <label class="text-sm font-semibold">Camera video
                        <input id="video-camera" type="file" accept="video/*" capture="environment" class="mt-1 block w-full text-sm">
                    </label>
                    <label class="text-sm font-semibold">Gallery video
                        <input id="video-library" type="file" accept="video/mp4,video/quicktime,video/webm" class="mt-1 block w-full text-sm">
                    </label>
                </div>
                <ul id="photos" class="grid gap-2"></ul>
                <p id="video-name" class="text-sm text-ink-500"></p>
                <button class="h-12 rounded-md bg-primary-500 font-bold text-white" type="submit">Submit for review</button>
            </form>
            <p id="sell-done" class="mt-6 hidden rounded-lg bg-trust-50 p-4 text-sm font-semibold text-trust-700">Submitted. Your ad stays pending until a moderator approves it.</p>
        </div>
    </section>
    <script>
        const required = @json($required);
        const tokenKey = 'tantan_token';
        const photos = [];
        let video = null;
        const form = document.getElementById('sell-form');
        const auth = document.getElementById('sell-auth');
        if (localStorage.getItem(tokenKey)) { auth.classList.add('hidden'); form.classList.remove('hidden'); }

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

        document.getElementById('send-otp').onclick = async () => {
            document.getElementById('sell-error').textContent = '';
            try { await api('/auth/otp/send', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ phone: document.getElementById('phone').value }) }); }
            catch (error) { document.getElementById('sell-error').textContent = error.message; }
        };
        document.getElementById('verify-otp').onclick = async () => {
            try {
                const body = await api('/auth/otp/verify', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ phone: document.getElementById('phone').value, otp: document.getElementById('otp').value }) });
                localStorage.setItem(tokenKey, body.data.token);
                auth.classList.add('hidden');
                form.classList.remove('hidden');
            } catch (error) { document.getElementById('sell-error').textContent = error.message; }
        };

        function renderAttrs() {
            const box = document.getElementById('attrs');
            box.innerHTML = '';
            const select = document.getElementById('category');
            const slug = select.options[select.selectedIndex]?.dataset.slug || '';
            const choices = {
                fuel: ['petrol', 'diesel', 'cng', 'electric'],
                transmission: ['manual', 'automatic'],
                storage: ['64', '128', '256', '512'],
                brand: slug === 'mobiles' ? ['apple', 'samsung', 'xiaomi', 'vivo', 'other'] : null,
            };
            const checks = ['bill', 'warranty', 'working'];
            (required[select.value] || []).forEach((key) => {
                const label = document.createElement('label');
                label.className = 'text-sm font-semibold';
                label.textContent = key.replaceAll('_', ' ');
                const options = choices[key];
                const field = options ? document.createElement('select') : document.createElement('input');
                field.name = 'attr_' + key;
                field.className = 'mt-1 h-11 w-full rounded-md border border-line px-3';
                if (options) {
                    options.forEach((option) => {
                        const item = document.createElement('option');
                        item.value = option;
                        item.textContent = option;
                        field.append(item);
                    });
                } else if (checks.includes(key)) {
                    field.type = 'checkbox';
                } else if (['year', 'km', 'salary', 'experience', 'area_sqft'].includes(key)) {
                    field.type = 'number';
                    field.required = true;
                } else {
                    field.required = true;
                }
                label.append(field);
                box.append(label);
            });
        }
        document.getElementById('category').onchange = renderAttrs;
        renderAttrs();

        async function compress(file) {
            const bitmap = await createImageBitmap(file);
            const scale = Math.min(1, 1600 / Math.max(bitmap.width, 1));
            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(bitmap.width * scale));
            canvas.height = Math.max(1, Math.round(bitmap.height * scale));
            canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
            const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.72));
            return new File([blob], 'photo.jpg', { type: 'image/jpeg' });
        }
        function paint() {
            const list = document.getElementById('photos');
            list.innerHTML = '';
            photos.forEach((file, index) => {
                const item = document.createElement('li');
                item.className = 'flex items-center gap-2 text-sm';
                item.textContent = (index + 1) + '. ' + file.name;
                ['↑', '↓'].forEach((mark, dir) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.textContent = mark;
                    button.className = 'h-8 w-8 rounded-md bg-canvas font-bold';
                    button.onclick = () => {
                        const next = index + (dir ? 1 : -1);
                        if (next < 0 || next >= photos.length) return;
                        [photos[index], photos[next]] = [photos[next], photos[index]];
                        paint();
                    };
                    item.append(button);
                });
                list.append(item);
            });
        }
        async function addPhotos(files) {
            for (const file of files) {
                if (photos.length >= 8) break;
                photos.push(await compress(file));
            }
            paint();
        }
        document.getElementById('camera').onchange = (event) => addPhotos(event.target.files);
        document.getElementById('library').onchange = (event) => addPhotos(event.target.files);
        document.getElementById('video-camera').onchange = document.getElementById('video-library').onchange = (event) => {
            video = event.target.files[0] || null;
            document.getElementById('video-name').textContent = video ? video.name : '';
        };

        form.onsubmit = async (event) => {
            event.preventDefault();
            document.getElementById('sell-error').textContent = '';
            const data = new FormData(form);
            const payload = new FormData();
            ['category_id', 'title', 'description', 'price', 'city_id'].forEach((key) => {
                if (data.get(key)) payload.append(key, data.get(key));
            });
            payload.append('condition', 'used');
            (required[document.getElementById('category').value] || []).forEach((key) => {
                const value = data.get('attr_' + key);
                const checked = ['bill', 'warranty', 'working'].includes(key);
                payload.append('attributes[' + key + ']', checked ? (value ? '1' : '0') : value);
            });
            photos.forEach((file) => payload.append('images[]', file, file.name));
            if (video) payload.append('video', video, video.name);
            try {
                await api('/listings', { method: 'POST', body: payload });
                form.classList.add('hidden');
                document.getElementById('sell-done').classList.remove('hidden');
            } catch (error) {
                document.getElementById('sell-error').textContent = error.message;
            }
        };
    </script>
@endsection
