<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin sign in · MemberHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-visual">
            <a class="brand-lockup" href="{{ route('dashboard') }}"><span class="brand-mark">M</span><span><strong>MemberHub</strong><small>Membership operations</small></span></a>
            <div class="auth-visual-copy">
                <p class="eyebrow">Welcome back</p>
                <h1>Keep every membership moment in view.</h1>
                <p>Manage member records, payments, renewals, and approvals from one calm workspace.</p>
            </div>
            <div class="auth-shape auth-shape-one"></div><div class="auth-shape auth-shape-two"></div>
        </section>
        <section class="auth-card-wrap">
            <div class="auth-card">
                <p class="eyebrow">Administrator access</p>
                <h2>Sign in to MemberHub</h2>
                <p class="auth-muted">Use your staff account to continue.</p>
                @if ($errors->any())
                    <div class="error-box"><strong>Sign in failed.</strong> {{ $errors->first() }}</div>
                @endif
                <form class="stack-form" action="{{ route('login.store') }}" method="POST">
                    @csrf
                    <div><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required></div>
                    <div><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
                    <label class="checkbox-label"><input name="remember" type="checkbox" value="1"> Keep me signed in</label>
                    <button class="primary-button" type="submit">Sign in</button>
                </form>
                <p class="auth-hint">Demo access: <strong>test@example.com</strong> / <strong>password</strong> after running the database seeder.</p>
            </div>
        </section>
    </main>
</body>
</html>
