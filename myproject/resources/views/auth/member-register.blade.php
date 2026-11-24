<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Member Account · MemberHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $googleMapsKey = config('services.google_maps.key');
        $hasGoogleMapsKey = filled($googleMapsKey) && ! str_starts_with($googleMapsKey, 'replace_with_');
    @endphp
    @if (! $hasGoogleMapsKey)
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    @endif
</head>
<body class="auth-page auth-register-page">
    <main class="auth-panel auth-register-panel">
        <a class="brand auth-brand auth-brand-inline" href="{{ route('member.login') }}">
            <span class="brand-mark">M</span>
            <span class="brand-text">MemberHub<small>Membership management portal</small></span>
        </a>

        <h1>Create a Member account</h1>
        <p class="help">Register once to access your member dashboard, tasks, profile, and transaction history.</p>

        @if ($errors->any())
            <div class="error-box"><strong>Please check the form.</strong> {{ $errors->first() }}</div>
        @endif

        <form class="stack-form" action="{{ route('member.register.store') }}" method="POST">
            @csrf
            <div class="form-grid"><div><label for="first_name">First name</label><input id="first_name" name="first_name" value="{{ old('first_name') }}" required></div><div><label for="last_name">Last name</label><input id="last_name" name="last_name" value="{{ old('last_name') }}" required></div></div>
            <div><label for="email">Email Address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required></div>
            <div class="form-grid"><div><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="new-password" required></div><div><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div></div>
            <div class="form-grid"><div><label for="phone">Phone number <span class="optional">(optional)</span></label><input id="phone" name="phone" value="{{ old('phone') }}"></div><div><label for="address">Address <span class="optional">(optional)</span></label><input id="address" name="address" value="{{ old('address') }}"></div></div>
            <div class="form-grid"><div><label for="membership_category_id">Membership category</label><select id="membership_category_id" name="membership_category_id" required><option value="">Choose a category</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('membership_category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select></div><div><label for="membership_level_id">Membership level</label><select id="membership_level_id" name="membership_level_id" required><option value="">Choose a level</option>@foreach ($levels as $level)<option value="{{ $level->id }}" @selected(old('membership_level_id') == $level->id)>{{ $level->name }} · ₱{{ number_format((float) $level->fee, 2) }}</option>@endforeach</select></div></div>
            <div class="registration-location-grid">
                <div class="registration-map-panel">
                    <div class="registration-section-heading"><label for="member-map">Location on map</label><span id="member-coordinates">Choose a location</span></div>
                    <div id="member-map" aria-label="Choose your location on the map"></div>
                    <input id="member-lat" type="hidden" name="latitude" value="{{ old('latitude', '14.5995') }}">
                    <input id="member-lon" type="hidden" name="longitude" value="{{ old('longitude', '120.9842') }}">
                </div>
                <aside class="registration-weather-panel">
                    <p class="eyebrow">Selected location</p>
                    <h2>Current weather</h2>
                    <p id="member-weather" aria-live="polite">Weather has not been loaded.</p>
                    <small>Your selected coordinates are saved with your member profile.</small>
                </aside>
            </div>
            <div class="form-actions"><a class="secondary-button" href="{{ route('member.login') }}">Back to login</a><button class="primary-button" type="submit">Create Member Account</button></div>
        </form>
    </main>
    <script>
        let memberMap;
        let memberMarker;

        function loadMemberWeather(lat, lon) {
            document.getElementById('member-weather').textContent = 'Loading weather...';

            fetch(`{{ route('weather.show') }}?lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lon)}`)
                .then(response => response.ok ? response.json() : Promise.reject())
                .then(weather => {
                    document.getElementById('member-weather').textContent = `${weather.place}: ${weather.temperature}°C, ${weather.description}, humidity ${weather.humidity}%`;
                })
                .catch(() => {
                    document.getElementById('member-weather').textContent = 'Weather is currently unavailable.';
                });
        }

        function setMemberLocation(lat, lon) {
            document.getElementById('member-lat').value = lat.toFixed(7);
            document.getElementById('member-lon').value = lon.toFixed(7);
            document.getElementById('member-coordinates').textContent = `Latitude: ${lat.toFixed(5)}, Longitude: ${lon.toFixed(5)}`;

            @if ($hasGoogleMapsKey)
                if (memberMarker) memberMarker.setMap(null);
                memberMarker = new google.maps.Marker({ position: { lat, lng: lon }, map: memberMap });
            @else
                if (memberMarker) memberMap.removeLayer(memberMarker);
                memberMarker = L.marker([lat, lon]).addTo(memberMap);
            @endif

            loadMemberWeather(lat, lon);
        }

        @if ($hasGoogleMapsKey)
            function initMemberMap() {
                const lat = Number(document.getElementById('member-lat').value);
                const lon = Number(document.getElementById('member-lon').value);
                memberMap = new google.maps.Map(document.getElementById('member-map'), { center: { lat, lng: lon }, zoom: 13 });
                memberMap.addListener('click', event => setMemberLocation(event.latLng.lat(), event.latLng.lng()));
                setMemberLocation(lat, lon);
            }

            window.initMemberMap = initMemberMap;
        @else
            function initMemberMap() {
                const lat = Number(document.getElementById('member-lat').value);
                const lon = Number(document.getElementById('member-lon').value);
                memberMap = L.map('member-map').setView([lat, lon], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors', maxZoom: 19 }).addTo(memberMap);
                memberMap.on('click', event => setMemberLocation(event.latlng.lat, event.latlng.lng));
                setMemberLocation(lat, lon);
            }

            window.addEventListener('DOMContentLoaded', initMemberMap);
        @endif
    </script>
    @if ($hasGoogleMapsKey)
        <script async src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&loading=async&callback=initMemberMap"></script>
    @else
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    @endif
</body>
</html>
