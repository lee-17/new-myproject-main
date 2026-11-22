<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Membership application</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $googleMapsKey = config('services.google_maps.key');
        $hasGoogleMapsKey = filled($googleMapsKey) && ! str_starts_with($googleMapsKey, 'replace_with_');
    @endphp
    @if (! $hasGoogleMapsKey)
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIINfQ3w3bYq9uZl5L5sR4s9qz2x3z9QZl4=" crossorigin="">
    @endif
</head>
<body class="public-application">
    <main class="mx-auto flex max-w-6xl flex-col gap-8 px-4 py-10 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-2">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-sky-600">MemberHub</p>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">Membership application</h1>
            <p class="max-w-2xl text-slate-500">Choose a membership category, share your contact details, and select your location for a live weather preview.</p>
        </header>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800" role="status">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800" role="alert">
                <p class="font-semibold">Please correct the highlighted fields.</p>
                <ul class="mt-2 list-inside list-disc text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(20rem,0.85fr)]">
            <form class="flex flex-col gap-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" method="POST" action="{{ route('membership.store') }}">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700" for="membership_category_id">Membership category</label>
                    <select class="mt-2 w-full rounded-lg border-slate-300" id="membership_category_id" name="membership_category_id" required>
                        <option value="">Choose a category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('membership_category_id') == $category->id)>
                                {{ $category->name }}{{ $category->level ? ' - '.$category->level : '' }} (₱{{ number_format((float) $category->fee, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700" for="full_name">Full name</label>
                        <input class="mt-2 w-full rounded-lg border-slate-300" id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700" for="email">Email</label>
                        <input class="mt-2 w-full rounded-lg border-slate-300" id="email" type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700" for="phone">Phone</label>
                        <input class="mt-2 w-full rounded-lg border-slate-300" id="phone" name="phone" value="{{ old('phone') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700" for="address">Address</label>
                        <input class="mt-2 w-full rounded-lg border-slate-300" id="address" name="address" value="{{ old('address') }}">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between gap-4">
                        <label class="block text-sm font-semibold text-slate-700" for="map">Location on map</label>
                        <span id="coordinates" class="text-xs text-slate-500">Choose a location</span>
                    </div>
                    <div class="mt-2 h-80 overflow-hidden rounded-xl border border-slate-200 bg-slate-100" id="map"></div>
                    <input id="lat" type="hidden" name="latitude" value="{{ old('latitude', '14.5995') }}">
                    <input id="lon" type="hidden" name="longitude" value="{{ old('longitude', '120.9842') }}">
                </div>

                <button class="rounded-lg bg-sky-600 px-4 py-3 font-semibold text-white transition hover:bg-sky-700" type="submit">Create membership application</button>
            </form>

            <aside class="flex flex-col gap-5 rounded-2xl border border-sky-100 bg-sky-50 p-6">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.15em] text-sky-700">Selected location</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Current weather</h2>
                </div>
                <p class="rounded-xl bg-white/70 p-4 text-slate-600" id="weather" aria-live="polite">Weather has not been loaded.</p>
                <p class="text-sm leading-6 text-slate-500">Your coordinates are used to preview weather and are saved with the application so staff can understand the selected location.</p>
            </aside>
        </section>
    </main>

    <script>
        let map;
        let marker;

        function loadWeather(lat, lon) {
            document.getElementById('weather').textContent = 'Loading weather...';

            fetch(`{{ route('weather.show') }}?lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lon)}`)
                .then(response => response.ok ? response.json() : Promise.reject())
                .then(weather => {
                    document.getElementById('weather').textContent = `${weather.place}: ${weather.temperature}°C, ${weather.description}, humidity ${weather.humidity}%`;
                })
                .catch(() => {
                    document.getElementById('weather').textContent = 'Weather is currently unavailable.';
                });
        }

        function setLocation(lat, lon) {
            document.getElementById('lat').value = lat.toFixed(7);
            document.getElementById('lon').value = lon.toFixed(7);
            document.getElementById('coordinates').textContent = `Latitude: ${lat.toFixed(5)}, Longitude: ${lon.toFixed(5)}`;

            if (marker) {
                @if ($hasGoogleMapsKey)
                    marker.setMap(null);
                    marker = new google.maps.Marker({ position: { lat, lng: lon }, map });
                @else
                    map.removeLayer(marker);
                    marker = L.marker([lat, lon]).addTo(map);
                @endif
            } else {
                @if ($hasGoogleMapsKey)
                    marker = new google.maps.Marker({ position: { lat, lng: lon }, map });
                @else
                    marker = L.marker([lat, lon]).addTo(map);
                @endif
            }

            loadWeather(lat, lon);
        }

        @if ($hasGoogleMapsKey)
        function initMap() {
            const lat = Number(document.getElementById('lat').value);
            const lon = Number(document.getElementById('lon').value);

            map = new google.maps.Map(document.getElementById('map'), { center: { lat, lng: lon }, zoom: 13 });
            map.addListener('click', event => setLocation(event.latLng.lat(), event.latLng.lng()));
            setLocation(lat, lon);
        }

        window.initMap = initMap;
        @else
        function initMap() {
            const lat = Number(document.getElementById('lat').value);
            const lon = Number(document.getElementById('lon').value);

            map = L.map('map').setView([lat, lon], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);
            map.on('click', event => setLocation(event.latlng.lat, event.latlng.lng));
            setLocation(lat, lon);
        }

        window.addEventListener('DOMContentLoaded', initMap);
        @endif
    </script>
    @if ($hasGoogleMapsKey)
        <script async src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&loading=async&callback=initMap"></script>
    @else
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @endif
</body>
</html>
