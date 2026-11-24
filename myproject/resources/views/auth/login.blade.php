<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin sign in · MemberHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <main class="auth-panel">
        <a class="brand auth-brand" href="{{ route('dashboard') }}">
            <span class="brand-mark">M</span>
            <span class="brand-text">MemberHub<small>Membership management portal</small></span>
        </a>

        <nav class="auth-role-switch" aria-label="Choose access type">
            <a href="{{ route('member.login') }}" class="auth-role-link">Member</a>
            <a href="{{ route('login') }}" class="auth-role-link is-active" aria-current="page">Administrator</a>
        </nav>

        <h1>Administrator Login</h1>
        <p class="help">Manage member records, applications, payments, and membership approvals.</p>

        @if ($errors->any())
            <div class="error-box"><strong>Sign in failed.</strong> {{ $errors->first() }}</div>
        @endif

        <form class="stack-form" action="{{ route('login.store') }}" method="POST">
            @csrf
            <div>
                <label for="email">Email Address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            </div>
            <div>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <label class="checkbox-label"><input name="remember" type="checkbox" value="1"> Remember me</label>
            <button class="primary-button" type="submit">Log In</button>
        </form>

        <div class="auth-footer">
            <p class="auth-links">Need an account? <a href="{{ route('member.register') }}">Create a Member account</a></p>
            <p class="help">Administrator access is for staff members. Members can register, check their status, and view their transactions through the member portal.</p>
        </div>

        <p class="auth-hint">Demo access: <strong>test@example.com</strong> / <strong>password</strong></p>
    </main>
</body>
</html>
